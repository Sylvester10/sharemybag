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


	public function contact_messages()
	{
		$total_messages = $this->message_model->count_messages();
		$inner_page_title = 'Contact Messages (' . $total_messages . ')';
		$this->admin_header('Contact Messages', $inner_page_title);
		//config for pagination
		$config = array();
		$per_page = 5; //number of items to be displayed per page
		$uri_segment = 3; //pagination segment id
		$config["base_url"] = base_url('message/contact_messages');
		$config["total_rows"] = $total_messages;
		$config["per_page"] = $per_page;
		$config["uri_segment"] = $uri_segment;
		$config['cur_tag_open'] = '<a class="pagination-active-page" href="#!">'; //disable click event of current link
		$config['cur_tag_close'] = '</a>';
		$config['first_link'] = 'First';
		$config['next_link'] = '&raquo;'; // >>
		$config['prev_link'] = '&laquo;'; // <<
		$config['last_link'] = 'Last';
		$config['display_pages'] = TRUE; //show pagination link digits
		$config['num_links'] = 3; //number of digit links
		$this->pagination->initialize($config);
		$page = $this->uri->segment($uri_segment) ? $this->uri->segment($uri_segment) : 0;
		$data["messages"] = $this->message_model->get_messages($per_page, $page);
		$str_links = $this->pagination->create_links();
		$data["links"] = explode('&nbsp;', $str_links); //explode the links 1 2 3 4 into distinct items for styling.
		$data['total_records'] = $total_messages;
		$data["latest_message"] = $this->message_model->get_latest_message();
		$this->load->view('admin/messages/contact_messages', $data);
		$this->admin_footer();
	}


	public function single_message($msg_id)
	{
		//check message exists
		$this->check_data_exists($msg_id, 'id', 'contact_messages', 'admin');
		$total_messages = $this->message_model->count_messages();
		$inner_page_title = 'Contact Messages (' . $total_messages . ')';
		$this->admin_header('Contact Messages', $inner_page_title);
		//config for pagination
		$config = array();
		$per_page = 5; //number of items to be displayed per page
		$uri_segment = 4; //pagination segment id
		$config["base_url"] = base_url('message/single_message/' . $msg_id);
		$config["total_rows"] = $total_messages;
		$config["per_page"] = $per_page;
		$config["uri_segment"] = $uri_segment;
		$config['cur_tag_open'] = '<a class="pagination-active-page" href="#!">'; //disable click event of current link
		$config['cur_tag_close'] = '</a>';
		$config['first_link'] = 'First';
		$config['next_link'] = '&raquo;'; // >>
		$config['prev_link'] = '&laquo;'; // <<
		$config['last_link'] = 'Last';
		$config['display_pages'] = TRUE; //show pagination link digits
		$config['num_links'] = 3; //number of digit links
		$this->pagination->initialize($config);
		$page = $this->uri->segment($uri_segment) ? $this->uri->segment($uri_segment) : 0;
		$data["messages"] = $this->message_model->get_messages($per_page, $page);
		$str_links = $this->pagination->create_links();
		$data["links"] = explode('&nbsp;', $str_links); //explode the links 1 2 3 4 into distinct items for styling.
		$data['total_records'] = $total_messages;
		$data["message"] = $this->message_model->get_message_details($msg_id);
		$this->load->view('admin/messages/single_message', $data);
		$this->admin_footer();
	}


	public function reply_message($msg_id)
	{
		//check message exists
		$this->check_data_exists($msg_id, 'id', 'contact_messages', 'admin');
		$this->form_validation->set_rules('message', 'Message', 'trim|required');
		$sender = $this->message_model->get_message_details($msg_id)->name;
		if ($this->form_validation->run()) {
			$this->message_model->reply_message($sender);
			$this->session->set_flashdata('status_msg', "Reply successfully sent to {$sender}.");
		} else {
			$this->session->set_flashdata('status_msg_error', 'Error replying message.');
		}
		redirect($this->agent->referrer());
	}


	public function delete_message($msg_id)
	{
		$this->check_data_exists($msg_id, 'id', 'contact_messages', 'admin');
		$this->message_model->delete_message($msg_id);
		$this->session->set_flashdata('status_msg', 'Message deleted successfully.');
		redirect('message/contact_messages');
	}


	public function bulk_actions_messages()
	{
		$this->form_validation->set_rules('check_bulk_action', 'Bulk Select', 'trim');
		$selected_rows = count($this->input->post('check_bulk_action', TRUE));
		if ($this->form_validation->run()) {
			if ($selected_rows > 0) {
				$this->message_model->bulk_actions_messages();
			} else {
				$this->session->set_flashdata('status_msg_error', 'No item selected.');
			}
		} else {
			$this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
		}
		redirect('message/contact_messages');
	}



}