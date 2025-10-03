<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation ===== 
Name: Admin_model
Role: Model
Description: Controls the DB processes of Admin from admin panel
Controller: About
Author: Sylvester Nmakwe
Date Created: 10th May, 2023
*/



class Admin_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}



	/* ===== Dashboard ===== */
	public function send_quick_mail()
	{
		$email = $this->input->post('email', TRUE);
		$subject = ucwords($this->input->post('subject', TRUE));
		$message = nl2br(ucfirst($this->input->post('message', TRUE)));
		return email_user($email, $subject, $message);
	}


	/* ===== Profile ===== */
	public function update_profile()
	{
		$name = ucwords($this->input->post('name', TRUE));
		$phone = $this->input->post('phone', TRUE);
		$current_password = $this->admin_details->password;
		if ($this->input->post('password', TRUE) == '') {
			$password = $current_password; //user does not change password, set password as old password
		} else {
			$password = hash('ripemd128', $this->input->post('password', TRUE));
		}
		$data = array(
			'name' => $name,
			'phone' => $phone,
			'password' => $password
		);
		$this->db->where('email', $this->admin_details->email);
		$this->db->update('admins', $data);
	}


	public function update_profile_photo($profile_photo, $thumbnail)
	{
		$data = array(
			'photo' => $profile_photo,
			'photo_thumb' => $thumbnail,
		);
		$email = $this->session->admin_email;
		$this->db->where('email', $email);
		return $this->db->update('admins', $data);
	}


	public function delete_old_profile_photo()
	{
		$y = $this->admin_details;
		unlink(FCPATH . 'uploads/photos/admins/' . $y->photo); //delete the profile photo
		unlink(FCPATH . 'uploads/photos/admins/' . $y->photo_thumb); //delete the thumbnail
	}


	public function reset_profile_photo()
	{ //remove profile photo
		$this->delete_old_profile_photo(); //delete the photo and thumbnail
		$data = array(
			'photo' => NULL,
			'photo_thumb' => NULL,
		);
		$email = $this->session->admin_email;
		$this->db->where('email', $email);
		return $this->db->update('admins', $data);
	}
}
