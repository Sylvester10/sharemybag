<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

class Booking_read_model extends \MY_Model
{
    const BOOKING_COUNT_CACHE_TTL = 300;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'bookings';
        $this->primary_cols = array('id');
    }

    public function get_booking_details_by_id($id)
    {
        return $this->dataById($id);
    }

    public function get_bookings_by_user_id($userId, $activeOnly = false)
    {
        $this->db->order_by('date_added', 'desc');
        $this->db->where('user_id', $userId);
        $this->applyNotDeleted();
        if ($activeOnly) { $this->applyActiveBookings(); }
        return $this->db->get($this->table)->result();
    }

    public function count_active_bookings_by_user_id($userId)
    {
        $this->db->where('user_id', (int) $userId);
        $this->applyNotDeleted();
        $this->applyActiveBookings();
        return (int) $this->db->count_all_results($this->table);
    }

    private function applyActiveBookings()
    {
        // Match legacy payment aliases; completed payment is not completed delivery.
        $this->db->where("LOWER(TRIM(COALESCE(payment_status, ''))) IN ('completed', 'complete', 'success', 'paid')", null, false);
        $this->db->where("LOWER(TRIM(COALESCE(delivery_status, ''))) NOT IN ('completed', 'delivered', 'cancelled', 'canceled')", null, false);
        $this->db->where("LOWER(TRIM(COALESCE(status, ''))) NOT IN ('declined', 'booking declined', 'cancelled', 'canceled')", null, false);
        if ($this->db->field_exists('cancelled_at', $this->table)) {
            $this->db->where('cancelled_at IS NULL', null, false);
        }
    }

    public function count_bookings_by_user_id($userId)
    {
        $this->db->where('user_id', $userId);
        $this->applyNotDeleted();
        return $this->db->count_all_results($this->table);
    }

    public function get_all_bookings()
    {
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function count_all_bookings()
    {
        return $this->getBookingCountSummary()->all_bookings;
    }

    public function get_completed_bookings()
    {
        $this->db->where('payment_status', 'completed');
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function count_completed_bookings()
    {
        return $this->getBookingCountSummary()->completed_bookings;
    }

    public function count_new_completed_bookings()
    {
        $this->db->where('payment_status', 'completed');
        $this->db->where('new', 0);
        $this->applyNotDeleted();
        return (int) $this->db->count_all_results($this->table);
    }

    public function get_canceled_bookings()
    {
        $this->db->where('payment_status', 'canceled');
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function count_canceled_bookings()
    {
        return $this->getBookingCountSummary()->canceled_bookings;
    }

    public function clearBookingCountCaches()
    {
        $this->forgetCache('bookings.summary.counts');
    }

    private function getBookingCountSummary()
    {
        return $this->rememberCache('bookings.summary.counts', self::BOOKING_COUNT_CACHE_TTL, function () {
            $this->db->select("
                COUNT(*) AS all_bookings,
                SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) AS completed_bookings,
                SUM(CASE WHEN payment_status = 'canceled' THEN 1 ELSE 0 END) AS canceled_bookings
            ", false);
            $this->applyNotDeleted();
            $row = $this->db->get($this->table)->row();

            return (object) array(
                'all_bookings' => $row ? (int) $row->all_bookings : 0,
                'completed_bookings' => $row ? (int) $row->completed_bookings : 0,
                'canceled_bookings' => $row ? (int) $row->canceled_bookings : 0,
            );
        });
    }
}
