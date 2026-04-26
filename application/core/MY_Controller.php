<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* ===== Documentation =====
Name: MY_Controller
Role: Core (super) Controller
Description: MY_Controller Class is the super class that holds global info accessible to the regular controller and model classes. The headers and footers for Site, Admin and Customer are created here. Database, libraries and helpers used by the app are loaded here. This class extends the main CI controller, and at such, every other controller inherits it.
Author: Sylvester Esso Nmakwe
Date Created: 4th January, 2020

UPDATED: Added employee role-based access control methods.
  - admin_role_restricted($allowed_roles): Redirects if admin's role is not in the allowed list.
  - get_admin_role(): Returns the current logged-in admin's role from the DB.
Roles: 'super_admin', 'customer_support', 'traveller_support'

CSRF POLICY:
  - CSRF protection is configured globally in application/config/config.php.
  - This controller does not toggle CSRF at runtime because CI3 validates
    incoming requests before controller construction.
*/


require_once FCPATH . 'vendor/autoload.php';

/**
 * @property CI_DB_query_builder $db
 * @property CI_DB_utility $dbutil
 * @property CI_Form_validation $form_validation
 * @property CI_Email $email
 * @property CI_Session $session
 * @property CI_Pagination $pagination
 * @property CI_User_agent $agent
 * @property CI_Upload $upload
 * @property CI_URI $uri
 * @property CI_Router $router
 * @property CI_Output $output
 * @property CI_Security $security
 * @property Common_model $common_model
 * @property Phpmailer_library $phpmailer
 * @property Html_template $template
 * @property object $admin_details
 * @property object $user_details
 * @property object $traveller_details
 * @property object $traveller_read_model
 * @property object $booking_presenter
 * @property object $current_model
 */

class MY_Controller extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->dbutil();
		$this->load->library(['form_validation', 'email', 'session', 'pagination', 'user_agent', 'upload']);
		$this->load->library("phpmailer_library", null, 'phpmailer');
		$this->load->library("html_template", null, 'template');
		$this->load->helper(['form', 'url', 'captcha', 'date', 'inflector', 'file', 'download', 'app', 'email', 'my', 'sk']);
		$this->load->model('common_model');
		require_once "application/core/Constants.php";
	}


	protected function check_profiler()
	{
		$this->output->enable_profiler(ENVIRONMENT != 'production');
	}

	/* ===== Refresh Last Login ===== */
	protected function refresh_last_login($user_id, $table)
	{
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


	/* =========== Admin =========== */
	public function admin_login_header($title)
	{
		$data['title'] = $title;
		return $this->load->view('admin/login/layout/header', $data);
	}


	public function admin_login_footer()
	{
		return $this->load->view('admin/login/layout/footer');
	}


	public function admin_header($title, $inner_page_title)
	{
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


	/**
	 * EMPLOYEE ROLE RESTRICTION
	 *
	 * Call this AFTER admin_restricted() in any controller method that
	 * should be limited to specific roles.
	 *
	 * Usage example in a controller:
	 *   $this->admin_restricted();
	 *   $this->admin_role_restricted(['super_admin']); // finances page
	 *   $this->admin_role_restricted(['super_admin', 'customer_support']); // bookings
	 *
	 * @param array $allowed_roles  Roles that ARE allowed. Others get blocked.
	 */
	public function admin_role_restricted(array $allowed_roles)
	{
		$role = $this->get_admin_role();
		if (!in_array($role, $allowed_roles)) {
			$this->session->set_flashdata('status_msg_error', 'You do not have permission to access that page.');
			redirect(site_url('admin'));
		}
	}


	/**
	 * Returns the current logged-in admin's role.
	 * Falls back to 'super_admin' if the column doesn't exist yet (safe during migration).
	 *
	 * @return string
	 */
	public function get_admin_role()
	{
		$admin = $this->common_model->get_admin_details($this->session->admin_email ?? $this->session->email);
		return $admin->role ?? 'super_admin';
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


	/* =========== User =========== */
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


	public function dashboard_header($title)
	{
		$this->load->model('user_read_model');
		$user_details = $this->user_read_model->get_user_details($this->session->email);

		$data['title'] = $title;
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
		$query = $this->db->where(array($column => $data))->get($table)->row();
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
	protected function handleFileUpload($fileUploadConfig = null)
	{
		if ($fileUploadConfig === null) {
			return null;
		}

		$uploadedFiles = array();

		foreach ($fileUploadConfig as $fileInputName => $config) {
			$uploadPath = $config['upload_path'] ?? 'assets/uploads/';
			$allowedTypes = $config['allowed_types'] ?? 'jpg|jpeg|png|svg|pdf';
			$maxSize = $config['max_size'] ?? 1024 * 4;

			$config = array(
				'upload_path'      => $uploadPath,
				'allowed_types'    => $allowedTypes,
				'max_size'         => $maxSize,
				'file_ext_tolower' => TRUE,
				'remove_spaces'    => TRUE,
				'detect_mime'      => TRUE,
			);

			$this->load->library('upload', $config);

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

		$config['upload_path'] = $upload_path;
		$config['allowed_types'] = $allowed_types;
		$config['max_size'] = $file_size;
		$config['file_ext_tolower'] = TRUE;
		$config['remove_spaces'] = TRUE;
		$config['detect_mime'] = TRUE;
		$config['encrypt_name'] = TRUE;

		$this->upload->initialize($config);
		if (!$this->upload->do_upload('file_int')) {
			return $this->upload->display_errors();
		}
		return '';
	}


	// Schema
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
