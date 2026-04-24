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
		$data['schema'] = $this->get_schema();
		$this->website_header('Share My Bag', $data); // Schema is passed here
		$this->load->view('website/home', $data);     // Pass it again to the main view
		$this->website_footer();
	}

	// Search
	// public function search()
	// {
	// 	$destination = $this->input->post('destination');
	// 	$this->load->model('common_model', 'common');
	// 	$travellers = $this->common_model->get_travellers_by_destination($destination);

	// 	if (count($travellers) > 0) {
	// 		$traveller = $travellers[0];
	// 		$days = get_date_difference(date('Y-m-d H:i:s'), $traveller->travel_date);
	// 		$days = !$days ? 'Today' : ($days > 1 ? "$days Days" : "$days Day");
	// 		$location = ($traveller->destination == 'Nigeria') ? $traveller->location : $traveller->current_state;
	// 		$data = array(
	// 			'travel_date' => x_date($traveller->travel_date),
	// 			'days_remaining' => $days,
	// 			'current_state' => $traveller->current_state,
	// 			'departure_state' => $traveller->departure_state,
	// 			'arrival_airport' => $traveller->arrival_airport,
	// 			'arrival_state' => $traveller->arrival_state,
	// 			'available_space' => $traveller->available_space,
	// 			'id' => $traveller->id,
	// 			'status' => true
	// 		);
	// 		echo json_encode($data);
	// 	} else {
	// 		$data = array(
	// 			'status' => false,
	// 			'msg' => 'No Traveller Available'
	// 		);
	// 		echo json_encode($data);
	// 	}
	// }

	public function search()
	{
		$destination = $this->input->post('destination');
		$this->load->model('common_model', 'common');
		$travellers = $this->common_model->get_travellers_by_destination($destination);

		$selected_traveller = null;

		// Loop through all travellers to find the first valid one
		if (count($travellers) > 0) {
			foreach ($travellers as $t) {
				// Check if space > 0 AND bag is NOT locked (bag_locked != 1)
				if ($t->available_space > 0 && $t->bag_locked != 1) {
					$selected_traveller = $t;
					break; // Stop loop once we find the first match
				}
			}
		}

		// If a valid traveller was found, prepare the data
		if ($selected_traveller) {
			$traveller = $selected_traveller;

			$days = get_date_difference(date('Y-m-d H:i:s'), $traveller->travel_date);
			$days = !$days ? 'Today' : ($days > 1 ? "$days Days" : "$days Day");
			// $location = ($traveller->destination == 'Nigeria') ? $traveller->location : $traveller->current_state;

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
		}
		// If list was empty OR no traveller met the criteria
		else {
			$data = array(
				'status' => false,
				'msg' => 'No Traveller Available'
			);
			echo json_encode($data);
		}
	}


	public function price_estimate()
	{
		$origin = trim((string) $this->input->post('origin', true));
		$destination = trim((string) $this->input->post('destination', true));
		$category = trim((string) $this->input->post('category', true));
		$weight = (float) $this->input->post('weight', true);
		$csrfHash = $this->security->get_csrf_hash();

		if ($origin === '' || $destination === '' || $category === '' || $weight <= 0) {
			echo json_encode([
				'status' => false,
				'msg' => 'Please complete all estimate fields.',
				'csrf_hash' => $csrfHash,
			]);
			return;
		}

		if ($origin === $destination) {
			echo json_encode([
				'status' => false,
				'msg' => 'Origin and destination cannot be the same.',
				'csrf_hash' => $csrfHash,
			]);
			return;
		}

		$routePricing = smb_booking_route_pricing($origin, $destination);
		$categoryConfig = smb_booking_category_config($origin, $destination, $category);

		if (!$routePricing || !$categoryConfig) {
			echo json_encode([
				'status' => false,
				'msg' => 'That route or category is not currently available for pricing.',
				'csrf_hash' => $csrfHash,
			]);
			return;
		}

		$itemPrice = round($weight * (float) $categoryConfig['price'], 2);
		$specialFee = smb_booking_special_fee_from_items([
			(object) ['category' => $category],
		]);
		$serviceCharge = round((float) $routePricing['service_charge'], 2);
		$total = round($itemPrice + $serviceCharge + $specialFee, 2);
		$symbol = $routePricing['symbol'];
		$unit = $categoryConfig['unit'];

		echo json_encode([
			'status' => true,
			'route' => $origin . ' to ' . $destination,
			'category' => $categoryConfig['label'],
			'weight' => number_format($weight, $unit === 'PC' ? 0 : 2) . ' ' . $unit,
			'price_per_unit' => $symbol . number_format((float) $categoryConfig['price'], 2) . ' / ' . $unit,
			'item_price' => $symbol . number_format($itemPrice, 2),
			'service_charge' => $symbol . number_format($serviceCharge, 2),
			'special_fee' => $specialFee > 0 ? ($symbol . number_format($specialFee, 2)) : null,
			'total' => $symbol . number_format($total, 2),
			'disclaimer' => 'Estimate only. Final booking totals may change based on selected insurance and confirmed parcel details.',
			'csrf_hash' => $csrfHash,
		]);
	}


	public function travellers()
	{
		$this->website_header('Travellers');
		$data['captcha_code'] = mt_rand(111111, 999999);
		$this->load->view('website/travellers', $data);
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
			['field' => 'captcha_code', 'label' => 'Captcha Code', 'rules' => 'trim'],
			['field' => 'c_captcha_code', 'label' => 'Captcha Code', 'rules' => 'trim|required|matches[captcha_code]']
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

		// Check for Canada and United Kingdom routes
		$is_canada_uk_route = (strtolower($location) === 'canada' && strtolower($destination) === 'united kingdom') ||
			(strtolower($location) === 'united kingdom' && strtolower($destination) === 'canada');

		if ($is_canada_uk_route) {
			$res = ['status' => false, 'msg' => 'Selected route is currently not available.'];
			echo json_encode($res);
			return;
		}

		// Check for similar routes
		if ($location === $destination) {
			$res = ['status' => false, 'msg' => 'Location and Destination cannot be the same route.'];
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
		// NOTE: Place this file in your CodeIgniter root directory and run it from your browser ONCE.
		// Example: http://your-site.com/infobip_setup.php
		//
		// After you get your 'applicationId' and 'messageId', DELETE THIS FILE.

		// Your API Key
		$apiKey = 'c5cc8e91f2c07ef2d9e8eb2490dbf451-0ae2948d-75de-41cf-af66-e91a6dc73c55';

		// Your Base URL (this one is from your helper file)
		$baseUrl = 'https://m3e629.api.infobip.com';


		// ---------------------------------------------------------------
		// STEP 1: CREATE 2FA APPLICATION
		// ---------------------------------------------------------------
		echo "<h1>Step 1: Creating Application...</h1>";

		$app_payload = json_encode([
			"name" => "ShareMyBag 2FA Application",
			"enabled" => true,
			"configuration" => [
				"pinAttempts" => 10,
				"allowMultiplePinVerifications" => true,
				"pinTimeToLive" => "15m",
				"verifyPinLimit" => "1/3s",
				"sendPinPerApplicationLimit" => "1000/1d",
				"sendPinPerPhoneNumberLimit" => "10/1d"
			]
		]);

		$ch_app = curl_init("$baseUrl/2fa/2/applications");
		curl_setopt_array($ch_app, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				"Authorization: App $apiKey",
				"Content-Type: application/json",
				"Accept: application/json"
			],
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $app_payload,
		]);

		$response_app = curl_exec($ch_app);
		$httpCode_app = curl_getinfo($ch_app, CURLINFO_HTTP_CODE);
		curl_close($ch_app);

		if ($httpCode_app === 200 || $httpCode_app === 201) { // 201 is 'Created'
			$app_result = json_decode($response_app, true);
			$applicationId = $app_result['applicationId'];
			echo "<p><b>Success!</b></p>";
			echo "<p>Your Application ID is: <b>" . htmlspecialchars($applicationId) . "</b></p>";
			echo "<p>Copy this ID into your `infobip_helper.php` file.</p>";
			echo "<pre>" . htmlspecialchars($response_app) . "</pre>";
		} else {
			echo "<p><b>Error creating application!</b></p>";
			echo "<p>HTTP Status: " . $httpCode_app . "</p>";
			echo "<pre>" . htmlspecialchars($response_app) . "</pre>";
			// Stop if app creation failed
			exit;
		}


		// ---------------------------------------------------------------
		// STEP 2: CREATE MESSAGE TEMPLATE (using the Application ID from Step 1)
		// ---------------------------------------------------------------
		echo "<hr><h1>Step 2: Creating Message Template...</h1>";

		$msg_payload = json_encode([
			"pinType" => "NUMERIC",
			"messageText" => "Your ShareMyBag verification pin is {{pin}}",
			"pinLength" => 4,
			"senderId" => "ShareMyBag" // This may require registration with Infobip
		]);

		$ch_msg = curl_init("$baseUrl/2fa/2/applications/$applicationId/messages");
		curl_setopt_array($ch_msg, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				"Authorization: App $apiKey",
				"Content-Type: application/json",
				"Accept: application/json"
			],
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $msg_payload,
		]);

		$response_msg = curl_exec($ch_msg);
		$httpCode_msg = curl_getinfo($ch_msg, CURLINFO_HTTP_CODE);
		curl_close($ch_msg);

		if ($httpCode_msg === 200 || $httpCode_msg === 201) {
			$msg_result = json_decode($response_msg, true);
			$messageId = $msg_result['messageId'];
			echo "<p><b>Success!</b></p>";
			echo "<p>Your Message ID is: <b>" . htmlspecialchars($messageId) . "</b></p>";
			echo "<p>Copy this ID into your `infobip_helper.php` file.</p>";
			echo "<pre>" . htmlspecialchars($response_msg) . "</pre>";
		} else {
			echo "<p><b>Error creating message template!</b></p>";
			echo "<p>HTTP Status: " . $httpCode_msg . "</p>";
			echo "<pre>" . htmlspecialchars($response_msg) . "</pre>";
		}

		echo "<hr><h2>Setup Complete. Now, delete this file!</h2>";
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
