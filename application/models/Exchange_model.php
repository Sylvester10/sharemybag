<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation =====
Name: Exchange_model
Role: Model
Description: Controls the DB processes of Exchange_model from admin panel
Controller: Admin_exchange
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Exchange_model extends CI_Model
{
	public $admin_details;
	public $table;

	public function __construct()
	{
		parent::__construct();
		$this->table = 'exchange_rates'; // Correct table name for consistency
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
		$this->load->model('finance_read_model');
	}



	public function add_exchange_rate()
	{

		$currency = currency_code_normalize($this->input->post('currency', TRUE));
		$rate = $this->input->post('rate', TRUE);

		$data = array(
			'currency' => $currency,
			'rate' => $rate
		);

		$this->db->insert('exchange_rates', $data); // Using the correct table name
		$this->finance_read_model->clearExchangeRateCaches();

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
