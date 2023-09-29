<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to Travellers pages and functions in admin panel
Models: Traveller_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Travellers extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('travellers_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}



	/* ========== Approved travellers ========== */
	public function index()
	{
		$inner_page_title = 'Approved Travellers (' . count($this->common_model->get_approved_travellers()) . ')';
		$this->admin_header('Admin', $inner_page_title);
		$this->load->view('admin/travellers/approved_travellers');
		$this->admin_footer();
	}


	public function approved_travellers_ajax()
	{
		$this->load->model('ajax/travellers/approved_travellers_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		foreach ($list as $y) {
			$status = '<span class="text-success"><b> ' . $y->status . ' </b></span>';
			$available_space = "$y->available_space KG";
			$arrival_date = ($y->arrival_date == '') ? 'No Information' : x_date($y->arrival_date);
			$row = array();
			$row[] = checkbox_bulk_action($y->id);
			$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = $y->name;
			$row[] = $y->phone;
			$row[] = $y->alt_phone;
			$row[] = $y->email;
			$row[] = $y->location;
			$row[] = $y->destination;
			$row[] = $y->address;
			$row[] = $y->airline;
			$row[] = x_date($y->travel_date);
			$row[] = $arrival_date;
			$row[] = $available_space;
			$row[] = $status;
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


	/* ========== Pending travellers ========== */
	public function pending()
	{
		$inner_page_title = 'Pending Travellers (' . count($this->common_model->get_pending_travellers()) . ')';
		$this->admin_header('Admin', $inner_page_title);
		$this->load->view('admin/travellers/pending_travellers');
		$this->admin_footer();
	}


	public function pending_travellers_ajax()
	{
		$this->load->model('ajax/travellers/pending_travellers_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		foreach ($list as $y) {
			$status = '<span class="text-primary"><b> ' . $y->status . '</b> </span>';
			$row = array();
			$row[] = checkbox_bulk_action($y->id);
			$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = $y->name;
			$row[] = $y->phone;
			$row[] = $y->alt_phone;
			$row[] = $y->email;
			$row[] = $y->location;
			$row[] = $y->destination;
			$row[] = x_date($y->travel_date);
			$row[] = $status;
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


	/* ========== Unavailable travellers ========== */
	public function unavailable()
	{
		$inner_page_title = 'Unavailable Travelers (' . count($this->common_model->get_unavailable_travellers()) . ')';
		$this->admin_header('Admin', $inner_page_title);
		$this->load->view('admin/travellers/unavailable_travellers');
		$this->admin_footer();
	}


	public function unavailable_travellers_ajax()
	{
		$this->load->model('ajax/travellers/unavailable_travellers_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		foreach ($list as $y) {
			$status = '<span class="text-danger"><b> ' . $y->status . ' </b></span>';
			$bag_space = "$y->bag_space KG";
			$row = array();
			$row[] = checkbox_bulk_action($y->id);
			$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = $y->name;
			$row[] = $y->phone;
			$row[] = $y->email;
			$row[] = $y->destination;
			$row[] = $y->address;
			$row[] = $y->airline;
			$row[] = x_date($y->travel_date);
			$row[] = $bag_space;
			$row[] = $status;
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


	public function unapproved()
	{
		$inner_page_title = 'Unapproved Travellers (' . count($this->common_model->get_unapproved_travellers()) . ')';
		$this->admin_header('Admin', $inner_page_title);
		$this->load->view('admin/travellers/unapproved_travellers');
		$this->admin_footer();
	}


	public function unapproved_travellers_ajax()
	{
		$this->load->model('ajax/travellers/unapproved_travellers_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		foreach ($list as $y) {
			$status = '<span class="text-danger"><b> ' . $y->status . ' </b></span>';
			$bag_space = "$y->bag_space KG";
			$row = array();
			$row[] = checkbox_bulk_action($y->id);
			$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = $y->name;
			$row[] = $y->phone;
			$row[] = $y->email;
			$row[] = $y->location;
			$row[] = $y->destination;
			$row[] = x_date($y->travel_date);
			$row[] = $status;
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


	/* ========== Add Traveller ========== */
	public function add_traveller($error = array('error' => ''))
	{
		$this->admin_header('Admin', 'Add Traveler');
		$this->load->view('admin/travellers/add_traveller');
		$this->admin_footer();
	}


	public function add_traveller_ajax($error = array('error' => ''))
	{
		// validation rules
		$this->form_validation->set_rules('name', 'Name', 'trim|min_length[2]|max_length[500]|required');
		$this->form_validation->set_rules('phone', 'Phone Number', 'trim|required');
		$this->form_validation->set_rules('alt_phone', ' Alternate Phone Number', 'trim|required');
		$this->form_validation->set_rules(
			'email',
			'Email',
			'trim|required|valid_email',
			array('valid_email' => 'Enter a valid email.')
		);
		$this->form_validation->set_rules('location', 'Current Location', 'trim|required');
		$this->form_validation->set_rules('current_state', 'State', 'trim');
		$this->form_validation->set_rules('drop_address1', 'Drop off Address', 'trim');
		$this->form_validation->set_rules('drop_date1', 'Drop off Date', 'trim');
		$this->form_validation->set_rules('departure_state', 'State of Departure', 'trim');
		$this->form_validation->set_rules('drop_address2', '2nd Drop off Address', 'trim');
		$this->form_validation->set_rules('drop_date2', '2nd Drop off Date', 'trim');
		$this->form_validation->set_rules('destination', 'Destination', 'trim|required');
		//$this->form_validation->set_rules('destination_address', 'Address on Arrival', 'trim|required');
		$this->form_validation->set_rules('travel_date', 'Travel Date', 'trim|required');
		$this->form_validation->set_rules('arrival_date', 'Arrival Date', 'trim');
		$this->form_validation->set_rules('airline', 'Airline', 'required');
		$this->form_validation->set_rules('address', 'Address', 'trim|min_length[2]|max_length[500]');
		$this->form_validation->set_rules('available_space', 'Available Space', 'trim|required');
		$this->form_validation->set_rules('unwanted_items[]', 'Unwanted Items', 'required');

		if ($this->form_validation->run()) {
			$this->travellers_model->add_traveller_to_db();
			$this->session->set_flashdata('status_msg', "Traveler data added successfully.");
			redirect('travellers');
		} else {
			echo validation_errors();
		}

	}


	/* ========== Update Travelers ========== */
	public function update_traveller($id, $error = array('error' => ''))
	{
		//check travellers exists
		$this->check_data_exists($id, 'id', 'travellers', 'admin');
		$travellers_details = $this->common_model->get_traveller_details_by_id($id);
		$page_title = 'Update Traveler: ' . $travellers_details->name;
		$this->admin_header($page_title, $page_title);
		$data['y'] = $travellers_details;
		$data['upload_error'] = $error;
		$this->load->view('admin/travellers/update_traveller', $data);
		$this->admin_footer();
	}


	public function update_traveller_ajax($id, $error = array('error' => ''))
	{
		//check travellers exists
		$this->check_data_exists($id, 'id', 'travellers', 'admin');
		// validation rules
		$this->form_validation->set_rules('name', 'Name', 'trim|min_length[2]|max_length[500]|required');
		$this->form_validation->set_rules('phone', 'Mobile', 'trim|required');
		$this->form_validation->set_rules(
			'email',
			'Email',
			'trim|required|valid_email',
			array('valid_email' => 'Enter a valid email.')
		);
		$this->form_validation->set_rules('travel_date', 'Travel Date', 'trim|required');
		$this->form_validation->set_rules('arrival_date', 'Arrival Date', 'trim');
		$this->form_validation->set_rules('location', 'Current Location', 'trim|required');
		$this->form_validation->set_rules('current_state', 'State', 'trim');
		$this->form_validation->set_rules('destination', 'Destination', 'trim|required');
		//$this->form_validation->set_rules('destination_address', 'Address on Arrival', 'trim|required');
		$this->form_validation->set_rules('airline', 'Airline', 'required');
		$this->form_validation->set_rules('address', 'Address', 'trim|min_length[2]|max_length[500]');
		$this->form_validation->set_rules('available_space', 'Available Space', 'trim|required');
		$this->form_validation->set_rules('unwanted_items[]', 'Unwanted Items', 'required');

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('status_msg_error', validation_errors());
			redirect('travellers/update_traveller/' .$id);
		}

		if ($this->travellers_model->update_traveller($id)) {
			$this->session->set_flashdata('status_msg', "Traveller info updated successfully.");
			redirect('travellers');
			return;
		}
		$this->session->set_flashdata('status_msg_error', 'Traveller Data could not be updated');
		redirect('travellers/update_traveller/' . $id);


	}


	/* ========== travellers Profile ========== */
	public function traveller_profile($id)
	{
		//check travellers exists
		$this->check_data_exists($id, 'id', 'travellers', 'admin');
		$travellers_details = $this->common_model->get_traveller_details_by_id($id);
		$page_title = 'Traveller Profile: ' . $travellers_details->name;
		$this->admin_header($page_title, $page_title);
		$data['y'] = $travellers_details;
		$data['booking_details'] = $this->common_model->get_booking_details_by_traveller_id($id);
		//$data['b'] = $booking_details;
		$this->load->view('admin/travellers/traveller_profile', $data);
		$this->admin_footer();
	}


	public function message_travellers($id)
	{
		//check admin exists
		$this->check_data_exists($id, 'id', 'travellers', 'admin');
		$this->form_validation->set_rules('message', 'Message', 'trim|required');
		$y = $this->common_model->get_traveller_details_by_id($id);
		if ($this->form_validation->run()) {
			$this->travellers_model->message_travellers($id);
			$this->session->set_flashdata('status_msg', "Message successfully sent to {$y->name}.");
		} else {
			$this->session->set_flashdata('status_msg_error', 'Error sending message to travellers.');
		}
		redirect($this->agent->referrer());
	}


	public function approve_traveller($id)
	{
		$this->travellers_model->approve_traveller($id);
		$this->session->set_flashdata('status_msg', 'Traveller Approved.');
		redirect($this->agent->referrer());
	}


	public function unapprove_traveller($id)
	{
		$this->travellers_model->unapprove_traveller($id);
		$this->session->set_flashdata('status_msg', 'Traveller Unapproved.');
		redirect($this->agent->referrer());
	}


	public function delete_traveller($id)
	{
		//check admin exists
		$this->check_data_exists($id, 'id', 'travellers', 'admin');
		$this->travellers_model->delete_traveller($id);
		$this->session->set_flashdata('status_msg', 'Traveller Deleted.');
		redirect($this->agent->referrer());
	}


	public function bulk_actions_traveller()
	{
		$this->form_validation->set_rules('check_bulk_action', 'Bulk Select', 'trim');
		$selected_rows = count($this->input->post('check_bulk_action', TRUE));
		if ($this->form_validation->run()) {
			if ($selected_rows > 0) {
				$this->travellers_model->bulk_actions_traveller();
			} else {
				$this->session->set_flashdata('status_msg_error', 'No item selected.');
			}
		} else {
			$this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
		}
		redirect($this->agent->referrer());
	}





}