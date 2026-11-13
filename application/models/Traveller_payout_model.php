<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Booking-level payout allocations and append-only settlement history.
 * No payment provider is called here. Manual settlement is a later phase.
 */
class Traveller_payout_model extends CI_Model
{
    const OPENING_DATE = '2026-11-13';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_opening_balance()
    {
        return $this->db->where('id', 1)->get('traveller_payout_opening_balances')->row();
    }

    public function preview_opening_balance(array $declaredPaidTravellers = [])
    {
        return $this->buildOpeningPreview(false, $declaredPaidTravellers);
    }

    /** CLI-only, one-time historical declaration. Never infer that later bookings were paid. */
    public function initialise_opening_balance(array $declaredPaidTravellers = [])
    {
        if (PHP_SAPI !== 'cli') {
            return ['status'=>false, 'msg'=>'Opening balances may only be initialized from the setup command.'];
        }
        if (!$this->db->trans_begin()) {
            return ['status'=>false, 'msg'=>'Unable to start payout initialization.'];
        }
        try {
            // This singleton lock serializes simultaneous setup runs, including an empty ledger.
            $opening = $this->db->query('SELECT * FROM traveller_payout_opening_balances WHERE id = 1' . $this->lockClause())->row();
            if (!$opening || $opening->opening_date !== self::OPENING_DATE) {
                throw new RuntimeException('Missing or mismatched opening-balance configuration.');
            }
            if ($opening->status === 'applied') {
                $this->db->trans_commit();
                return ['status'=>true, 'already_applied'=>true, 'msg'=>'Opening balance was already applied.'];
            }
            if ($opening->status !== 'pending' || $this->db->count_all('traveller_payouts') > 0) {
                throw new RuntimeException('Existing payout records require review before initialization.');
            }
            // A delayed first deployment cannot infer payment state as of the cutoff
            // from today's mutable booking status. Require a reviewed historical snapshot.
            if ($this->businessDate() > self::OPENING_DATE) {
                throw new RuntimeException('First-time initialization after the opening date requires a reviewed historical booking snapshot.');
            }

            $preview = $this->buildOpeningPreview(true, $declaredPaidTravellers);
            $recordedAt = $this->now();
            foreach ($preview['paid_groups'] as $group) {
                $this->insertOpeningPayout($group, $recordedAt);
            }
            $audit = json_encode($preview, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($audit === false || !$this->db->where('id', 1)->update('traveller_payout_opening_balances', [
                'status'=>'applied', 'snapshot_booking_id'=>$preview['snapshot_booking_id'],
                'audit_snapshot'=>$audit, 'applied_at'=>$recordedAt,
            ]) || !$this->db->trans_status() || !$this->db->trans_commit()) {
                throw new RuntimeException('Opening balance could not be committed.');
            }
            return ['status'=>true, 'already_applied'=>false, 'msg'=>'Opening balance recorded.', 'preview'=>$preview];
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Traveller payout initialization failed: ' . $error->getMessage());
            return ['status'=>false, 'msg'=>'Payout initialization was not saved. Review the setup state before retrying.'];
        }
    }

    /**
     * Outstanding is calculated per booking and current traveller/currency.
     * Overpaid or canceled bookings cannot reduce another customer's liability.
     */
    public function get_outstanding_summary()
    {
        if (!$this->schema_ready()) {
            return ['currencies'=>[], 'review_booking_ids'=>[], 'opening_balance_status'=>'missing'];
        }
        $totals = [];
        foreach (['GBP','CAD'] as $currency) {
            $totals[$currency] = ['total_commission'=>0, 'allocated_paid'=>0, 'outstanding'=>0, 'booking_count'=>0, 'unpaid_booking_count'=>0];
        }
        $allocations = [];
        $rows = $this->db->select('booking_id, traveller_id, currency, SUM(amount) AS amount', false)
            ->group_by(['booking_id','traveller_id','currency'])->get('traveller_payout_items')->result();
        foreach ($rows as $row) {
            $allocations[$row->booking_id . ':' . $row->traveller_id . ':' . $row->currency] = $this->minorAmount($row->amount);
        }
        $review = [];
        foreach ($this->currentBookings() as $booking) {
            $currency = $this->currency($booking->currency);
            $commission = $this->minorAmount($booking->traveller_commission);
            if (!$currency || $commission === null) {
                $review[] = (int) $booking->id;
                continue;
            }
            if (!$booking->current_traveller_id || !empty($booking->traveller_deleted_at)) {
                $review[] = (int) $booking->id;
            }
            $key = $booking->id . ':' . $booking->traveller_id . ':' . $currency;
            $paid = min($commission, max(0, $allocations[$key] ?? 0));
            $totals[$currency]['total_commission'] += $commission;
            $totals[$currency]['allocated_paid'] += $paid;
            $totals[$currency]['outstanding'] += $commission - $paid;
            $totals[$currency]['booking_count']++;
            if ($commission > $paid) $totals[$currency]['unpaid_booking_count']++;
        }
        foreach ($totals as &$total) {
            foreach (['total_commission','allocated_paid','outstanding'] as $key) $total[$key] = $this->decimalAmount($total[$key]);
        }
        unset($total);
        $opening = $this->get_opening_balance();
        return ['currencies'=>$totals, 'review_booking_ids'=>$review, 'opening_balance_status'=>$opening ? $opening->status : 'missing'];
    }

    public function get_traveller_history($travellerId)
    {
        if ((int) $travellerId <= 0) return [];
        $rows = $this->db->where('traveller_id', (int) $travellerId)->order_by('id', 'desc')->get('traveller_payouts')->result();
        if (!$rows) return [];
        $items = $this->db->where_in('payout_id', array_map(function ($row) { return (int) $row->id; }, $rows))
            ->order_by('booking_id', 'asc')->get('traveller_payout_items')->result();
        $byPayout = [];
        foreach ($items as $item) $byPayout[$item->payout_id][] = $item;
        foreach ($rows as $row) $row->items = $byPayout[$row->id] ?? [];
        return $rows;
    }

    public function schema_ready()
    {
        foreach (['traveller_payout_opening_balances','traveller_payouts','traveller_payout_items'] as $table) {
            if (!$this->db->table_exists($table)) return false;
        }
        return true;
    }

    public function get_payout_context($travellerId)
    {
        if (!$this->schema_ready() || !$this->get_opening_balance() || $this->get_opening_balance()->status !== 'applied') {
            return ['status'=>false, 'msg'=>'Initialize the payout opening balance before recording payouts.'];
        }
        return ['status'=>true, 'context'=>$this->buildPayoutContext($this->travellerPayoutState((int) $travellerId, false))];
    }

    /** Bulk status for the Arrivals page, avoiding a query per traveller. */
    public function get_arrival_payout_statuses(array $travellerIds)
    {
        $travellerIds = array_values(array_filter(array_map('intval', $travellerIds)));
        if (!$travellerIds || !$this->schema_ready()) return [];
        $opening = $this->get_opening_balance();
        if (!$opening || $opening->status !== 'applied') return [];
        $allocations = [];
        foreach ($this->db->where_in('traveller_id', $travellerIds)->get('traveller_payout_items')->result() as $item) {
            $key = $item->booking_id . ':' . $item->traveller_id . ':' . $item->currency;
            $allocations[$key] = ($allocations[$key] ?? 0) + ($this->minorAmount($item->amount) ?? 0);
        }
        $rows = $this->db->select('id, traveller_id, traveller_commission, currency, payment_method')->where_in('traveller_id', $travellerIds)
            ->where('payment_status','completed')->where('deleted_at IS NULL',null,false)->get('bookings')->result();
        $states = [];
        foreach ($rows as $booking) {
            $id = (int) $booking->traveller_id;
            $currency = $this->currency($booking->currency);
            $commission = $this->minorAmount($booking->traveller_commission);
            if (!$currency || $commission === null || ($booking->payment_method !== null && !in_array(strtolower($booking->payment_method), ['paystack','stripe','offline','bank'], true))) {
                $states[$id] = 'review';
                continue;
            }
            if (($states[$id] ?? '') === 'review') continue;
            $key = $booking->id . ':' . $id . ':' . $currency;
            if ($commission > ($allocations[$key] ?? 0)) $states[$id] = 'unpaid';
            elseif (!isset($states[$id])) $states[$id] = 'paid';
        }
        return $states;
    }

    /** Records an already-made bank payout; no money is transferred by this action. */
    public function record_manual_payout($travellerId, $adminId, array $submission)
    {
        if (!$this->schema_ready()) return ['status'=>false, 'msg'=>'Payout tracking needs database setup.'];
        if (!$this->db->trans_begin()) return ['status'=>false, 'msg'=>'Unable to start the payout record.'];
        try {
            $admin = $this->db->query('SELECT id, role FROM admins WHERE id = ?' . $this->lockClause(), [(int) $adminId])->row();
            if (!$admin || $admin->role !== 'super_admin') throw new RuntimeException('Only Super Admin can record traveller payouts.');
            $opening = $this->get_opening_balance();
            if (!$opening || $opening->status !== 'applied') throw new RuntimeException('Initialize the payout opening balance before recording payouts.');
            $errors = [];
            if (($submission['confirm_paid'] ?? '') !== '1') $errors['confirm_paid'] = 'Confirm that this traveller has been paid.';
            if (!preg_match('/^[a-f0-9]{32}$/D', (string) ($submission['request_key'] ?? ''))
                || !preg_match('/^[a-f0-9]{64}$/D', (string) ($submission['snapshot_token'] ?? ''))) {
                $errors['confirm_paid'] = 'Reload the payout details before confirming.';
            }
            if ($errors) {
                $this->db->trans_rollback();
                return ['status'=>false, 'msg'=>'Please correct the highlighted field.', 'field_errors'=>$errors];
            }

            $state = $this->travellerPayoutState((int) $travellerId, true);
            $prefix = 'manual:' . (int) $travellerId . ':' . (int) $adminId . ':' . $submission['request_key'] . ':';
            $previous = $this->db->like('operation_key', $prefix, 'after')->get('traveller_payouts')->result();
            if ($previous) {
                $this->db->trans_commit();
                return ['status'=>true, 'already_recorded'=>true, 'msg'=>'This payout has already been recorded.'];
            }
            $context = $this->buildPayoutContext($state);
            if ($context['paid']) {
                $this->db->trans_commit();
                return ['status'=>true, 'already_recorded'=>true, 'msg'=>'No outstanding payout for this traveller.'];
            }
            if (!$context['eligible']) throw new RuntimeException($context['eligibility_message']);
            if (!hash_equals($context['snapshot_token'], $submission['snapshot_token'])) {
                throw new RuntimeException('The booking or payout amounts changed. Reopen the payout details and confirm the updated amount.');
            }
            $recordedAt = $this->now();
            foreach ($context['currencies'] as $currency => $group) {
                if ($group['amount'] === '0.00') continue;
                if (!$this->db->insert('traveller_payouts', [
                    'operation_key'=>$prefix . $currency, 'opening_balance_id'=>null,
                    'traveller_id'=>(int) $travellerId, 'traveller_name'=>$context['traveller_name'],
                    'currency'=>$currency, 'amount'=>$group['amount'], 'source'=>'manual', 'admin_id'=>(int) $adminId,
                    'paid_at'=>null, 'recorded_at'=>$recordedAt,
                    'note'=>'Super Admin confirmed the traveller was paid after arrival and shipping was arranged for every active booking.',
                ])) throw new RuntimeException('Unable to record payout history.');
                $payoutId = (int) $this->db->insert_id();
                foreach ($group['items'] as $item) {
                    if (!$this->db->insert('traveller_payout_items', [
                        'payout_id'=>$payoutId, 'booking_id'=>$item['booking_id'], 'booking_reference'=>$item['booking_reference'],
                        'traveller_id'=>(int) $travellerId, 'currency'=>$currency,
                        'commission_snapshot'=>$item['commission'], 'amount'=>$item['amount'],
                    ])) throw new RuntimeException('Unable to record payout allocations.');
                }
            }
            if (!$this->db->trans_status() || !$this->db->trans_commit()) throw new RuntimeException('Unable to commit the payout record.');
            return ['status'=>true, 'already_recorded'=>false, 'msg'=>'Traveller payout recorded successfully.'];
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Traveller payout recording failed: ' . $error->getMessage());
            return ['status'=>false, 'msg'=>$error instanceof RuntimeException ? $error->getMessage() : 'Unable to save the payout record. Please try again.'];
        }
    }

    private function travellerPayoutState($travellerId, $lock)
    {
        $ids = $this->db->select('id')->where('traveller_id', $travellerId)->where('payment_status', 'completed')
            ->where('deleted_at IS NULL', null, false)->order_by('id')->get('bookings')->result();
        $ids = array_map(function ($row) { return (int) $row->id; }, $ids);
        $shipping = [];
        if ($ids) {
            $this->db->from('shipping_records')->where_in('booking_id', $ids)->order_by('booking_id');
            $shipping = $this->db->query($this->db->get_compiled_select() . ($lock ? $this->lockClause() : ''))->result();
        }
        // Match existing cancellation/move lock order: shipping -> bookings -> traveller.
        $this->db->from('bookings')->where('traveller_id', $travellerId)->where('payment_status', 'completed')
            ->where('deleted_at IS NULL', null, false)->order_by('id');
        $bookings = $this->db->query($this->db->get_compiled_select() . ($lock ? $this->lockClause() : ''))->result();
        $actualIds = array_map(function ($row) { return (int) $row->id; }, $bookings);
        if ($lock && $ids !== $actualIds) throw new RuntimeException('Bookings changed. Reopen the payout details before confirming.');
        $traveller = $this->db->query('SELECT * FROM travellers WHERE id = ?' . ($lock ? $this->lockClause() : ''), [$travellerId])->row();
        $allocations = [];
        if ($actualIds) {
            $this->db->from('traveller_payout_items')->where_in('booking_id', $actualIds)->order_by('id');
            $allocations = $this->db->query($this->db->get_compiled_select() . ($lock ? $this->lockClause() : ''))->result();
        }
        return ['traveller'=>$traveller, 'bookings'=>$bookings, 'shipping'=>$shipping, 'allocations'=>$allocations];
    }

    private function buildPayoutContext(array $state)
    {
        $traveller = $state['traveller'];
        $groups = ['GBP'=>['amount_minor'=>0,'items'=>[]], 'CAD'=>['amount_minor'=>0,'items'=>[]]];
        $paid = [];
        $review = false;
        foreach ($state['allocations'] as $item) {
            $key = $item->booking_id . ':' . $item->traveller_id . ':' . $item->currency;
            $amount = $this->minorAmount($item->amount);
            if ($amount === null) $review = true;
            $paid[$key] = ($paid[$key] ?? 0) + ($amount ?? 0);
        }
        $shipping = [];
        foreach ($state['shipping'] as $record) $shipping[(int) $record->booking_id] = (int) $record->id;
        $snapshot = [];
        $allShipping = count($state['bookings']) > 0;
        foreach ($state['bookings'] as $booking) {
            if (!isset($shipping[(int) $booking->id])) $allShipping = false;
            $currency = $this->currency($booking->currency);
            $commission = $this->minorAmount($booking->traveller_commission);
            if (!$currency || $commission === null || ($booking->payment_method !== null && !in_array(strtolower($booking->payment_method), ['paystack','stripe','offline','bank'], true))) {
                $review = true;
                continue;
            }
            $key = $booking->id . ':' . $booking->traveller_id . ':' . $currency;
            $settled = min($commission, $paid[$key] ?? 0);
            $owing = $commission - $settled;
            $snapshot[] = [(int) $booking->id, (int) $booking->traveller_id, $currency, $commission, $settled, $shipping[(int) $booking->id] ?? null];
            if ($owing > 0) {
                $groups[$currency]['amount_minor'] += $owing;
                $groups[$currency]['items'][] = ['booking_id'=>(int) $booking->id, 'booking_reference'=>$booking->tracking_id,
                    'commission'=>$this->decimalAmount($commission), 'amount'=>$this->decimalAmount($owing)];
            }
        }
        $total = $groups['GBP']['amount_minor'] + $groups['CAD']['amount_minor'];
        foreach ($groups as &$group) {
            $group['amount'] = $this->decimalAmount($group['amount_minor']);
            $group['booking_count'] = count($group['items']);
            unset($group['amount_minor']);
        }
        unset($group);
        $travelDate = $traveller ? $this->validDate($traveller->travel_date) : null;
        $arrivalDate = $traveller ? $this->validDate($traveller->arrival_date) : null;
        $arrived = $travelDate && $arrivalDate && $travelDate < $this->businessDate()
            && $arrivalDate >= $travelDate && $arrivalDate <= $this->businessDate();
        $reason = '';
        if (!$traveller || !empty($traveller->deleted_at)) $reason = 'Traveller is unavailable.';
        elseif (!$state['bookings']) $reason = 'This traveller has no active completed bookings.';
        elseif ($review) $reason = 'Review the booking currency or commission before recording this payout.';
        elseif ($total === 0) $reason = 'No outstanding payout for this traveller.';
        elseif (!$arrived) $reason = 'Payouts can only be recorded after the traveller has arrived.';
        elseif (!$allShipping) $reason = 'Arrange shipping for every active booking before recording the payout.';
        return [
            'traveller_id'=>$traveller ? (int) $traveller->id : 0, 'traveller_name'=>$traveller ? (string) $traveller->fullname : '',
            'eligible'=>$reason === '', 'eligibility_message'=>$reason, 'paid'=>$total === 0 && !$review && count($state['bookings']) > 0,
            'currencies'=>$groups, 'booking_count'=>count($state['bookings']), 'shipping_arranged'=>$allShipping, 'arrived'=>(bool) $arrived,
            'snapshot_token'=>hash('sha256', json_encode([$traveller ? $traveller->id : null, $travelDate, $arrivalDate, $snapshot])),
        ];
    }

    private function buildOpeningPreview($lock, array $declaredPaidTravellers)
    {
        $declaredPaidTravellers = array_values(array_unique(array_filter(array_map('intval', $declaredPaidTravellers), function ($id) { return $id > 0; })));
        sort($declaredPaidTravellers, SORT_NUMERIC);
        $max = $this->db->select_max('id')->get('bookings')->row();
        $snapshotId = (int) ($max->id ?? 0);
        $preview = [
            'opening_date'=>self::OPENING_DATE, 'snapshot_booking_id'=>$snapshotId,
            'requires_historical_review'=>$this->businessDate() > self::OPENING_DATE,
            'declared_paid_traveller_ids'=>$declaredPaidTravellers,
            'basis'=>'Owner declared all historical travellers paid except upcoming travellers and the arrival on 13 November 2026. Unknown dates are held for review. Actual historical payment dates are unknown.',
            'paid_booking_ids'=>[], 'outstanding_booking_ids'=>[], 'review_booking_ids'=>[],
            'outstanding_reasons'=>[], 'review_reasons'=>[], 'paid_groups'=>[],
        ];
        $groups = [];
        foreach ($this->currentBookings($lock, $snapshotId) as $booking) {
            $id = (int) $booking->id;
            $classification = $this->openingClassification($booking, $declaredPaidTravellers);
            $preview[$classification[0] . '_booking_ids'][] = $id;
            if ($classification[0] !== 'paid') {
                $preview[$classification[0] . '_reasons'][$id] = $classification[1];
                continue;
            }
            $currency = $this->currency($booking->currency);
            $key = $booking->traveller_id . ':' . $currency;
            if (!isset($groups[$key])) {
                $groups[$key] = ['traveller_id'=>(int) $booking->traveller_id, 'traveller_name'=>$booking->fullname,
                    'currency'=>$currency, 'amount_minor'=>0, 'items'=>[]];
            }
            $groups[$key]['amount_minor'] += $this->minorAmount($booking->traveller_commission);
            $groups[$key]['items'][] = ['booking_id'=>$id, 'booking_reference'=>$booking->tracking_id,
                'amount'=>$this->decimalAmount($this->minorAmount($booking->traveller_commission))];
        }
        foreach ($groups as $group) {
            $group['amount'] = $this->decimalAmount($group['amount_minor']);
            unset($group['amount_minor']);
            $preview['paid_groups'][] = $group;
        }
        return $preview;
    }

    private function openingClassification($booking, array $declaredPaidTravellers)
    {
        if (!$this->currency($booking->currency) || $this->minorAmount($booking->traveller_commission) === null) {
            return ['review','Unsupported currency or invalid stored commission.'];
        }
        if (!$booking->current_traveller_id || !empty($booking->traveller_deleted_at)) {
            return ['review','Traveller is missing or archived.'];
        }
        $bookingDate = $this->validDate($booking->date_added);
        if (!$bookingDate) return ['review','Booking date is missing or invalid.'];
        if ($bookingDate > self::OPENING_DATE) return ['outstanding','Booking created after the opening date.'];
        $travelDate = $this->validDate($booking->travel_date);
        $arrivalDate = $this->validDate($booking->arrival_date);
        if ($travelDate && $travelDate >= self::OPENING_DATE) return ['outstanding','Upcoming on the opening date.'];
        if ($arrivalDate && $arrivalDate >= self::OPENING_DATE) return ['outstanding','Arrival on or after the opening date.'];
        // Explicit historical declarations can resolve missing arrival information,
        // but can never bypass the upcoming/cutoff-day arrival exclusions above.
        if ($travelDate && in_array((int) $booking->traveller_id, $declaredPaidTravellers, true)) {
            return ['paid','Historical traveller explicitly declared paid by owner.'];
        }
        if (!$travelDate || !$arrivalDate || $arrivalDate < $travelDate) return ['review','Travel or arrival date is missing, invalid or inconsistent.'];
        return ['paid','Historical settlement declared by owner.'];
    }

    private function currentBookings($lock = false, $maximumId = null)
    {
        $this->db->select('bookings.id, bookings.traveller_id, bookings.tracking_id, bookings.traveller_commission,
            bookings.currency, bookings.date_added, travellers.id AS current_traveller_id, travellers.fullname,
            travellers.travel_date, travellers.arrival_date, travellers.deleted_at AS traveller_deleted_at', false);
        $this->db->from('bookings')->join('travellers', 'travellers.id = bookings.traveller_id', 'left');
        $this->db->where('bookings.payment_status', 'completed')->where('bookings.deleted_at IS NULL', null, false);
        $this->db->where("(LOWER(COALESCE(bookings.payment_method, '')) IN ('paystack','stripe','offline','bank') OR bookings.payment_method IS NULL)", null, false);
        if ($maximumId !== null) $this->db->where('bookings.id <=', (int) $maximumId);
        $this->db->order_by('bookings.id', 'asc');
        return $this->db->query($this->db->get_compiled_select() . ($lock ? $this->lockClause() : ''))->result();
    }

    private function insertOpeningPayout(array $group, $recordedAt)
    {
        if (!$this->db->insert('traveller_payouts', [
            'operation_key'=>'opening:' . self::OPENING_DATE . ':' . $group['traveller_id'] . ':' . $group['currency'],
            'opening_balance_id'=>1, 'traveller_id'=>$group['traveller_id'], 'traveller_name'=>$group['traveller_name'],
            'currency'=>$group['currency'], 'amount'=>$group['amount'], 'source'=>'opening_balance',
            'admin_id'=>null, 'paid_at'=>null, 'recorded_at'=>$recordedAt,
            'note'=>'Historical payout declared paid by owner as of ' . self::OPENING_DATE . '. Actual payment date unknown.',
        ])) throw new RuntimeException('Payout history insert failed.');
        $payoutId = (int) $this->db->insert_id();
        foreach ($group['items'] as $item) {
            if (!$this->db->insert('traveller_payout_items', [
                'payout_id'=>$payoutId, 'booking_id'=>$item['booking_id'], 'booking_reference'=>$item['booking_reference'],
                'traveller_id'=>$group['traveller_id'], 'currency'=>$group['currency'],
                'commission_snapshot'=>$item['amount'], 'amount'=>$item['amount'],
            ])) throw new RuntimeException('Payout allocation insert failed.');
        }
    }

    private function lockClause()
    {
        return $this->db->dbdriver === 'sqlite3' ? '' : ' FOR UPDATE';
    }

    private function currency($value)
    {
        $value = strtoupper(trim((string) $value));
        if (in_array($value, ['', 'GBP', 'POU', 'POUND', 'POUNDS'], true)) return 'GBP';
        if (in_array($value, ['CAD', 'DOLLAR', 'DOLLARS'], true)) return 'CAD';
        return null;
    }

    // Integer minor units preserve cents without floating-point arithmetic.
    private function minorAmount($value)
    {
        if (!preg_match('/^(\d{1,14})(?:\.(\d{1,2}))?$/D', (string) $value, $match)) return null;
        return (int) $match[1] * 100 + (int) str_pad($match[2] ?? '', 2, '0');
    }

    private function decimalAmount($minor)
    {
        return intdiv($minor, 100) . '.' . str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    private function validDate($value)
    {
        $value = trim((string) $value);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}:\d{2})?$/D', $value)) return null;
        if (!checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4))) return null;
        $format = strlen($value) === 10 ? 'Y-m-d' : 'Y-m-d H:i:s';
        $parsed = DateTimeImmutable::createFromFormat('!' . $format, $value);
        return $parsed && $parsed->format($format) === $value ? substr($value, 0, 10) : null;
    }

    private function businessDate()
    {
        return (new DateTimeImmutable($this->now(), new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Africa/Lagos'))->format('Y-m-d');
    }

    protected function now()
    {
        return gmdate('Y-m-d H:i:s');
    }
}
