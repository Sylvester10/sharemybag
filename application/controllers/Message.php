<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation =====
Name: Message
Role: Controller
Description: Controls access to Messages pages and functions in admin panel
Models: Message_model
Date Created: 10th May, 2023
*/



class Message extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('message_model');
		$this->load->model('user_read_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}




    private function valid_mail_intent()
    {
        $expected = (string) $this->session->userdata('admin_mail_token');
        $provided = (string) $this->input->post('admin_mail_token', TRUE);
        return $expected !== '' && $provided !== '' && hash_equals($expected, $provided);
    }

    public function send_email()
    {
        if ($this->input->method() !== 'post' || !$this->valid_mail_intent()) {
            $this->session->set_flashdata('status_msg_error', 'Reload the dashboard and submit the email form to send a message.');
            redirect($this->agent->referrer());
            return;
        }
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', 'Subject', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('message', 'Message', 'trim|required|max_length[65536]');
        $sent = $this->form_validation->run() && $this->message_model->send_email();
        $this->session->set_flashdata($sent ? 'status_msg' : 'status_msg_error',
            $sent ? 'Email sent successfully.' : 'Email could not be sent. Please try again later.');
        redirect($this->agent->referrer());
    }

    public function send_bulk_mail()
    {
        if ($this->input->method() !== 'post' || !$this->valid_mail_intent()) {
            $this->session->set_flashdata('status_msg_error', 'Reload the dashboard and submit the email form to queue messages.');
            redirect($this->agent->referrer());
            return;
        }
        $this->form_validation->set_rules('mailing_list', 'Mailing List', 'trim|required|in_list[all_users,approved_users]');
        $this->form_validation->set_rules('subject', 'Subject', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('message', 'Message', 'trim|required|max_length[65536]');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('status_msg_error', first_validation_error('Complete the bulk-email form and try again.'));
        } else {
            $selection = $this->input->post('mailing_list', TRUE);
            $users = $selection === 'all_users' ? $this->user_read_model->users() : $this->user_read_model->get_approved_users();
            $result = $this->message_model->send_bulk_email($users, (int) $this->admin_details->id);
            $message = sprintf('Queued %d emails. %d skipped; %d could not be queued.', $result['queued'], $result['skipped'], $result['errors']);
            $this->session->set_flashdata($result['status'] ? 'status_msg' : 'status_msg_error', $message);
        }
        redirect($this->agent->referrer());
    }

}
