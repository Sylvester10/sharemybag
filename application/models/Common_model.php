<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation =====
Name: Common_model
Role: Model
Description: Shared legacy read/write helpers still used across admin and user bootstrap flows
Controller: MY_Controller, Admin, Admin_login, Message, Shipping
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Common_model extends \CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	/* ===== Last Login ===== */
	public function update_last_login($user_id, $table = 'users')
	{ //update last login
		$data = array(
			'last_login' => date('Y-m-d H:i:s'), //current timestamp
		);
		$this->db->where('id', $user_id);
		return $this->db->update($table, $data);
	}


	public function get_last_login_stats($period, $period_type, $table)
	{ //get last login
		$period_type = strtoupper($period_type);
		$where = "last_login IS NOT NULL AND
					last_login > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL {$period} {$period_type})";
		$this->db->where($where);
		$query = $this->db->get($table)->num_rows();
		return $query;
	}


	/* =================== Admins ====================== */
	public function get_admin_details($email)
	{ //get admin info by email
		$this->db->where('email', $email);
		return $this->db->get('admins')->row();
	}


	public function get_admin_details_by_id($id)
	{ //get admin info	id
		$this->db->where('id', $id);
		return $this->db->get('admins')->row();
	}


	public function get_admins()
	{ //get all admins
		return $this->db->get_where('admins')->result();
	}
}
