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
		$this->load->model('waitlist_model');
		$this->load->model('bookings_model');
		$this->load->model('adverts_model');
		$this->traveller_details = $this->common_model->get_traveller_details_by_id($this->session->id);
	}


	public function index()
	{
		$this->header2('Home');
		$this->load->view('home');
		$this->footer();
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
				'current_state' => $location,
				'departure_state' => $traveller->departure_state,
				'available_space' => $traveller->available_space,
				'id' => $traveller->id,
				'status' => true
			);
			echo json_encode($data);
		} else {
			$data = array(
				'status' => false,
				'msg' => 'No Traveler Available'
			);
			echo json_encode($data);
		}
	}


	public function booking($id)
	{
		$this->header('Buy Space');
		$traveller = $this->common_model->get_traveller_details_by_id($id);
		$data['traveller_details'] = $traveller;
		$data['currency'] = trim(strtolower($traveller->location)) == 'nigeria' ? 'pounds' : 'naira';
		$data['symbol'] = $data['currency'] == 'naira' ? '&#8358;' : '&pound;';
		$data['one_naira'] = $this->common_model->one_naira();
		$data['one_pound'] = $this->common_model->one_pound();
		$this->load->view('booking', $data);
		$this->footer();
	}


	/* ========== Add Booking ========== */
	public function add_booking_ajax()
	{

		$errorUploadType = $statusMsg = '';

		//validate file size
		$validate_image = $this->validate_file_upload('id_photo', 'Image Upload ERROR');
		$validate_selfie = $this->validate_file_upload('selfie', 'Selfie Image - ERROR');

		$error_list = (($validate_image) ? $validate_image : '') . (($validate_selfie) ? $validate_selfie : '');
		if ($validate_image or $validate_selfie) {
			//upload does not happen when file is selected
			$res = ['status' => false, 'msg' => $error_list];
			echo json_encode($res); //show validation errors
			return;
		} else {

			//traveller details validation
			$this->form_validation->set_rules('traveller_id', 'Traveller ID', 'trim');
			$this->form_validation->set_rules('traveller_name', 'Traveller Name', 'trim');
			$this->form_validation->set_rules('traveller_email', 'Traveller Email', 'trim');
			$this->form_validation->set_rules('available_space', 'Available Space', 'trim');
			$this->form_validation->set_rules('traveller_departure_date', 'Traveller departure date', 'trim');
			$this->form_validation->set_rules('traveller_arrival_date', 'Traveller arrival date', 'trim');
			$this->form_validation->set_rules('traveller_contact', 'Traveller contact', 'trim');
			$this->form_validation->set_rules('traveller_drop_address1', '1st drop address', 'trim');
			$this->form_validation->set_rules('traveller_drop_date1', '1st drop date', 'trim');
			$this->form_validation->set_rules('traveller_drop_address2', '2nd drop address', 'trim');
			$this->form_validation->set_rules('traveller_drop_date2', '2nd drop date', 'trim');
			$this->form_validation->set_rules('traveller_departure_state', 'Traveller departure', 'trim');


			//form validation
			$this->form_validation->set_rules('insurance', 'Insurance', 'trim');
			$this->form_validation->set_rules(
				'sender_name',
				'Sender Name',
				'trim|required',
				array('required' => 'Sender name field is empty')
			);
			$this->form_validation->set_rules(
				'sender_name',
				'Sender Name',
				'trim|required',
				array('required' => 'Sender name field is empty')
			);
			$this->form_validation->set_rules(
				'sender_address',
				'Sender Address',
				'trim|required',
				array('required' => 'Sender address field is empty')
			);
			$this->form_validation->set_rules('sender_phone', 'Mobile', 'trim|is_natural|required', array('required' => 'Sender phone field is empty'));
			$this->form_validation->set_rules(
				'sender_email',
				'Email',
				'trim|required|valid_email',
				array('valid_email' => 'Invalid Email. @xyz.com')
			);
			$this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required', array('required' => 'Bank Name field is empty'));
			$this->form_validation->set_rules(
				'bank_acc',
				'Bank Account',
				'trim|is_natural|required',
				array(
					'required' => 'Bank Account field is empty',
					'is_natural' => 'Numbers only for Bank account'
				)
			);
			$this->form_validation->set_rules(
				'sort_code',
				'Sort Code',
				'trim|is_natural|required',
				array(
					'required' => 'Sort Code field is empty',
					'is_natural' => 'Numbers only for Sort Code'
				)
			);
			$this->form_validation->set_rules(
				'receiver_name',
				'Receiver Name',
				'trim|required',
				array('required' => 'Receiver address field is empty')
			);
			$this->form_validation->set_rules('receiver_address', 'Receiver Address', 'trim|required', array('required' => 'Receiver address field is empty'));
			$this->form_validation->set_rules('payment_acc', 'Payment Option', 'trim|required', array('required' => 'Payment Option is empty'));
			$this->form_validation->set_rules('receiver_phone', 'Receiver Mobile', 'trim|is_natural|required', array('required' => 'Receiver phone field is empty'));
			$this->form_validation->set_rules(
				'receiver_email',
				'Receiver Email',
				'trim|required|valid_email',
				array('valid_email' => 'Invalid Email. @xyz.com')
			);
			$this->form_validation->set_rules(
				'id_type',
				'ID Type',
				'trim|required',
				array('required' => 'ID Type not selected')
			);


			if ($this->form_validation->run()) {
				//loading upload library
				$this->load->library('upload');

				$upload_error = "";

				// If id_photo file is selected to upload
				if (!empty($_FILES['id_photo']['name'])) {
					$_FILES['file']['name'] = $_FILES['id_photo']['name'];
					$_FILES['file']['type'] = $_FILES['id_photo']['type'];
					$_FILES['file']['tmp_name'] = $_FILES['id_photo']['tmp_name'];
					$_FILES['file']['error'] = $_FILES['id_photo']['error'];
					$_FILES['file']['size'] = $_FILES['id_photo']['size'];

					// File upload configuration 
					$config['upload_path'] = './assets/uploads'; //path to save the files
					$config['allowed_types'] = 'jpg|JPG|jpeg|JPEG|png|PNG'; //extensions which are allowed
					$config['max_size'] = 1024 * 5; //filesize cannot exceed 5MB
					$config['file_ext_tolower'] = TRUE; //force file extension to lower case
					$config['remove_spaces'] = TRUE; //replace space in file names with underscores to avoid break
					$config['detect_mime'] = TRUE; //detect type of file to avoid code injection
					$config['encrypt_name'] = TRUE; //encrypt file name

					//Creating new configuration for upload
					$this->upload->initialize($config);
					if ($this->upload->do_upload('file')) {
						$upload_data = $this->upload->data();
						$id_photo = $upload_data['file_name'];
					} else {
						// Handle the upload error
						$upload_error .= $this->upload->display_errors();
					}
				}

				// If selfie file is selected to upload
				if (!empty($_FILES['selfie']['name'])) {
					$_FILES['file']['name'] = $_FILES['selfie']['name'];
					$_FILES['file']['type'] = $_FILES['selfie']['type'];
					$_FILES['file']['tmp_name'] = $_FILES['selfie']['tmp_name'];
					$_FILES['file']['error'] = $_FILES['selfie']['error'];
					$_FILES['file']['size'] = $_FILES['selfie']['size'];

					// File upload configuration 
					$config['upload_path'] = './assets/selfie'; //path to save the files
					$config['allowed_types'] = 'jpg|JPG|jpeg|JPEG|png|PNG'; //extensions which are allowed
					$config['max_size'] = 1024 * 5; //filesize cannot exceed 5MB
					$config['file_ext_tolower'] = TRUE; //force file extension to lower case
					$config['remove_spaces'] = TRUE; //replace space in file names with underscores to avoid break
					$config['detect_mime'] = TRUE; //detect type of file to avoid code injection
					$config['encrypt_name'] = TRUE; //encrypt file name

					//Creating new configuration for upload
					$this->upload->initialize($config);
					if ($this->upload->do_upload('file')) {
						$upload_data = $this->upload->data();
						$selfie = $upload_data['file_name'];
					} else {
						// Handle the upload error
						$upload_error .= $this->upload->display_errors();
					}
				}


				//displaying error for id_photo file, if any
				if (!empty($upload_error)) {

					$res = ['status' => false, 'msg' => $upload_error . 'Image File Error: ' . $id_photo . ')'];
					echo json_encode($res);
					return;
				}

				//displaying error for selfie file, if any
				if (!empty($upload_error)) {

					$res = ['status' => false, 'msg' => $upload_error . 'Image File Error: ' . $selfie . ')'];
					echo json_encode($res);
					return;
				}

				if ($this->bookings_model->add_booking_to_db($id_photo, $selfie)) {

					$res = ['status' => true];
					echo json_encode($res);
					return;
				} else {

					$res = ['status' => false, 'msg' => 'Booking could not be completed, try again later.'];
					echo json_encode($res);
					return;
				}
			} else {
				$res = ['status' => false, 'msg' => validation_errors()];
				echo json_encode($res); //show validation errors
				return;
			}
		}
	}


	public function join_waitlist()
	{
		$this->header('Join Wait List');
		$this->load->view('join_waitlist');
		$this->footer();
	}


	public function waitlist_ajax()
	{
		$this->form_validation->set_rules(
			'name',
			'Name',
			'trim|required',
			array('required' => 'Please enter your full name')
		);
		$this->form_validation->set_rules(
			'email',
			'Email',
			'trim|valid_email|required',
			array('required' => 'Please enter your email')
		);
		$this->form_validation->set_rules(
			'c_code',
			'Country Code',
			'trim|required',
			array('required' => 'Please select a country code')
		);
		$this->form_validation->set_rules(
			'phone',
			'Phone Number',
			'trim|required',
			array('required' => 'Please enter a phone number')
		);


		if ($this->form_validation->run()) {
			$this->waitlist_model->add_waitlist_to_db(); //insert the data into db
			echo 1;
		} else {
			echo validation_errors();
		}
	}


	public function traveller()
	{
		$this->header('Traveller');
		$this->load->view('traveller');
		$this->footer();
	}


	// Traveller Form
	public function add_traveller_ajax()
	{
		$this->form_validation->set_rules(
			'name',
			'Name',
			'trim|required',
			array('required' => 'Please enter your full name')
		);
		$this->form_validation->set_rules(
			'travel_date',
			'Travel Date',
			'trim|required',
			array('required' => 'Please select your travel date')
		);
		$this->form_validation->set_rules(
			'email',
			'Email',
			'trim|valid_email|required',
			array('required' => 'Please enter your email')
		);
		$this->form_validation->set_rules(
			'c_code1',
			'Country Code',
			'trim|required',
			array('required' => 'Please select a country code')
		);
		$this->form_validation->set_rules(
			'phone',
			'Phone Number',
			'trim|required',
			array('required' => 'Please enter a phone number')
		);
		$this->form_validation->set_rules(
			'c_code2',
			'Country Code',
			'trim'
		);
		$this->form_validation->set_rules(
			'alt_phone',
			'Alternate Phone Number',
			'trim'
		);
		$this->form_validation->set_rules('location', 'Location', 'trim|required');
		$this->form_validation->set_rules('destination', 'Destination', 'trim|required');


		if ($this->form_validation->run()) {
			$this->travellers_model->add_traveller_to_db(); //insert the data into db
			echo 1;
		} else {
			echo validation_errors();
		}
	}


	public function track()
	{
		$this->header2('Track Your Parcel');
		$data['adverts'] = $this->adverts_model->get_recent_published_adverts(10);
		$this->load->view('track', $data);
		$this->footer();
	}


	// Track parcel
	public function track_parcel()
	{
		$tracking_id = $this->input->post('parcel');
		$this->load->model('common_model', 'common');
		$shippings = $this->common_model->get_shipping_by_tracking_id($tracking_id);

		if (!empty($shippings)) {
			$data = array(
				'status' => true,
				'data' => array()
			);

			foreach ($shippings as $shipping) {
				$data['data'][] = array(
					'tracking_id' => $shipping->tracking_id,
					'icon_text' => $shipping->icon_text,
					'heading' => $shipping->heading,
					'description' => $shipping->body,
					// Changed 'body' to 'description'
					'date_added' => x_datetime_full($shipping->date_added)
				);
			}

			echo json_encode($data);
		} else {
			$data = array(
				'status' => false,
				'msg' => 'No Shipping Info, please check again later.'
			);
			echo json_encode($data);
		}
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
		$this->load->view('mail/admin_booking_notify_email');
	}
}
