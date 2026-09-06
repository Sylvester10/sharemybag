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
	private const OFFLINE_BACKFILL_BROWSER_KEY = 'smb-backfill-20260429';

	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('admin_model');
		$this->load->model('travellers_model');
		$this->load->model('finance_read_model');
		$this->load->model('user_read_model');
		$this->load->model('users_model');
		$this->load->model('booking_read_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}



	/* ====== Dashboard ====== */
	public function index()
	{ //admin dashboard, routed as dashboard
		$this->admin_header('Admin', 'Dashboard');
		$data['total_approved_travellers'] = $this->travellers_model->count_approved_travellers();
		$data['total_pending_travellers'] = $this->travellers_model->count_pending_travellers();
		$data['total_unapproved_travellers'] = $this->travellers_model->count_unapproved_travellers();
		$data['total_amount'] = $this->finance_read_model->get_all_total_amount();
		$data['all_users'] = $this->user_read_model->count_users();
		$data['approved_users'] = $this->user_read_model->count_approved_users();
		$data['total_bookings'] = $this->booking_read_model->count_completed_bookings();
		$data['total_users'] = $this->user_read_model->count_users();
		$this->load->view('admin/dashboard/dashboard', $data);
		$this->admin_footer();
	}

	public function backfill_offline_bookings()
	{
		$this->admin_role_restricted(['super_admin']);

		$key = (string) $this->input->get('key', true);
		if ($key !== self::OFFLINE_BACKFILL_BROWSER_KEY) {
			show_404();
		}

		$updated = $this->users_model->backfill_offline_booking_financials();

		$this->output
			->set_content_type('text/plain')
			->set_output("Offline booking backfill completed. Updated {$updated} booking(s).");
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
		$this->form_validation->set_rules('country_code', 'Country code', 'trim|required');
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


	/* ====== All Admins ====== */
	public function admins()
	{
		$this->admin_role_restricted(['super_admin']);
		$this->admin_header('Admins', 'Manage Admin Accounts');
		$data['admins'] = $this->admin_model->get_all_admins();
		$this->load->view('admin/admins/all_admins', $data);
		$this->admin_footer();
	}


	/* ====== Add Admin — show form ====== */
	public function add()
	{
		$this->admin_role_restricted(['super_admin']);
		$this->admin_header('Admins', 'Add New Admin');
		$this->load->view('admin/admins/add_admin');
		$this->admin_footer();
	}


	/* ====== Add Admin — process ====== */
	public function add_ajax()
	{
		$this->admin_role_restricted(['super_admin']);
		$this->form_validation->set_rules('name',  'Name',  'trim|required');
		$this->form_validation->set_rules(
			'email',
			'Email',
			'trim|required|valid_email|is_unique[admins.email]',
			['is_unique' => 'An admin with this email already exists.']
		);
		$this->form_validation->set_rules('country_code', 'Country code', 'trim|required');
		$this->form_validation->set_rules('phone',    'Phone',    'trim|required');
		$this->form_validation->set_rules('role',     'Role',     'trim|required|in_list[super_admin,customer_support,traveller_support]');
		$this->form_validation->set_rules('can_manage_shipping', 'Shipping access', 'trim|required|in_list[0,1]');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
		$this->form_validation->set_rules(
			'c_password',
			'Confirm Password',
			'trim|required|matches[password]',
			['matches' => 'Passwords do not match.']
		);

		if ($this->form_validation->run()) {
			$this->admin_model->add_admin();
			$this->session->set_flashdata('status_msg', 'Admin account created successfully.');
			redirect(site_url('all_admins'));
		} else {
			$this->session->set_flashdata('status_msg_error', validation_errors());
			redirect(site_url('add-admin'));
		}
	}


	/* ====== Edit Admin — show form ====== */
	public function edit($id)
	{
		$this->admin_role_restricted(['super_admin']);
		$this->check_data_exists($id, 'id', 'admins', 'edit-admin');
		$this->admin_header('Admins', 'Edit Admin');
		$data['y'] = $this->admin_model->get_admin_by_id($id);
		$this->load->view('admin/admins/edit_admin', $data);
		$this->admin_footer();
	}


	/* ====== Edit Admin — process ====== */
	public function edit_ajax($id)
	{
		$this->admin_role_restricted(['super_admin']);
		$this->check_data_exists($id, 'id', 'admins', 'edit-admin');

		// 1. Fetch the existing admin record from the database
		// (Adjust the method name below to match your actual model method)
		$current_admin = $this->admin_model->get_admin_by_id($id);

		$this->form_validation->set_rules('name',  'Name',  'trim|required');

		// 2. Conditionally set the email rule
		$email_rule = 'trim|required|valid_email';
		$submitted_email = $this->input->post('email');

		if ($submitted_email !== $current_admin->email) {
			// Only check for uniqueness if they are actually changing the email
			$email_rule .= '|is_unique[admins.email]';
		}

		$this->form_validation->set_rules(
			'email',
			'Email',
			$email_rule,
			['is_unique' => 'Another admin account is already using this email address.']
		);

		$this->form_validation->set_rules('country_code', 'Country code', 'trim|required');
		$this->form_validation->set_rules('phone', 'Phone', 'trim|required');
		$this->form_validation->set_rules('role',  'Role',  'trim|required|in_list[super_admin,customer_support,traveller_support]');
		$this->form_validation->set_rules('can_manage_shipping', 'Shipping access', 'trim|required|in_list[0,1]');

		// Password only validated if the field is filled in
		if ($this->input->post('password')) {
			$this->form_validation->set_rules('password',   'Password',         'trim|min_length[6]');
			$this->form_validation->set_rules(
				'c_password',
				'Confirm Password',
				'trim|matches[password]',
				['matches' => 'Passwords do not match.']
			);
		}

		if ($this->form_validation->run()) {
			$this->admin_model->update_admin($id);
			$this->session->set_flashdata('status_msg', 'Admin account updated successfully.');
			redirect(site_url('all_admins'));
		} else {
			$this->session->set_flashdata('status_msg_error', validation_errors());
			redirect(site_url('edit-admin/' . $id));
		}
	}


	/* ====== Delete Admin ====== */
	public function delete($id)
	{
		$this->admin_role_restricted(['super_admin']);
		$this->check_data_exists($id, 'id', 'admins', 'all_admins');

		// Cannot delete yourself
		$target = $this->admin_model->get_admin_by_id($id);
		if ($target->email === $this->session->admin_email) {
			$this->session->set_flashdata('status_msg_error', 'You cannot delete your own account.');
			redirect(site_url('all_admins'));
			return;
		}

		$this->admin_model->delete_admin($id);
		$this->session->set_flashdata('status_msg', 'Admin account deleted.');
		redirect(site_url('all_admins'));
	}
}
