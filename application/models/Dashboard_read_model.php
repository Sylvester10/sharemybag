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

    public function get_overview($role, $days = 7)
    {
        $days = (int) $days === 30 ? 30 : 7;
        $sources = $this->sources($role);
        $today = new DateTimeImmutable('today');
        $start = $today->modify('-29 days');
        $end = $today->modify('+1 day')->format('Y-m-d');
        $daily = array();
        for ($i = 0; $i < 30; $i++) {
            $date = $start->modify('+' . $i . ' days');
            $daily[$date->format('Y-m-d')] = array('label' => $date->format('j M'), 'values' => array_fill_keys(array_keys($sources), 0));
        }
        $tiles = array();
        $recent = array();
        foreach ($sources as $table => $source) {
            $this->sourceQuery($table, $role);
            if ($table === 'travellers') {
                $this->db->where('status', 'Approved');
                $this->db->where('travel_date >=', $today->format('Y-m-d'));
                $this->db->where('travel_date <', $today->modify('+7 days')->format('Y-m-d'));
            }
            $count = (int) $this->db->count_all_results();
            $labels = array('users' => 'Total Users', 'bookings' => 'Completed Bookings', 'travellers' => 'Upcoming Travellers');
            $icons = array('users' => 'las la-users', 'bookings' => 'las la-briefcase', 'travellers' => 'las la-suitcase-rolling');
            $tiles[] = array('icon' => $icons[$table], 'value' => number_format($count), 'label' => $labels[$table], 'col' => count($sources) === 1 ? 12 : 4);
            if ($table === 'users') {
                $this->load->model('user_read_model');
                $tiles[count($tiles) - 1]['detail'] = $this->user_read_model->count_recent_signups() . ' new in the last 5 days';
            } elseif ($table === 'bookings') {
                $this->sourceQuery($table, $role);
                $this->db->where('date_added >=', $today->format('Y-m-d'))->where('date_added <', $end);
                $tiles[count($tiles) - 1]['detail'] = $this->db->count_all_results() . ' created today';
            } else {
                $tiles[count($tiles) - 1]['detail'] = 'Departures in the next 7 days';
            }

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
                if ($table === 'bookings') {
                    $details = $row->tracking_id;
                    if ((float) $row->selected_space > 0) $details .= ' · ' . (float) $row->selected_space . ' kg';
                    if (trim((string) $row->traveller_destination) !== '') $details .= ' → ' . $row->traveller_destination;
                } elseif ($table === 'travellers') {
                    $route = implode(' → ', array_filter(array(trim((string) $row->location), trim((string) $row->destination)), 'strlen'));
                    $details = $name . ($route !== '' ? ' · ' . $route : '');
                }
                $recent[] = array('type' => $table, 'activity' => $source['label'], 'name' => $name, 'details' => $details, 'reference' => $table === 'bookings' ? $row->tracking_id : '', 'occurred_at' => $row->occurred_at, 'id' => (int) $row->id, 'url' => $url . (int) $row->id);
            }
        }
        usort($recent, function ($a, $b) {
            $dateOrder = strcmp((string) $b['occurred_at'], (string) $a['occurred_at']);
            return $dateOrder ?: ($b['id'] <=> $a['id']) ?: strcmp($a['type'], $b['type']);
        });
        $attention = $this->attention($role);
        foreach ($attention as $item) {
            if ($item['url'] === 'shipping/arrivals') $tiles[] = array('icon' => 'las la-box', 'label' => 'Needs Shipping', 'value' => number_format($item['count']), 'detail' => 'Paid parcels awaiting arrangement');
        }
        foreach ($tiles as &$tile) $tile['col'] = count($tiles) === 4 ? 3 : (count($tiles) === 3 ? 4 : (count($tiles) === 2 ? 6 : 12));
        unset($tile);
        $attention = array_values(array_filter($attention, function ($item) { return (int) $item['count'] > 0; }));
        return array('tiles' => $tiles, 'sources' => $sources, 'chart_daily' => $daily, 'daily' => array_slice($daily, -$days, null, true), 'days' => $days, 'recent' => $recent, 'attention' => $attention);
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
        if (admin_shipping_access_allowed(get_instance()->admin_details)) {
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
