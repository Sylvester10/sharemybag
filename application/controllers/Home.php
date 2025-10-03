<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to messages, travellers and track pages and functions in admin panel
Models: Common_model, Travellers_model, Track_model
Author: Sylvester Esso Nmakwe
Date Created: 11th April, 2023
*/



class Home extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('common_model');
		$this->load->model('travellers_model');
		$this->load->model('bookings_model');
		$this->load->model('adverts_model');
		$this->traveller_details = $this->common_model->get_traveller_details_by_id($this->session->id);
	}


	public function index()
	{
		$this->website_header('Share My Bag');
		$data['schema'] = $this->get_schema();
		$this->load->view('website/home', $data);
		$this->website_footer();
	}

	// Search
	public function search()
	{
		$destination = $this->input->post('destination');
		$this->load->model('common_model', 'common');
		$travellers = $this->common_model->get_travellers_by_destination($destination);

		if (count($travellers) > 0) {
			$traveller = $travellers[0];
			$days = get_date_difference(date('Y-m-d H:i:s'), $traveller->travel_date);
			$days = !$days ? 'Today' : ($days > 1 ? "$days Days" : "$days Day");
			$location = ($traveller->destination == 'Nigeria') ? $traveller->location : $traveller->current_state;
			$data = array(
				'travel_date' => x_date($traveller->travel_date),
				'days_remaining' => $days,
				'current_state' => $traveller->current_state,
				'departure_state' => $traveller->departure_state,
				'arrival_airport' => $traveller->arrival_airport,
				'arrival_state' => $traveller->arrival_state,
				'available_space' => $traveller->available_space,
				'id' => $traveller->id,
				'status' => true
			);
			echo json_encode($data);
		} else {
			$data = array(
				'status' => false,
				'msg' => 'No Traveller Available'
			);
			echo json_encode($data);
		}
	}


	public function travellers()
	{
		$this->website_header('Travellers');
		$this->load->view('website/travellers');
		$this->website_footer();
	}
	

	public function traveller_agreement()
	{
		$this->website_header('Traveller Agreement');
		$this->load->view('website/traveller_agreement');
		$this->website_footer();
	}


	// Traveller Form
	public function add_traveller_ajax()
	{
		// Validation rules
		$rules = [
			['field' => 'fullname', 'label' => 'Full Name', 'rules' => 'trim|required'],
			['field' => 'travel_date', 'label' => 'Travel Date', 'rules' => 'trim|required'],
			['field' => 'email', 'label' => 'Email', 'rules' => 'trim|valid_email|required'],
			['field' => 'c_code1', 'label' => 'Country Code', 'rules' => 'trim|required'],
			['field' => 'phone', 'label' => 'Phone Number', 'rules' => 'trim|required'],
			['field' => 'location', 'label' => 'Location', 'rules' => 'trim|required'],
			['field' => 'destination', 'label' => 'Destination', 'rules' => 'trim|required'],
			['field' => 'available_space', 'label' => 'Bag Space', 'rules' => 'trim|required'],
			// ['field' => 'payment_type', 'label' => 'How you want to be paid', 'rules' => 'trim'],
			// Optional fields without 'required' rule
			['field' => 'c_code2', 'label' => 'Alternate Country Code', 'rules' => 'trim'],
			['field' => 'alt_phone', 'label' => 'Alternate Phone Number', 'rules' => 'trim'],
		];

		// Apply validation rules
		foreach ($rules as $rule) {
			$this->form_validation->set_rules($rule['field'], $rule['label'], $rule['rules'], ['required' => 'Please enter your ' . strtolower($rule['label'])]);
		}

		// File upload configuration
		$config = [
			'upload_path' => 'assets/itinerary',
			'allowed_types' => 'jpg|jpeg|png|pdf',
			'max_size' => 5024,
			'file_ext_tolower' => TRUE,
			'remove_spaces' => TRUE,
			'detect_mime' => TRUE,
		];

		$this->load->library('upload', $config);

		$location = $this->input->post('location');
		$destination = $this->input->post('destination');
		if ($location === $destination) {
			$res = ['status' => false, 'msg' => 'Location and Destination cannot be the same.'];
			echo json_encode($res);
			return;
		}

		if ($this->form_validation->run() == false) {
			$res = ['status' => false, 'msg' => validation_errors()];
			echo json_encode($res);
			return;
		}

		if (empty($_FILES['itinerary_photo']['name'])) {
			$res = ['status' => false, 'msg' => 'Upload Itinerary'];
			echo json_encode($res);
			return;
		}

		// Generate a unique name with an alphanumeric string for the filename
		$file_ext = pathinfo($_FILES['itinerary_photo']['name'], PATHINFO_EXTENSION);
		$new_name = uniqid() . '.' . $file_ext;
		$temp_name = $_FILES['itinerary_photo']['tmp_name'];

		if (!move_uploaded_file($temp_name, $config['upload_path'] . '/' . $new_name)) {
			$res = ['status' => false, 'msg' => 'Failed to upload file.'];
			echo json_encode($res);
			return;
		}

		$itinerary_photo = $new_name;

		// Assuming generate_image_thumb function generates thumbnails for the uploaded image
		$thumbnail = generate_image_thumb($itinerary_photo, '100', '100');

		$this->travellers_model->add_traveller_to_db($itinerary_photo, $thumbnail);

		$res = ['status' => true, 'msg' => 'Thank you! <br> One of our agents will contact you shortly.'];
		echo json_encode($res);
	}


	public function investors()
	{
		$this->website_header('Investors');
		$this->load->view('website/investors');
		$this->website_footer();
	}


	public function prohibited()
	{
		$this->website_header('Prohibited Items');
		$this->load->view('website/prohibited');
		$this->website_footer();
	}


	public function terms_of_use()
	{
		$this->website_header('Terms of Use');
		$this->load->view('website/terms_use');
		$this->website_footer();
	}

	public function terms_conditions()
	{
		$this->website_header('Terms & Conditions');
		$this->load->view('website/terms_conditions');
		$this->website_footer();
	}


	public function policy()
	{
		$this->website_header('Privacy Policy');
		$this->load->view('website/policy');
		$this->website_footer();
	}


	public function cookies()
	{
		$this->website_header('Cookie Policy');
		$this->load->view('website/cookies');
		$this->website_footer();
	}


	public function success()
	{
		$this->load->view('success');
	}


	public function coming_soon()
	{
		$this->load->view('coming_soon');
	}


	public function check()
	{
		$this->load->view('website/check');
		// var_dump(production_url('assets/general/logo/colored_logo.png'));
	}


	public function referrer_ok($username)
	{ //Check referrer

		if ($username == "") {
			return true;
		} else {
			$numRow = $this->db->get_where('users', array('username' => $username))->num_rows();

			if ($numRow > 0) {
				return true;
			} else {
				return false;
			}
		}
	}
}
