<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation ===== 
Name: Message_model
Role: Model
Description: Controls the DB processes of Message from admin panel
Controller: Message
Author: Sylvester Nmakwe
Date Created: 10th January, 2023
*/



class Message_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}



	public function contact_us()
	{
		$name = ucwords($this->input->post('name', TRUE));
		$email = $this->input->post('email', TRUE);
		$phone = ucwords($this->input->post('phone', TRUE));
		$message = ucfirst($this->input->post('message', TRUE));
		$message = nl2br($message);
		$data = array(
			'name' => $name,
			'email' => $email,
			'phone' => $phone,
			'message' => $message,
		);
		$this->db->insert('contact_messages', $data);

		//email admin		
	}


	public function send_email()
	{

		$email = strtolower($this->input->post('email', TRUE));
		$subject = ucfirst($this->input->post('subject', TRUE));
		$data['message'] = nl2br(ucfirst($this->input->post('message', TRUE)));

		// email user
		return send_email_notification($this, $email, $subject, $data, 'admin_general_notification_email');
	}

    public function send_bulk_email($mail_list, $actor_id)
	{
		$subject = ucwords($this->input->post('subject', TRUE));
		$data['message'] = nl2br(ucfirst($this->input->post('message', TRUE)));
		$this->load->model('bulk_mail_model');
		return $this->bulk_mail_model->enqueue_users($mail_list, $subject, $data['message'], (int) $actor_id);
	}



}
