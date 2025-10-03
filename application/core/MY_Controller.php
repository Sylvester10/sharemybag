<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* ===== Documentation ===== 
Name: MY_Controller
Role: Core (super) Controller
Description: MY_Controller Class is the super class that holds global info accessible to the regular controller and model classes. The headers and footers for Site, Admin and Customer are created here. Database, libraries and helpers used by the app are loaded here. This class extends the main CI controller, and at such, every other controller inherits it.
Author: Sylvester Esso Nmakwe
Date Created: 4th January, 2020
*/


require_once FCPATH . 'vendor/autoload.php';


class MY_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->dbutil(); //database utility
		$this->load->library('form_validation');
		$this->load->library('email');
		$this->load->library('session');
		$this->load->library('pagination');
		$this->load->library('user_agent');
		$this->load->library("phpmailer_library", null, 'phpmailer');
		$this->load->library("html_template", null, 'template');
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->helper('captcha');
		$this->load->helper('date');
		$this->load->helper('inflector');
		$this->load->helper('file');
		$this->load->helper('download');
		$this->load->helper('app'); //custom general app helper
		$this->load->helper('email'); //custom email helper
		$this->load->model('common_model'); //general model for controllers
		require_once "application/core/Constants.php"; //require constants
		//require_once "Modules.php"; //require Modules

		//set CSRF
		$this->set_csrf();

		//profiler
		//$this->check_profiler();
	}



	private function set_csrf()
	{
		//get array of controllers to be excluded
		$excluded_controllers = $this->csrf_exclude_controllers();
		//get current controller class and check if it's in the array of controllers to be excluded
		$current_class = $this->router->fetch_class();
		if (!in_array($current_class, $excluded_controllers)) {
			$this->config->set_item('csrf_protection', TRUE); //allow CSRF
		} else {
			$this->config->set_item('csrf_protection', FALSE); //disable CSRF
		}
	}


	private function check_profiler()
	{
		if (ENVIRONMENT != 'production') {
			//allow profiling on development and testing servers only
			$this->output->enable_profiler(TRUE);
		} else {
			$this->output->enable_profiler(FALSE);
		}
	}


	private function csrf_exclude_controllers()
	{
		//get array of controllers to be excluded
		$excluded_controllers = array();
		return $excluded_controllers;
	}	
	
	
	/* ===== Refresh Last Login ===== */
	private function refresh_last_login($user_id, $table)
	{
		//refresh last login every time loggedin user loads or reloads a page
		$this->common_model->update_last_login($user_id, $table);
	}


	/* ===== Website layout===== */
	public function website_header($title, $data = [])
	{
		$data['title'] = $title;
		return $this->load->view('website/layout/header', $data);
	}


	public function website_footer()
	{
		return $this->load->view('website/layout/footer');
	}



	/* =========== Admin Login =========== */
	public function admin_login_header($title)
	{
		$data['title'] = $title;
		return $this->load->view('admin/login/layout/header', $data);
	}


	public function admin_login_footer()
	{
		return $this->load->view('admin/login/layout/footer');
	}



	/* =========== Admin =========== */
	public function admin_header($title, $inner_page_title)
	{
		//$this->load->model('admin_model');
		$admin_details = $this->common_model->get_admin_details($this->session->email);
		$requested_data = array(
			'is_requested',
			'requested_page'
		);
		$this->session->unset_userdata($requested_data);

		$data['title'] = $title;
		$data['inner_page_title'] = $inner_page_title;
		$data['admin_details'] = $admin_details;
		$data['ci'] = $this;
		return $this->load->view('admin/layout/header', $data);
	}


	public function admin_footer()
	{
		return $this->load->view('admin/layout/footer');
	}


	public function admin_restricted()
	{
		//check admin's session
		if ($this->session->admin_loggedin) {
			return TRUE;
		} else { //admin is not logged in or admin's session has expired
			$requested_data = array(
				'is_requested' => TRUE,
				'requested_page' => current_url()
			);
			$this->session->set_userdata($requested_data);
			redirect(site_url('admin_login'));
		}
	}


	public function return_to_dashboard()
	{
		//if admin is still logged in and tries to access login page, redirect to admin dashboard
		if (!$this->session->admin_loggedin) {
			return TRUE;
		} else {
			redirect(site_url('admin'));
		}
	}


	/* =========== User Dashboard Functions =========== */
	public function user_restricted()
	{
		//check user's session
		if ($this->session->user_loggedin) {
			return TRUE;
		} else { //user is not logged in or user's session has expired
			$requested_data = array(
				'is_requested' => TRUE,
				'requested_page' => current_url()
			);
			$this->session->set_userdata($requested_data);
			redirect(site_url('signin'));
		}
	}


	public function return_to_user_dashboard()
	{
		//if user is still logged in and tries to access login page, redirect to user dashboard
		if (!$this->session->user_loggedin) {
			return TRUE;
		} else {
			redirect(site_url('signin'));
		}
	}


	/* =========== Dashboard =========== */
	public function dashboard_header($title)
	{
		$this->load->model('users_model');
		$user_details = $this->common_model->get_user_details($this->session->email);

		$data['title'] = $title;
		//$data['business_name'] = $this->business_name;
		$data['user_details'] = $user_details;
		return $this->load->view('users/layout/header', $data);
	}


	public function dashboard_footer()
	{
		return $this->load->view('users/layout/footer');
	}


	/* ===== Function to check that data exists ===== */
	public function check_data_exists($data, $column, $table, $redirect_url)
	{
		$query = $this->db->get_where($table, array($column => $data))->row();
		return ($query) ? TRUE : redirect(site_url($redirect_url));
	}


	// Validate image of upload 
	public function validate_file_upload($id_photo, $input_name = false, $file_size = (1024 * 5), $file_size_word = '5MB')
	{
		// If files are selected to upload 
		if (!empty($_FILES[$id_photo]['name'])) {

			$error_list = '';
			$_FILES['file']['name'] = $_FILES[$id_photo]['name'];
			$_FILES['file']['type'] = $_FILES[$id_photo]['type'];
			$_FILES['file']['tmp_name'] = $_FILES[$id_photo]['tmp_name'];
			$_FILES['file']['error'] = $_FILES[$id_photo]['error'];
			$_FILES['file']['size'] = $_FILES[$id_photo]['size'];

			// File upload configuration 
			$config['max_size'] = $file_size;
			$current_file = $_FILES['file']['name'];

			($config['max_size'] < ($_FILES['file']['size'] * 0.0009765625)) ? $error_list .= $current_file . 'File exceeding maximum file size(' . $file_size_word . ')<br>' : '';

			if ($error_list != '') {
				return ($input_name) ? '<b>' . $input_name . '</b> <br>' . $error_list : $error_list;
			} else {
				return false;
			}
		} else {
			return null;
		}
	}

	// Function to validate form using CodeIgniter form validation library
	protected function validateForm($rules)
	{
		$this->form_validation->set_rules($rules);
		return $this->form_validation->run();
	}

	// Function to handle file uploads
	// $fileUploadConfig: Associative array with file input names as keys and their upload configurations as values
	protected function handleFileUpload($fileUploadConfig = null)
	{
		if ($fileUploadConfig === null) {
			return null; // No file uploads specified
		}

		$uploadedFiles = array();

		foreach ($fileUploadConfig as $fileInputName => $config) {
			$uploadPath = $config['upload_path'] ?? 'assets/uploads/';
			$allowedTypes = $config['allowed_types'] ?? 'jpg|jpeg|png|svg|pdf';
			$maxSize = $config['max_size'] ?? 1024 * 4;

			// Configuration for the file upload
			$config = array(
				'upload_path'      => $uploadPath,
				'allowed_types'    => $allowedTypes,
				'max_size'         => $maxSize,
				'file_ext_tolower' => TRUE,
				'remove_spaces'    => TRUE,
				'detect_mime'      => TRUE,
			);

			$this->load->library('upload', $config);

			// Perform the file upload
			if (!$this->upload->do_upload($fileInputName)) {
				$uploadedFiles[$fileInputName] = $this->upload->display_errors();
			} else {
				$uploadedFiles[$fileInputName] = $this->upload->data('file_name');
			}
		}

		return $uploadedFiles;
	}

	// Function for file upload
	protected function upload_file($file_input, $upload_path, $allowed_types, $file_size = 1024 * 5)
	{
		$_FILES['file_int']['name'] = $_FILES[$file_input]['name'];
		$_FILES['file_int']['type'] = $_FILES[$file_input]['type'];
		$_FILES['file_int']['tmp_name'] = $_FILES[$file_input]['tmp_name'];
		$_FILES['file_int']['error'] = $_FILES[$file_input]['error'];
		$_FILES['file_int']['size'] = $_FILES[$file_input]['size'];

		// File upload configuration
		$config['upload_path'] = $upload_path; // Path to save the files
		$config['allowed_types'] = $allowed_types; // Allowed extensions
		$config['max_size'] = $file_size; // Filesize cannot exceed 5MB
		$config['file_ext_tolower'] = TRUE; // Force file extension to lower case
		$config['remove_spaces'] = TRUE; // Replace space in file names with underscores to avoid break
		$config['detect_mime'] = TRUE; // Detect type of file to avoid code injection
		$config['encrypt_name'] = TRUE; // Encrypt file name

		// Initialize upload library
		$this->upload->initialize($config);
		if (!$this->upload->do_upload('file_int')) {
			return $this->upload->display_errors();
		}
		return ''; // No error
	}

	protected function get_schema()
	{
		return [
			"@context" => "https://schema.org",
			"@type" => "DeliveryService",
			"name" => business_name,
			"url" => base_url(),
			"logo" => business_logo,
			"contactPoint" => [
				[
					"@type" => "ContactPoint",
					"telephone" => business_phone_number,
					"contactType" => "Customer Service",
					"areaServed" => "NG",
					"availableLanguage" => ["English"]
				],
				[
					"@type" => "ContactPoint",
					"telephone" => business_phone_number2,
					"contactType" => "Customer Service",
					"areaServed" => "NG",
					"availableLanguage" => ["English"]
				]
			],
			"sameAs" => [
				business_facebook,
				business_instagram,
			]
		];
	}

}