<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shipping_records_ajax extends CI_Model
{
    private $table = 'shipping_records';
    private $column_order = array(
        null,
        'shipping_records.staff_name',
        'admins.role',
        'bookings.user_fullname',
        'bookings.traveller_name',
        'shipping_records.pickup_address',
        'shipping_records.dropoff_address',
        'shipping_records.pickup_country',
        'shipping_records.courier',
        'shipping_records.carrier_tracking_id',
        'shipping_records.status',
        'shipping_records.date_added',
    );
    private $column_search = array(
        'shipping_records.staff_name',
        'admins.role',
        'bookings.user_fullname',
        'users.number',
        'bookings.user_email',
        'bookings.traveller_name',
        'bookings.traveller_email',
        'shipping_records.pickup_address',
        'shipping_records.dropoff_address',
        'shipping_records.pickup_country',
        'shipping_records.courier',
        'shipping_records.carrier_tracking_id',
        'bookings.tracking_id',
        'shipping_records.status',
    );
    private $order = array('bookings.date_added' => 'desc');

    private function the_query()
    {
        $search_value = datatable_search_value();

        $this->db->select('
            bookings.id AS booking_id,
            bookings.user_fullname,
            bookings.user_email,
            users.number AS user_phone,
            bookings.traveller_name,
            bookings.traveller_contact,
            bookings.traveller_email,
            bookings.agent_name,
            bookings.agent_phone,
            bookings.agent_email,
            bookings.agent_address,
            bookings.agent_locality,
            bookings.agent_postcode,
            bookings.receiver_name,
            bookings.receiver_phone,
            bookings.receiver_email,
            bookings.receiver_address,
            bookings.receiver_locality,
            bookings.receiver_postcode,
            bookings.tracking_id,
            bookings.delivery_status AS booking_delivery_status,
            bookings.date_added AS booking_date_added,
            shipping_records.id AS shipping_record_id,
            shipping_records.carrier_tracking_id,
            shipping_records.pickup_address,
            shipping_records.dropoff_address,
            shipping_records.pickup_country,
            shipping_records.courier,
            shipping_records.staff_name,
            admins.role AS staff_role,
            shipping_records.status,
            shipping_records.date_added
        ');
        $this->db->from('shipping_records');
        $this->db->join('bookings', 'bookings.id = shipping_records.booking_id', 'inner');
        $this->db->join('users', 'users.id = bookings.user_id', 'left');
        $this->db->join('admins', 'admins.id = shipping_records.staff_admin_id', 'left');
        ci_where_not_deleted($this->db, 'bookings');
        ci_where_not_deleted($this->db, 'users');
        $this->db->where('bookings.payment_status', 'completed');

        $i = 0;
        foreach ($this->column_search as $item) {
            if ($search_value !== '') {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
                if (count($this->column_search) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        if (isset($_POST['order']['0']['column'], $_POST['order']['0']['dir'])) {
            $order_column_index = (int) $_POST['order']['0']['column'];
            $order_direction = strtolower($_POST['order']['0']['dir']) === 'asc' ? 'asc' : 'desc';
            if (isset($this->column_order[$order_column_index]) && $this->column_order[$order_column_index] !== null) {
                $this->db->order_by($this->column_order[$order_column_index], $order_direction);
            }
        } else {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function get_records()
    {
        $this->the_query();
        $length = isset($_POST['length']) ? (int) $_POST['length'] : 10;
        $start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
        if ($length !== -1) {
            $this->db->limit($length, $start);
        }
        return $this->db->get()->result();
    }

    public function count_filtered_records()
    {
        $this->the_query();
        return $this->db->count_all_results();
    }

    public function count_all_records()
    {
        $this->db->from('shipping_records');
        $this->db->join('bookings', 'bookings.id = shipping_records.booking_id', 'inner');
        ci_where_not_deleted($this->db, 'bookings');
        $this->db->where('bookings.payment_status', 'completed');
        return $this->db->count_all_results();
    }
}
