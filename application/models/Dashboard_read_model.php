<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_read_model extends MY_Model
{
    private function sources($role)
    {
        $sources = array();
        if (in_array($role, array('super_admin', 'customer_support'), true)) {
            $sources['users'] = array('date' => 'date_registered', 'label' => 'New user registered', 'color' => 'users');
            $sources['bookings'] = array('date' => 'date_added', 'label' => 'Paid booking created', 'color' => 'bookings');
        }
        if (in_array($role, array('super_admin', 'customer_support', 'traveller_support'), true)) {
            $sources['travellers'] = array('date' => 'date_added', 'label' => 'Traveller submitted', 'color' => 'travellers');
        }
        return $sources;
    }

    private function sourceQuery($table, $role)
    {
        $this->db->from($table);
        $this->applyNotDeleted($table);
        if ($table === 'bookings') {
            $this->db->where('payment_status', 'completed');
        } elseif ($table === 'travellers' && $role === 'customer_support') {
            $this->db->where('status', 'Approved');
            $this->db->where('travel_date >=', date('Y-m-d'));
        }
    }

    public function get_overview($role, $days = 7, $includeActivity = true)
    {
        $days = (int) $days === 30 ? 30 : 7;
        $sources = $this->sources($role);
        $today = new DateTimeImmutable('today');
        $start = $today->modify('-29 days');
        $end = $today->modify('+1 day')->format('Y-m-d');
        $daily = array();
        for ($i = 0; $includeActivity && $i < 30; $i++) {
            $date = $start->modify('+' . $i . ' days');
            $daily[$date->format('Y-m-d')] = array('label' => $date->format('j M'), 'values' => array_fill_keys(array_keys($sources), 0));
        }
        $recent = array();
        foreach ($sources as $table => $source) {
            if (!$includeActivity) continue;

            $this->sourceQuery($table, $role);
            $this->db->select('DATE(' . $source['date'] . ') AS activity_date, COUNT(*) AS total', false);
            $this->db->where($source['date'] . ' >=', $start->format('Y-m-d'));
            $this->db->where($source['date'] . ' <', $end);
            $this->db->group_by('DATE(' . $source['date'] . ')', false);
            foreach ($this->db->get()->result() as $row) {
                if (isset($daily[$row->activity_date])) $daily[$row->activity_date]['values'][$table] = (int) $row->total;
            }

            $this->sourceQuery($table, $role);
            $names = array('users' => 'firstname, lastname', 'bookings' => 'user_fullname AS fullname, tracking_id, selected_space, traveller_destination', 'travellers' => 'fullname, status, travel_date, location, destination');
            $this->db->where($source['date'] . ' >=', date('Y-m-d H:i:s', strtotime('-7 days')));
            $this->db->where($source['date'] . ' <=', date('Y-m-d H:i:s'));
            $this->db->select('id, ' . $names[$table] . ', ' . $source['date'] . ' AS occurred_at');
            $this->db->order_by($source['date'], 'desc')->order_by('id', 'desc')->limit(10);
            foreach ($this->db->get()->result() as $row) {
                $url = $table === 'users' ? 'admin_users/user_profile/' : ($table === 'bookings' ? 'admin_bookings/view_booking/' : 'admin_travellers/traveller_profile/');
                // Customer Support must never receive links to hidden traveler records.
                $name = $table === 'users' ? trim($row->firstname . ' ' . $row->lastname) : $row->fullname;
                $details = $name . ' joined ShareMyBag';
                $origin = '';
                $destination = '';
                $bookingPrefix = '';
                if ($table === 'bookings') {
                    $parts = preg_split('/\s+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: array();
                    $name = $parts ? $parts[0] : 'A customer';
                    if (count($parts) > 1) $name .= ' ' . mb_strtoupper(mb_substr(end($parts), 0, 1)) . '.';
                    $weight = (float) $row->selected_space > 0 ? rtrim(rtrim(number_format((float) $row->selected_space, 2, '.', ''), '0'), '.') . ' kg ' : '';
                    $bookingPrefix = $name . ' booked a ' . $weight . 'parcel';
                    $destination = trim((string) $row->traveller_destination);
                    $details = $bookingPrefix . ($destination !== '' ? ' to ' . $destination : '');
                } elseif ($table === 'travellers') {
                    $origin = trim((string) $row->location);
                    $destination = trim((string) $row->destination);
                    $route = implode(' → ', array_filter(array($origin, $destination), 'strlen'));
                    $details = $name . ($route !== '' ? ' · ' . $route : '');
                }
                $recent[] = array('type' => $table, 'activity' => $source['label'], 'name' => $name, 'details' => $details, 'booking_prefix' => $bookingPrefix, 'origin' => $origin, 'destination' => $destination, 'reference' => $table === 'bookings' ? $row->tracking_id : '', 'occurred_at' => $row->occurred_at, 'id' => (int) $row->id, 'url' => $url . (int) $row->id);
            }
        }
        usort($recent, function ($a, $b) {
            $dateOrder = strcmp((string) $b['occurred_at'], (string) $a['occurred_at']);
            return $dateOrder ?: ($b['id'] <=> $a['id']) ?: strcmp($a['type'], $b['type']);
        });
        $attention = $this->attention($role);
        $tiles = $this->summaryTiles($role);
        foreach ($tiles as &$tile) $tile['col'] = count($tiles) === 4 ? 3 : (count($tiles) === 3 ? 4 : (count($tiles) === 2 ? 6 : 12));
        unset($tile);
        $attention = array_values(array_filter($attention, function ($item) { return (int) $item['count'] > 0; }));
        return array('tiles' => $tiles, 'sources' => $sources, 'chart_daily' => $daily, 'daily' => array_slice($daily, -$days, null, true), 'days' => $days, 'recent' => $recent, 'attention' => $attention);
    }

    private function summaryTiles($role)
    {
        $tiles = array();
        if (in_array($role, array('super_admin', 'customer_support'), true)) {
            $this->sourceQuery('users', $role);
            $tiles[] = array('icon' => 'las la-users', 'value' => number_format($this->db->count_all_results()), 'label' => 'Total Users');
        }
        if (in_array($role, array('super_admin', 'traveller_support'), true)) {
            $this->sourceQuery('travellers', $role);
            $this->db->where('status', 'Approved');
            $tiles[] = array('icon' => 'las la-suitcase-rolling', 'value' => number_format($this->db->count_all_results()), 'label' => 'Approved Travellers');
            $this->sourceQuery('travellers', $role);
            $this->db->where('status', 'Pending');
            $tiles[] = array('icon' => 'las la-history', 'value' => number_format($this->db->count_all_results()), 'label' => 'Pending Travellers');
        } elseif ($role === 'customer_support') {
            // This role can see upcoming travellers only; retain its existing scope.
            $this->sourceQuery('travellers', $role);
            $this->db->where('travel_date <', date('Y-m-d', strtotime('+7 days')));
            $tiles[] = array('icon' => 'las la-suitcase-rolling', 'value' => number_format($this->db->count_all_results()), 'label' => 'Upcoming Travellers');
        }
        if (in_array($role, array('super_admin', 'customer_support'), true)) {
            // The original Total Bookings card counted completed bookings.
            $this->sourceQuery('bookings', $role);
            $tiles[] = array('icon' => 'las la-briefcase', 'value' => number_format($this->db->count_all_results()), 'label' => 'Total Bookings');
        }
        return $tiles;
    }

    private function attention($role)
    {
        $items = array();
        if ($role === 'super_admin') {
            $this->sourceQuery('users', $role);
            $this->db->where('is_verified', VERIFY_PENDING);
            $items[] = array('label' => 'Pending Users', 'count' => (int) $this->db->count_all_results(), 'url' => 'admin_users/pending_users');
        }
        if (in_array($role, array('super_admin', 'traveller_support'), true)) {
            $this->sourceQuery('travellers', $role);
            $this->db->where('status', 'Pending');
            $items[] = array('label' => 'Pending Travellers', 'count' => (int) $this->db->count_all_results(), 'url' => 'admin_travellers/pending_travellers');
        }
        if ($role !== 'customer_support' && admin_shipping_access_allowed(get_instance()->admin_details)) {
            $this->db->from('bookings');
            $this->db->join('travellers', 'travellers.id = bookings.traveller_id');
            $this->db->join('shipping_records', 'shipping_records.booking_id = bookings.id', 'left');
            $this->applyNotDeleted('bookings');
            $this->applyNotDeleted('travellers');
            $this->db->where('bookings.payment_status', 'completed');
            $this->db->where('travellers.travel_date <', date('Y-m-d'));
            $this->db->where('shipping_records.id IS NULL', null, false);
            $items[] = array('label' => 'Parcels Awaiting Shipping', 'count' => (int) $this->db->count_all_results(), 'url' => 'shipping/arrivals');
        }
        if ($role === 'customer_support') {
            $this->load->model('user_read_model');
            $items[] = array('label' => 'New Signups · Last 5 Days', 'count' => $this->user_read_model->count_recent_signups(), 'url' => 'admin_users');
        }
        return $items;
    }
}
