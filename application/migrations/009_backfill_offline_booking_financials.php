<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Backfill_offline_booking_financials extends CI_Migration
{
    public function up()
    {
        $bookings = $this->db
            ->select('id, traveller_id, traveller_current_state, traveller_destination, traveller_arrival_state, selected_space, payment_method, payment_status, currency, selected_price, service_charge, sub_total, traveller_commission, vat, total_amount, items, tracking_id')
            ->from('bookings')
            ->group_start()
                ->where('LOWER(COALESCE(payment_method, "")) IN ("offline", "bank")', null, false)
                ->or_where('payment_method IS NULL', null, false)
            ->group_end()
            ->where('LOWER(COALESCE(payment_status, "")) IN ("completed","complete","success","paid")', null, false)
            ->get()
            ->result();

        foreach ($bookings as $booking) {
            $selectedSpace = (float) $booking->selected_space;
            if ($selectedSpace <= 0) {
                continue;
            }

            $traveller = $this->db
                ->select('location, destination')
                ->from('travellers')
                ->where('id', (int) $booking->traveller_id)
                ->limit(1)
                ->get()
                ->row();

            $origin = $traveller && !empty($traveller->location)
                ? trim((string) $traveller->location)
                : trim((string) $booking->traveller_current_state);

            $destination = $traveller && !empty($traveller->destination)
                ? trim((string) $traveller->destination)
                : trim((string) ($booking->traveller_destination ?: $booking->traveller_arrival_state));

            $pricing = $this->routePricing($origin, $destination);
            $currency = $this->routeCurrency($origin, $destination);
            $selectedPrice = round($pricing['normal_rate'] * $selectedSpace, 2);
            $serviceCharge = round((float) $pricing['service_charge'], 2);
            $travellerCommission = round($pricing['normal_payout_rate'] * $selectedSpace, 2);
            $subTotal = round($selectedPrice + $serviceCharge, 2);
            $vat = 0.00;
            $totalAmount = round($subTotal + $vat, 2);
            $items = json_encode(array(
                (object) array(
                    'item_name' => 'Offline Bag Space',
                    'category' => 'Normal',
                    'size' => $selectedSpace,
                    'price' => $selectedPrice,
                )
            ));

            $update = array();

            if (empty($booking->tracking_id)) {
                $update['tracking_id'] = $this->generateUniqueTrackingId();
            }
            if (empty($booking->traveller_destination) && $destination !== '') {
                $update['traveller_destination'] = $destination;
            }
            if (empty($booking->currency)) {
                $update['currency'] = $currency;
            }
            if ((float) $booking->selected_price <= 0) {
                $update['selected_price'] = $selectedPrice;
            }
            if ((float) $booking->service_charge <= 0) {
                $update['service_charge'] = $serviceCharge;
            }
            if ((float) $booking->sub_total <= 0) {
                $update['sub_total'] = $subTotal;
            }
            if ((float) $booking->traveller_commission <= 0) {
                $update['traveller_commission'] = $travellerCommission;
            }
            if ($booking->vat === null || $booking->vat === '') {
                $update['vat'] = $vat;
            }
            if ((float) $booking->total_amount <= 0) {
                $update['total_amount'] = $totalAmount;
            }
            if (empty($booking->items)) {
                $update['items'] = $items;
            }
            if (empty($booking->payment_method)) {
                $update['payment_method'] = 'offline';
            }

            if (!empty($update)) {
                $this->db->where('id', (int) $booking->id)->update('bookings', $update);
            }
        }
    }

    public function down()
    {
        // One-way data repair. No rollback.
    }

    private function routeKey($origin, $destination)
    {
        $origin = trim((string) $origin);
        $destination = trim((string) $destination);

        $map = array(
            'Nigeria|United Kingdom' => 'ng_uk',
            'United Kingdom|Nigeria' => 'uk_ng',
            'Nigeria|Canada' => 'ng_ca',
            'Canada|Nigeria' => 'ca_ng',
        );

        $key = $origin . '|' . $destination;
        return isset($map[$key]) ? $map[$key] : 'default';
    }

    private function routeCurrency($origin, $destination)
    {
        return in_array($this->routeKey($origin, $destination), array('ng_ca', 'ca_ng'), true) ? 'CAD' : 'GBP';
    }

    private function routePricing($origin, $destination)
    {
        $defaults = array(
            'service_charge' => 3.99,
            'normal_rate' => 8.50,
            'normal_payout_rate' => 5.00,
        );

        switch ($this->routeKey($origin, $destination)) {
            case 'ng_uk':
                return array_merge($defaults, array(
                    'service_charge' => 3.49,
                    'normal_rate' => 9.50,
                    'normal_payout_rate' => 5.00,
                ));

            case 'uk_ng':
                return array_merge($defaults, array(
                    'service_charge' => 3.99,
                    'normal_rate' => 6.50,
                    'normal_payout_rate' => 4.50,
                ));

            case 'ca_ng':
                return array_merge($defaults, array(
                    'service_charge' => 6.44,
                    'normal_rate' => 17.50,
                    'normal_payout_rate' => 10.00,
                ));

            case 'ng_ca':
                return array_merge($defaults, array(
                    'service_charge' => 3.99,
                    'normal_rate' => 17.50,
                    'normal_payout_rate' => 10.00,
                ));

            default:
                return $defaults;
        }
    }

    private function generateUniqueTrackingId()
    {
        do {
            $code = 'SMB' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 7));
            $exists = $this->db->where('tracking_id', $code)->count_all_results('bookings') > 0;
        } while ($exists);

        return $code;
    }
}
