<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to Adverts pages and functions in admin panel
Models: Advert_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Admin_adverts extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('adverts_model');
		$this->admin_detail = $this->common_model->get_admin_details($this->session->admin_email);
	}


	public function index()
	{
		$total_adverts = $this->adverts_model->count_adverts();
		$inner_page_title = 'Adverts (' . $total_adverts . ')';
		$this->admin_header('Admin', $inner_page_title);
		//config for pagination
		$config = array();
		$per_page = 12; //number of items to be displayed per page
		$uri_segment = 2; //pagination segment id: adverts/pagination_id
		$config["base_url"] = base_url('admin_adverts');
		$config["total_rows"] = $total_adverts;
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
		$data["adverts"] = $this->adverts_model->get_adverts($config["per_page"], $page);
		$data["total_records"] = $config["total_rows"];
		$str_links = $this->pagination->create_links();
		$data["links"] = explode('&nbsp;', $str_links); //explode the links 1 2 3 4 into distinct items for styling.
		$data['total_records'] = $this->adverts_model->count_adverts();
		$data['total_published'] = $this->adverts_model->count_published_adverts();
		$data['total_unpublished'] = $this->adverts_model->count_unpublished_adverts();
		$this->load->view('admin/adverts/all_adverts', $data);
		$this->admin_footer();
	}


	/* ========== Admin Actions: Adverts ============= */
	public function upload_advert_ajax()
	{
		if (!empty($_FILES)) {

			// Config for file uploads
			$config['upload_path'] = 'assets/adverts'; // Path to save the files
			$config['allowed_types'] = 'jpg|JPG|jpeg|JPEG|png|PNG'; // Extensions which are allowed
			$config['max_size'] = 1024 * 5; // Filesize cannot exceed 5MB
			$config['file_ext_tolower'] = TRUE; // Force file extension to lower case
			$config['remove_spaces'] = TRUE; // Replace space in file names with underscores to avoid break
			$config['detect_mime'] = TRUE; // Detect type of file to avoid code injection

			$this->load->library('upload', $config);

			// File upload
			if ($this->upload->do_upload('file')) {
				// Get data about the file
				$upload_data = $this->upload->data();

				// Generate a random name for the uploaded file
				$file_name = uniqid() . $upload_data['file_ext'];

				// Rename the uploaded file with the random name
				$new_file_path = $config['upload_path'] . '/' . $file_name;
				rename($upload_data['full_path'], $new_file_path);

				// Call the upload_advert function with the new file name
				$this->adverts_model->upload_advert($file_name);
			}
		}
	}



	public function update_adverts()
	{
		$this->session->set_flashdata('status_msg', 'Advert updated successfully.');
		redirect($this->agent->referrer());
	}


	public function publish_advert($photo_id)
	{
		$this->adverts_model->publish_advert($photo_id);
		$this->session->set_flashdata('status_msg', 'Advert published successfully.');
		redirect($this->agent->referrer());
	}


	public function unpublish_advert($photo_id)
	{
		//check advert exists
		$this->check_data_exists($photo_id, 'id', 'adverts', 'adverts');
		$this->adverts_model->unpublish_advert($photo_id);
		$this->session->set_flashdata('status_msg', 'Advert unpublished successfully.');
		redirect($this->agent->referrer());
	}


	public function delete_advert($photo)
	{
		//check advert exists
		$this->check_data_exists($photo, 'id', 'adverts', 'adverts');
		$this->adverts_model->delete_advert($photo);
		$this->session->set_flashdata('status_msg', 'Advert deleted successfully.');
		redirect($this->agent->referrer());
	}


	public function bulk_actions_adverts()
	{
		$this->form_validation->set_rules('check_bulk_action', 'Bulk Select', 'trim');
		$selected_rows = $this->input->post('check_bulk_action', TRUE);

		// Check if selected_rows is an array before counting
		if (is_array($selected_rows)) {
			$selected_rows_count = count($selected_rows);
		} else {
			$selected_rows_count = 0;
		}

		if ($this->form_validation->run()) {
			if ($selected_rows_count > 0) {
				$this->adverts_model->bulk_actions_adverts($selected_rows);
			} else {
				$this->session->set_flashdata('status_msg_error', 'No item selected.');
			}
		} else {
			$this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
		}
		redirect($this->agent->referrer());
	}






}