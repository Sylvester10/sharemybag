<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Admin
Role: Controller
Description: Controls access to pages who's models are listed below and functions in admin panel
Model: Admin_model, Travellers_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Admin extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('admin_model');
		$this->load->model('travellers_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}



	/* ====== Dashboard ====== */
	public function index()
	{ //admin dashboard, routed as dashboard
		$this->admin_header('Admin', 'Dashboard');
		$data['total_approved_travellers'] = $this->travellers_model->count_approved_travellers();
		$data['total_pending_travellers'] = $this->travellers_model->count_pending_travellers();
		$data['total_unapproved_travellers'] = $this->travellers_model->count_unapproved_travellers();
		$data['total_amount'] = $this->common_model->get_all_total_amount();
		$data['all_users'] = $this->common_model->count_users();
		$data['approved_users'] = $this->common_model->count_approved_users();
		$data['total_bookings'] = count($this->common_model->get_completed_bookings());
		$data['total_users'] = $this->common_model->count_users();
		$this->load->view('admin/dashboard/dashboard', $data);
		$this->admin_footer();
	}


	/* ====== Profile ====== */
	public function profile($error = array('error' => ''))
	{
		$this->admin_header('Profile', 'Profile');
		$data['y'] = $this->admin_details;
		$data['upload_error'] = $error;
		$this->load->view('admin/profile/profile', $data);
		$this->admin_footer();
	}


	public function edit_profile_ajax()
	{
		$this->form_validation->set_rules('name', 'Name', 'trim|required');
		$this->form_validation->set_rules('phone', 'Phone', 'trim|required|is_natural');
		if ($this->input->post('change_password')) { //if change password box is selected, require password fields
			$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
			$this->form_validation->set_rules(
				'c_password',
				'Confirm Password',
				'trim|required|matches[password]',
				array('matches' => 'Passwords do not match')
			);
		} else {
			$this->form_validation->set_rules('password', 'Password', 'trim|min_length[6]');
			$this->form_validation->set_rules(
				'c_password',
				'Confirm Password',
				'trim|matches[password]',
				array('matches' => 'Passwords do not match')
			);
		}

		if ($this->form_validation->run()) {
			$this->admin_model->update_profile();
			echo 1;
		} else {
			echo validation_errors();
		}
	}



	public function update_profile_photo($error = array('error' => ''))
	{
		//config for file uploads
		$config['upload_path']          = 'uploads/photos/admins'; //path to save the files
		$config['allowed_types']        = 'jpg|JPG|png|PNG';  //extensions which are allowed
		$config['max_size']             = 1024; //image size cannot exceed 1MB
		$config['file_ext_tolower']     = TRUE; //force file extension to lower case
		$config['remove_spaces']        = TRUE; //replace space in file names with underscores to avoid break
		$config['detect_mime']          = TRUE; //detect type of file to avoid code injection

		$this->load->library('upload', $config);

		if ($_FILES['profile_photo']['name'] == "") { //file is not selected
			$this->session->set_flashdata('status_msg_error', "No file selected!");
			$this->profile(); //reload page

		} elseif ((!$this->upload->do_upload('profile_photo')) && ($_FILES['profile_photo']['name'] != "")) {
			//upload does not happen when file is selected
			$error = array('error' => $this->upload->display_errors());
			$this->profile($error); //reload page with upload errors

		} else { //file is selected, upload happens, everyone is happy
			//delete the old school logo and favicon
			$this->admin_model->delete_old_profile_photo();

			$profile_photo = $this->upload->data('file_name');
			//generate a 200x200 image for use as thumbnail
			$photo_thumb = generate_image_thumb($profile_photo, '200', '200');
			$this->admin_model->update_profile_photo($profile_photo, $photo_thumb);
			$this->session->set_flashdata('status_msg', "Profile photo updated successfully!");
			redirect($this->agent->referrer());
		}
	}


	public function reset_profile_photo()
	{  //reset photo to app's default
		$this->admin_model->reset_profile_photo();
		$this->session->set_flashdata('status_msg', 'Profile photo removed successfully.');
		redirect($this->agent->referrer());
	}
}
