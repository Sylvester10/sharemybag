<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to Exchange pages and functions in admin panel
Models: Exchange_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Admin_exchange extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('exchange_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}



	/* ========== All Bookings ========== */
	public function index()
	{
		$inner_page_title = 'All Exchange Rates';
		$this->admin_header('Admin', $inner_page_title);
		$this->load->view('admin/exchange/all_exchange_rates');
		$this->admin_footer();
	}


	public function all_exchange_rates()
	{
		$this->load->model('ajax/exchange/exchange_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		foreach ($list as $y) {

            $rate = '₦'.$y->rate.' to £1';

			$row = array();
			$row[] = $rate;
			$row[] = x_date($y->date_added);
			$data[] = $row;
		}
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->current_model->count_all_records(),
			"recordsFiltered" => $this->current_model->count_filtered_records(),
			"data" => $data,
		);
		//output to json format
		echo json_encode($output);
	}


	public function add_exchange_rate()
	{
		$this->form_validation->set_rules('rate', 'Exchange Rate', 'trim|required');

		if ($this->form_validation->run()) {
			$this->exchange_model->add_exchange_rate();
			$this->session->set_flashdata('status_msg', "Exchange Rate added successfully.");
			redirect($this->agent->referrer());
		} else {
			echo validation_errors();
		}
	}




}