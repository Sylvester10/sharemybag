<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to Messages pages and functions in admin panel
Models: Messages_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Message extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('message_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}




	public function send_email()
	{
		$this->form_validation->set_rules('email', 'Email', 'trim|required');
		$this->form_validation->set_rules('subject', 'Subject', 'trim|required');
		$this->form_validation->set_rules('message', 'Message', 'trim|required');
		if ($this->form_validation->run()) {
			$this->message_model->send_email();
			$this->session->set_flashdata('status_msg', "Email sent successfully.");
		} else {
			$this->session->set_flashdata('status_msg_error', 'Error sending email to user.');
		}
		redirect($this->agent->referrer());
	}


// 	public function send_bulk_mail()
// 	{
// 		// Set validation rules
// 		$this->form_validation->set_rules('mailing_list', 'Mailing List', 'trim|required');
// 		$this->form_validation->set_rules('subject', 'Subject', 'trim|required');
// 		$this->form_validation->set_rules('message', 'Message', 'trim|required');

// 		if ($this->form_validation->run() == FALSE) {
// 			echo validation_errors();
// 		} else {
// 			$mailing_list = $this->input->post('mailing_list', TRUE);
// 			switch ($mailing_list) {
// 				case 'all_users':
// 					$mail_list = $this->common_model->users();
// 					$this->message_model->send_bulk_email($mail_list);
// 					$this->session->set_flashdata('status_msg', "Emails sent successfully.");
// 					redirect($this->agent->referrer());
// 					break;
// 				case 'approved_users':
// 					$mail_list = $this->common_model->get_approved_users();
// 					$this->message_model->send_bulk_email($mail_list);
// 					$this->session->set_flashdata('status_msg', "Emails sent successfully.");
// 					redirect($this->agent->referrer());
// 					break;
// 			}
// 		}
// 	}

    public function send_bulk_mail()
	{
		// Set validation rules
		$this->form_validation->set_rules('mailing_list', 'Mailing List', 'trim|required');
		$this->form_validation->set_rules('subject', 'Subject', 'trim|required');
		$this->form_validation->set_rules('message', 'Message', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			echo validation_errors();
		} else {
			$mailing_list = $this->input->post('mailing_list', TRUE);
			switch ($mailing_list) {
				case 'all_users':
					$mail_list = $this->common_model->users();
					$this->message_model->send_bulk_email($mail_list);
					$this->session->set_flashdata('status_msg', "Emails sent successfully.");
					redirect($this->agent->referrer());
					break;
				case 'approved_users':
					$mail_list = $this->common_model->get_approved_users();
					$this->message_model->send_bulk_email($mail_list);
					$this->session->set_flashdata('status_msg', "Emails sent successfully.");
					redirect($this->agent->referrer());
					break;
			}
		}
	}



}