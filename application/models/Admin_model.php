<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation =====
Name: Admin_model
Role: Model
Description: Controls the DB processes of Admin from admin panel
Controller: Admin
Author: Sylvester Nmakwe
Date Created: 10th May, 2023
*/



class Admin_model extends \CI_Model
{
	public $admin_details;

	public function __construct()
	{
		parent::__construct();
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
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
			$password = password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT);
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

	public function get_all_admins()
	{
		$this->db->order_by('date_added', 'DESC');
		return $this->db->get('admins')->result();
	}


	public function get_admin_by_id($id)
	{
		return $this->db->where(['id' => $id])->get('admins')->row();
	}


	public function add_admin()
	{
		$password = password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT);

		$data = [
			'name'     => ucwords(trim($this->input->post('name',  TRUE))),
			'email'    => strtolower(trim($this->input->post('email', TRUE))),
			'phone'    => trim($this->input->post('phone', TRUE)),
			'role'     => $this->input->post('role',  TRUE),
			'password' => $password,
		];

		$this->db->insert('admins', $data);
		return;
	}


	public function update_admin($id)
	{
		$data = [
			'name'  => ucwords(trim($this->input->post('name',  TRUE))),
			'email' => strtolower(trim($this->input->post('email', TRUE))),
			'phone' => trim($this->input->post('phone', TRUE)),
			'role'  => $this->input->post('role', TRUE),
		];

		// Only update password if a new one was supplied
		$new_password = trim($this->input->post('password', TRUE));
		if ($new_password !== '') {
			$data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
		}

		$this->db->where('id', $id);
		return $this->db->update('admins', $data);
	}


	public function delete_admin($id)
	{
		$this->db->where('id', $id);
		return $this->db->delete('admins');
	}
}
