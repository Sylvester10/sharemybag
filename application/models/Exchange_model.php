<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation ===== 
Name: Booking_model
Role: Model
Description: Controls the DB processes of Exchange_model from admin panel
Controller: Exchange, Home
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Exchange_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->table = 'exchange_rates'; // Correct table name for consistency
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}



	public function add_exchange_rate()
	{

		$currency = $this->input->post('currency', TRUE);
		$rate = $this->input->post('rate', TRUE);

		$data = array(
			'currency' => $currency,
			'rate' => $rate
		);

		$this->db->insert('exchange_rates', $data); // Using the correct table name

		return;
	}


	public function get_exchange_rates($limit, $offset)
	{
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("date_added", "DESC"); //order by date_added DESC so that the latest date appears first
		$query = $this->db->get_where('exchange_rates'); // Using the correct table name
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
}
