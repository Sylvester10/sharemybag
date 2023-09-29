<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to Booking pages and functions in admin panel
Models: Bookings_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Finances extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('bookings_model');
		$this->load->model('shipping_model');
		$this->load->model('travellers_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}



	/* ========== All Finances ========== */
	public function index()
	{
		$inner_page_title = 'All Naira Finances';
		$this->admin_header('Admin', $inner_page_title);
		$data['total_amount'] = $this->common_model->get_all_total_amount();
		$data['total_naira_tax'] = $this->common_model->get_total_naira_tax();
		$data['total_naira_amount'] = $this->common_model->get_total_naira_amount();
		$data['total_naira_selected_items'] = $this->common_model->get_total_naira_selected_price();
		// $data['total_pounds_tax'] = $this->common_model->get_total_pounds_tax();
		// $data['total_pounds_amount'] = $this->common_model->get_total_pounds_amount();
		// $data['total_pounds_selected_items'] = $this->common_model->get_total_pounds_selected_price();
		$this->load->view('admin/finances/all_finances', $data);
		$this->admin_footer();
	}


	public function all_finances_ajax()
	{
		$this->load->model('ajax/finances/finances_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		$rowNumber = 1; // Initialize the row number
		foreach ($list as $y) {
			$sign = $y->currency == 'naira' ? '&#8358;' : '&pound;';

			$payment_status = ($y->status == 'Booking Approved') ? '<span class="text-success"><b>Paid</b></span>' : '<span class="text-danger"><b>Unpaid</b></span>';

			$traveller_commission = (75 / 100) * $y->selected_price;
			$profit = $y->total_amount - $traveller_commission - $y->vat;


			$row = array();
			//$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = $rowNumber++; // Increment and add the row number
			$row[] = $y->traveller_name;
			$row[] = $y->sender_name;
			$row[] = $y->receiver_name;
			$row[] = $sign.number_format($y->total_amount, 2);
			$row[] = $sign.number_format($y->selected_price, 2);
			$row[] = $sign.number_format($y->service_charge, 2);
			$row[] = $sign.number_format($y->vat, 2);
			$row[] = $sign.number_format($y->insurance, 2);
			$row[] = $sign.number_format($profit, 2);
			$row[] = $sign.number_format($traveller_commission, 2);
			$row[] = $payment_status;
			$row[] = x_datetime_full($y->date_added);
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


	/* ========== View Finance ========== */
	public function view_finance($id)
	{
		//check bookings exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$bookings_details = $this->common_model->get_booking_details_by_id($id);
		$page_title = 'Booking Info: ' . $bookings_details->sender_name;
		$this->admin_header($page_title, $page_title);
		$data['y'] = $bookings_details;
		$this->load->view('admin/bookings/view_booking', $data);
		$this->admin_footer();
	}


}