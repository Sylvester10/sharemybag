<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

class User_read_model extends \MY_Model
{
    const USER_COUNT_CACHE_TTL = 300;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->primary_cols = array('id');
    }

    public function get_user_details($email)
    {
        $this->db->where('email', $email);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->row();
    }

    public function get_user_details_by_id($id)
    {
        return $this->dataById($id);
    }

    public function users()
    {
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function get_users_phone($phone)
    {
        $this->db->where('number', $phone);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->row();
    }

    public function get_approved_users()
    {
        $this->db->where('is_verified', VERIFY_APPROVED);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function get_pending_users()
    {
        $this->db->where('is_verified', VERIFY_PENDING);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function count_approved_users()
    {
        return $this->getUserCountSummary()->approved_users;
    }

    public function count_pending_users()
    {
        return $this->getUserCountSummary()->pending_users;
    }

    public function count_users()
    {
        return $this->getUserCountSummary()->total_users;
    }

    public function clearUserCountCaches()
    {
        $this->forgetCache('users.summary.counts');
    }

    private function getUserCountSummary()
    {
        return $this->rememberCache('users.summary.counts', self::USER_COUNT_CACHE_TTL, function () {
            $this->db->select("
                COUNT(*) AS total_users,
                SUM(CASE WHEN is_verified = " . (int) VERIFY_APPROVED . " THEN 1 ELSE 0 END) AS approved_users,
                SUM(CASE WHEN is_verified = " . (int) VERIFY_PENDING . " THEN 1 ELSE 0 END) AS pending_users
            ", false);
            $this->applyNotDeleted();
            $row = $this->db->get($this->table)->row();

            return (object) array(
                'total_users' => $row ? (int) $row->total_users : 0,
                'approved_users' => $row ? (int) $row->approved_users : 0,
                'pending_users' => $row ? (int) $row->pending_users : 0,
            );
        });
    }
}
