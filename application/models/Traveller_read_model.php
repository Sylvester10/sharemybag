<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

class Traveller_read_model extends \MY_Model
{
    const TRAVELLER_COUNT_CACHE_TTL = 300;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'travellers';
        $this->primary_cols = array('id');
    }

    public function get_traveller_details_by_id($id)
    {
        return $this->dataById($id);
    }

    public function get_traveller_details_by_hash($hash)
    {
        return $this->dataByHash($hash);
    }

    public function get_active_approved_travellers()
    {
        $this->db->where('status', 'Approved');
        $this->db->where('travel_date >=', date('Y-m-d'));
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function count_active_approved_travellers()
    {
        $cache_key = 'travellers.summary.active_approved.' . date('Ymd');

        return $this->rememberCache($cache_key, self::TRAVELLER_COUNT_CACHE_TTL, function () {
            $this->db->where('status', 'Approved');
            $this->db->where('travel_date >=', date('Y-m-d'));
            $this->applyNotDeleted();
            return (int) $this->db->count_all_results($this->table);
        });
    }

    public function clearTravellerCountCaches()
    {
        $this->forgetCache('travellers.summary.status_counts');
        $this->forgetCache('travellers.summary.active_approved.' . date('Ymd'));
    }

    public function getTravellerStatusCountSummary()
    {
        return $this->rememberCache('travellers.summary.status_counts', self::TRAVELLER_COUNT_CACHE_TTL, function () {
            $this->db->select("
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_travellers,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_travellers,
                SUM(CASE WHEN status = 'Unapproved' THEN 1 ELSE 0 END) AS unapproved_travellers
            ", false);
            $this->applyNotDeleted();
            $row = $this->db->get($this->table)->row();

            return (object) array(
                'approved_travellers' => $row ? (int) $row->approved_travellers : 0,
                'pending_travellers' => $row ? (int) $row->pending_travellers : 0,
                'unapproved_travellers' => $row ? (int) $row->unapproved_travellers : 0,
            );
        });
    }

    public function get_travellers_by_destination($destination)
    {
        $this->db->where('destination', $destination);
        $this->db->where('status', 'Approved');
        $this->db->where('available_space >=', 0);
        $this->db->where('travel_date >', date('Y-m-d'));
        $this->applyNotDeleted();
        $this->db->order_by('travel_date', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function get_booking_details_by_traveller_id($travellerId)
    {
        $this->db->order_by('date_added', 'desc');
        $this->db->where('traveller_id', $travellerId);
        $this->db->where('payment_status', 'completed');
        $this->applyNotDeleted('bookings');
        return $this->db->get('bookings')->result();
    }

    public function get_referrer_details($travellerId)
    {
        $this->db->select('referred_by');
        $this->db->from($this->table);
        $this->db->where('id', $travellerId);
        $this->applyNotDeleted();
        $traveller = $this->db->get()->row();

        if (!$traveller || !$traveller->referred_by) {
            return null;
        }

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('username', $traveller->referred_by);
        $this->applyNotDeleted('users');
        return $this->db->get()->row();
    }
}
