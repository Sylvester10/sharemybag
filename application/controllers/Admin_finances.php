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



class Admin_finances extends MY_Controller
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
		$inner_page_title = 'All Finances';
		$this->admin_header('Admin', $inner_page_title);
		$data['total_amount'] = $this->common_model->get_all_total_amount();
		$data['total_pounds_tax'] = $this->common_model->get_total_pounds_tax();
		$data['total_pounds_amount'] = $this->common_model->get_total_pounds_amount();
		$data['total_pounds_selected_items'] = $this->common_model->get_total_pounds_selected_price();
		$data['get_total_selected_space'] = $this->common_model->get_total_selected_space();

		// $data['get_total_selected_space_ng'] = $this->common_model->get_sum_for_nigeria();
		// $data['get_total_selected_space_uk'] = $this->common_model->get_sum_for_uk();
		$this->load->view('admin/finances/all_finances', $data);
		$this->admin_footer();
	}


	public function all_finances_ajax()
	{
		$this->load->model('ajax/finances/finances_ajax', 'current_model');
		$month = $this->input->post('month');
		$year = $this->input->post('year');

		$list = $this->current_model->get_records($month, $year);

		$data = array();
		$rowNumber = 1;
		foreach ($list as $y) {
			$sign = '&pound;';
			$payment_status = ($y->payment_status == 'completed') ? '<span class="text-success"><b>Paid</b></span>' : '<span class="text-danger"><b>Canceled</b></span>';
			$traveller = $this->common_model->get_traveller_details_by_id($y->traveller_id);

			$traveller_commission = $traveller->destination == 'Nigeria'
				? 4.50 * $y->selected_space
				: 5 * $y->selected_space;

			$commission = $y->payment_status == 'completed'
				? $sign . number_format($traveller_commission, 2)
				: 'N/A';

			$profit = $y->total_amount - $traveller_commission - $y->vat;

			$row = array();
			$row[] = $rowNumber++;
			$row[] = x_date_month_time_full($y->date_added);
			$row[] = $y->traveller_name;
			$row[] = $y->agent_name;
			$row[] = $y->receiver_name;
			$row[] = $sign . number_format($y->total_amount, 2);
			$row[] = $sign . number_format($y->selected_price, 2);
			$row[] = $sign . number_format($y->service_charge, 2);
			$row[] = $sign . number_format($y->vat, 2);
			$row[] = $sign . number_format($y->insurance, 2);
			$row[] = $sign . number_format($profit, 2);
			$row[] = $commission;
			$row[] = $y->payment_method;
			$row[] = $payment_status;
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->current_model->count_all_records($month, $year),
			"recordsFiltered" => $this->current_model->count_filtered_records($month, $year),
			"data" => $data,
		);

		echo json_encode($output);
	}


	/* ========== View Finance ========== */
	public function view_finance($id)
	{
		//check bookings exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$bookings_details = $this->common_model->get_booking_details_by_id($id);
		$page_title = 'Booking Info: ' . $bookings_details->agent_name;
		$this->admin_header($page_title, $page_title);
		$data['y'] = $bookings_details;
		$this->load->view('admin/bookings/view_booking', $data);
		$this->admin_footer();
	}
}
