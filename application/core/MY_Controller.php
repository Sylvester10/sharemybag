<?php
defined('BASEPATH') or exit('No direct script access allowed');

/* ===== Documentation ===== 
Name: MY_Controller
Role: Core (super) Controller
Description: MY_Controller Class is the super class that holds global info accessible to the regular controller and model classes. The headers and footers for Site, Admin and Customer are created here. Database, libraries and helpers used by the app are loaded here. This class extends the main CI controller, and at such, every other controller inherits it.
Author: Sylvester Esso Nmakwe
Date Created: 4th January, 2020
*/


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


	/* ===== Website layout===== */
	public function header($title)
	{
		$data['title'] = $title;
		return $this->load->view('layout/header', $data);
	}


	public function header2($title)
	{
		$data['title'] = $title;
		return $this->load->view('layout/header2', $data);
	}


	public function footer()
	{
		return $this->load->view('layout/footer');
	}


	public function footer2()
	{
		return $this->load->view('layout/footer2');
	}



	/* =========== Login =========== */
	public function login_header($title)
	{
		$data['title'] = $title;
		return $this->load->view('login/layout/header', $data);
	}


	public function login_footer()
	{
		return $this->load->view('login/layout/footer');
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
			redirect(site_url('login'));
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
}