<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation ===== 
Name: Booking_model
Role: Model
Description: Controls the DB processes of Common_model from admin and user panel
Controller: Bookings, Home, Admin, Adverts, Login, Messages, Shipping, Travellers
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Common_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}



	/* ===== Last Login ===== */
	public function update_last_login($user_id)
	{//update last login
		$data = array(
			'last_login' => date('Y-m-d H:i:s'), //current timestamp	 
		);
		$this->db->where('id', $user_id);
		return $this->db->update('users', $data);
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
		return $this->db->get_where('admins', array('email' => $email))->row();
	}


	public function get_admin_details_by_id($id)
	{ //get admin info	id
		return $this->db->get_where('admins', array('id' => $id))->row();
	}


	public function get_admins()
	{ //get all admins
		return $this->db->get_where('admins')->result();
	}


	/* =================== Travellers ====================== */
	public function get_traveller_details($email)
	{ //get traveller details by email
		return $this->db->get_where('travellers', array('email' => $email))->row();
	}


	public function get_traveller_details_by_id($id)
	{ //get traveller details by id
		return $this->db->get_where('travellers', array('id' => $id))->row();
	}


	public function get_traveller()
	{ //get all travellers
		return $this->db->get_where('travellers')->result();
	}


	public function get_approved_travellers()
	{ //get all approved travellers
		return $this->db->get_where('travellers', array('status' => 'Approved'))->result();
	}

	public function get_pending_travellers()
	{ //get all active traveller
		return $this->db->get_where('travellers', array('status' => 'Pending'))->result();
	}


	public function get_unapproved_travellers()
	{ //get all unapproved travellers
		return $this->db->get_where('travellers', array('status' => 'Unapproved'))->result();
	}


	public function get_traveller_bookings($id)
	{ //get all traveller booking
		return $this->db->get_where('booking', array('traveller_id' => $id))->result();
	}


	public function get_travellers_by_destination($destination)
	{
		// Get the current date in the format y-m-d
		$currentDate = date('Y-m-d');

		$this->db->where('destination', $destination);
		$this->db->where('status', 'Approved');
		$this->db->where('available_space >', 0);
		$this->db->where('travel_date >=', $currentDate);

		$this->db->order_by('travel_date', 'ASC');
		return $this->db->get('travellers')->result();
	}


	/* =================== Waitlist ====================== */
	public function get_waitlist_details_by_id($id)
	{ //get waitlist details by id
		return $this->db->get_where('waitlist', array('id' => $id))->row();
	}


	public function get_waitlist()
	{ //get all waitlist
		return $this->db->get_where('waitlist')->result();
	}


	/* =================== Bookings ====================== */
	public function get_traveller_booking($email)
	{ //get booking info by email
		return $this->db->get_where('bookings', array('email' => $email))->row();
	}


	public function get_booking_details_by_id($id)
	{ //get booking info by id
		return $this->db->get_where('bookings', array('id' => $id))->row();
	}


	public function get_booking_details_by_traveller_id($id)
	{ //get booking info by traveller id
		return $this->db->get_where('bookings', array('traveller_id' => $id))->result();
	}


	public function get_most_recent_booking()
	{
		return $this->db->get_where('bookings')->result()[0];
	}


	public function get_all_bookings()
	{ //get all booking
		return $this->db->get_where('bookings')->result();
	}


	/* =================== Shipping ====================== */
	public function get_shipping_by_tracking_id($tracking_id)
	{ // get shipping by tracking id
		$this->db->where('tracking_id', $tracking_id);
		$this->db->order_by('date_added', 'desc');
		return $this->db->get('shipping')->result();
	}


	public function get_shipping_by_tracking_id_row($tracking_id)
	{ //get shipping info by tracking id
		return $this->db->get_where('shipping', array('tracking_id' => $tracking_id))->row();
	}


	public function get_shipping_details_by_tracking_id($tracking_id)
	{ // Get all shipping info by tracking id
		return $this->db->get_where('shipping', array('tracking_id' => $tracking_id))->result();
	}



	/* =================== Exchange rates ====================== */
	public function get_all_exchange_rates()
	{ //get all exchange rates
		return $this->db->get_where('exchange_rates')->result();
	}

	public function get_most_recent_exchange_rate()
	{
		$this->db->order_by('date_added', 'DESC');
		return $this->db->get_where('exchange_rates')->result()[0];
	}

	public function one_pound() {
		return floatval($this->get_most_recent_exchange_rate()->rate);
	}

	public function one_naira()
	{
		return 1 / floatval($this->get_most_recent_exchange_rate()->rate);
	}



	/* =================== Exchange rates ====================== */
	public function get_all_total_amount()
	{//get sum of total_amount column
		$this->db->select_sum('total_amount');
		$this->db->where('status', 'Booking Approved');
		return $this->db->get('bookings')->row()->total_amount;
	}

	public function get_total_naira_tax()
	{//get sum of get_total_naira_tax
		$this->db->select_sum('vat');
		$this->db->where('currency', 'naira');
		$this->db->where('status', 'Booking Approved');
		return $this->db->get('bookings')->row()->vat;
	}

	public function get_total_pounds_tax()
	{//get sum of get_total_naira_tax
		$this->db->select_sum('vat');
		$this->db->where('currency', 'pounds');
		$this->db->where('status', 'Booking Approved');
		return $this->db->get('bookings')->row()->vat;
	}

	public function get_total_naira_amount()
	{ //get sum of total_naira_amount
		$this->db->select_sum('total_amount');
		$this->db->where('currency', 'naira');
		$this->db->where('status', 'Booking Approved');
		return $this->db->get('bookings')->row()->total_amount;
	}

	public function get_total_naira_selected_price()
	{ //get sum of total_naira_selected_price
		$this->db->select_sum('selected_price');
		$this->db->where('currency', 'naira');
		$this->db->where('status', 'Booking Approved');
		$query = $this->db->get('bookings');
		return $query->row()->selected_price;
	}
	
	public function get_total_pounds_amount()
	{ //get sum of total_pounds_amount
		$this->db->select_sum('total_amount');
		$this->db->where('currency', 'pounds');
		$this->db->where('status', 'Booking Approved');
		return $this->db->get('bookings')->row()->total_amount;
	}

	public function get_total_pounds_selected_price()
	{ //get sum of total_pounds_selected_price
		$this->db->select_sum('selected_price');
		$this->db->where('currency', 'pounds');
		$this->db->where('status', 'Booking Approved');
		$query = $this->db->get('bookings');
		return $query->row()->selected_price;
	}



}