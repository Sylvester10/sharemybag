<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

class Shipping_read_model extends \MY_Model
{
    const SHIPPING_COUNT_CACHE_TTL = 300;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'shipping_records';
        $this->primary_cols = array('id');
    }

    public function get_all_shippings()
    {
        $this->db->order_by('date_added', 'desc');
        return $this->db->get('shipping')->result();
    }

    public function count_all_shippings()
    {
        return $this->getShippingDashboardSummary()->created_records;
    }

    public function clearShippingCountCaches()
    {
        $this->forgetCache('shipping.summary.dashboard');
    }

    public function get_shipping_by_tracking_id($trackingId)
    {
        $this->db->where('tracking_id', $trackingId);
        $this->db->order_by('date_added', 'desc');
        return $this->db->get('shipping')->result();
    }

    public function get_shipping_details_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('shipping')->row();
    }

    public function get_shipping_by_tracking_id_row($trackingId)
    {
        $this->db->where('tracking_id', $trackingId);
        return $this->db->get('shipping')->row();
    }

    public function get_shipping_details_by_tracking_id($trackingId)
    {
        $this->db->where('tracking_id', $trackingId);
        $this->db->order_by('date_added', 'desc');
        return $this->db->get('shipping')->result();
    }

    public function get_shipping_record_by_booking_id($bookingId)
    {
        $this->db->where('booking_id', $bookingId);
        return $this->db->get('shipping_records')->row();
    }

    public function get_shipping_record_by_tracking_id($trackingId)
    {
        $this->db->where('tracking_id', $trackingId);
        return $this->db->get('shipping_records')->row();
    }

    public function get_shipping_record_view($bookingId)
    {
        $this->db->select('
            shipping_records.*,
            bookings.user_fullname,
            bookings.user_email,
            users.number AS user_phone,
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
            bookings.traveller_name,
            bookings.traveller_contact,
            bookings.traveller_email,
            bookings.tracking_id AS booking_tracking_id,
            bookings.date_added AS booking_date_added,
            admins.email AS staff_email,
            admins.role AS staff_role
        ');
        $this->db->from('shipping_records');
        $this->db->join('bookings', 'bookings.id = shipping_records.booking_id', 'inner');
        $this->db->join('users', 'users.id = bookings.user_id', 'left');
        $this->db->join('admins', 'admins.id = shipping_records.staff_admin_id', 'left');
        $this->db->where('shipping_records.booking_id', $bookingId);
        $this->applyNotDeleted('bookings');
        $this->applyNotDeleted('users');
        $this->db->where('bookings.payment_status', 'completed');
        return $this->db->get()->row();
    }

    public function get_booking_shipping_context($bookingId)
    {
        $this->db->select('
            bookings.id,
            bookings.tracking_id,
            bookings.delivery_status,
            bookings.date_added,
            bookings.user_fullname,
            bookings.user_email,
            users.number AS user_phone,
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
            bookings.traveller_name,
            bookings.traveller_contact,
            bookings.traveller_email,
            bookings.traveller_current_state,
            bookings.traveller_arrival_state,
            bookings.traveller_destination,
            shipping_records.pickup_address,
            shipping_records.carrier_tracking_id,
            shipping_records.dropoff_address,
            shipping_records.pickup_country,
            shipping_records.courier,
            shipping_records.staff_admin_id,
            shipping_records.staff_name,
            shipping_records.status,
            shipping_records.date_added AS shipping_date_added,
            shipping_records.date_updated AS shipping_date_updated
        ');
        $this->db->from('bookings');
        $this->db->join('users', 'users.id = bookings.user_id', 'left');
        $this->db->join('shipping_records', 'shipping_records.booking_id = bookings.id', 'left');
        $this->db->where('bookings.id', $bookingId);
        $this->applyNotDeleted('bookings');
        $this->applyNotDeleted('users');
        $this->db->where('bookings.payment_status', 'completed');
        return $this->db->get()->row();
    }

    public function search_bookings_for_shipping($query, $limit = 10)
    {
        $query = trim((string) $query);
        if ($query === '') {
            return array();
        }

        $this->db->select('
            bookings.id,
            bookings.tracking_id,
            bookings.user_fullname,
            bookings.user_email,
            users.number AS user_phone,
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
            bookings.traveller_name,
            bookings.traveller_contact,
            bookings.traveller_email,
            bookings.traveller_current_state,
            bookings.traveller_arrival_state,
            bookings.traveller_destination,
            bookings.delivery_status,
            bookings.date_added,
            shipping_records.id AS shipping_record_id
        ');
        $this->db->from('bookings');
        $this->db->join('users', 'users.id = bookings.user_id', 'left');
        $this->db->join('shipping_records', 'shipping_records.booking_id = bookings.id', 'left');
        $this->applyNotDeleted('bookings');
        $this->applyNotDeleted('users');
        $this->db->where('bookings.payment_status', 'completed');
        $this->db->group_start();
        $this->db->like('bookings.tracking_id', $query);
        $this->db->or_like('bookings.user_fullname', $query);
        $this->db->or_like('bookings.user_email', $query);
        $this->db->or_like('users.number', $query);
        $this->db->or_like('bookings.traveller_name', $query);
        $this->db->or_like('bookings.traveller_email', $query);
        $this->db->or_like('bookings.agent_phone', $query);
        $this->db->or_like('bookings.agent_name', $query);
        $this->db->group_end();
        $this->db->order_by('bookings.date_added', 'desc');
        $this->db->limit((int) $limit);
        return $this->db->get()->result();
    }

    public function get_shipping_dashboard_summary()
    {
        return $this->getShippingDashboardSummary();
    }

    public function get_staff_options($currentAdminId, $includeAllEligible = false)
    {
        $hasPermissionColumn = $this->db->field_exists('can_manage_shipping', 'admins');
        $fields = 'id, name, email, role';
        if ($hasPermissionColumn) {
            $fields .= ', can_manage_shipping';
        }

        $this->db->select($fields);
        $this->db->from('admins');
        if (!$includeAllEligible) {
            $this->db->where('id', (int) $currentAdminId);
        } elseif ($hasPermissionColumn) {
            $this->db->group_start();
            $this->db->where('role', 'super_admin');
            $this->db->or_where('can_manage_shipping', 1);
            $this->db->group_end();
        } else {
            $this->db->where_in('role', array('super_admin', 'customer_support'));
        }
        $this->db->order_by('CASE WHEN id = ' . (int) $currentAdminId . ' THEN 0 ELSE 1 END', '', false);
        $this->db->order_by('name', 'asc');
        return $this->db->get()->result();
    }

    private function getShippingDashboardSummary()
    {
        return $this->rememberCache('shipping.summary.dashboard', self::SHIPPING_COUNT_CACHE_TTL, function () {
            $this->db->select("
                COUNT(shipping_records.id) AS created_records,
                SUM(CASE WHEN shipping_records.status = 'Awaiting Collection' THEN 1 ELSE 0 END) AS awaiting_collection_records,
                SUM(CASE WHEN shipping_records.status = 'In Transit' THEN 1 ELSE 0 END) AS in_transit_records,
                SUM(CASE WHEN shipping_records.status = 'Completed' THEN 1 ELSE 0 END) AS completed_records
            ", false);
            $this->db->from('shipping_records');
            $this->db->join('bookings', 'bookings.id = shipping_records.booking_id', 'inner');
            $this->applyNotDeleted('bookings');
            $this->db->where('bookings.payment_status', 'completed');
            $row = $this->db->get()->row();

            return (object) array(
                'created_records' => $row ? (int) $row->created_records : 0,
                'awaiting_collection_records' => $row ? (int) $row->awaiting_collection_records : 0,
                'in_transit_records' => $row ? (int) $row->in_transit_records : 0,
                'completed_records' => $row ? (int) $row->completed_records : 0,
            );
        });
    }
}
