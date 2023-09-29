<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to Booking pages and functions in admin panel
Models: Bookings_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Bookings extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->admin_restricted(); //allow only logged in users to access this class
		$this->load->model('bookings_model');
		$this->load->model('shipping_model');
		$this->load->model('travellers_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
	}



	/* ========== All Bookings ========== */
	public function index()
	{
		$inner_page_title = 'All Bookings (' . count($this->common_model->get_all_bookings()) . ')';
		$this->admin_header('Admin', $inner_page_title);
		$this->load->view('admin/bookings/all_bookings');
		$this->admin_footer();
	}


	public function all_bookings_ajax()
	{
		$this->load->model('ajax/bookings/bookings_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		foreach ($list as $y) {
			$sign = $y->currency == 'naira' ? '&#8358;' : '&pound;';
			$image_src = base_url('assets/uploads/' . $y->id_photo);
			$selfie_src = base_url('assets/selfie/' . $y->selfie);
			$id_card = user_avatar_table($y->id_photo, $image_src, user_avatar);
			$selfie = user_avatar_table($y->id_photo, $selfie_src, user_avatar);

			$sender_details = '<i class="fa-solid fa-user"></i> ' . $y->sender_name . ' <br >
			                            <i class="fa-solid fa-phone"></i> ' . $y->sender_phone . ' <br > 
										<i class="fa-solid fa-at"></i> ' . $y->sender_email . ' <br > 
										<i class="fa-solid fa-location-dot"></i> ' . $y->sender_address . '';

			$receiver_details = '<i class="fa-solid fa-user"></i> ' . $y->receiver_name . ' <br />
			                            <i class="fa-solid fa-phone"></i> ' . $y->receiver_phone . ' <br /> 
										<i class="fa-solid fa-at"></i> ' . $y->receiver_email . ' <br /> 
										<i class="fa-solid fa-location-dot"></i> ' . $y->receiver_address . '';

			$items = ''; // Initialize $items variable

			$items .= '<table>';
			$items .= '<thead><tr><th>Item</th><th>Category</th><th>Size</th><th>Price</th></tr></thead>';
			$items .= '<tbody>';

			foreach (json_decode($y->items) as $item) {
				$items .= '<tr>';
				$items .= '<td>' . $item->item_name . '</td>';
				$items .= '<td>' . $item->category . '</td>';
				$items .= '<td>' . $item->size . 'KG</td>';
				$items .= '<td>' . $sign . number_format($item->price, 2) . '</td>';
				$items .= '</tr>';
			}

			$items .= '</tbody>';
			$items .= '</table>';

			$delivery_status = ($y->delivery_status == 'Delivered') ? '<span class="text-success"><b>Delivered</b></span>' : (($y->delivery_status == 'In Transit') ? '<span class="text-primary"><b>In Transit</b></span>' : (($y->delivery_status == 'Shipment Created') ? '<span class="text-warning"><b>Shipment Created</b></span>' : '<span class="text-danger"><b>Pending</b></span>'));

			$status = ($y->status == 'Booking Approved') ? '<span class="text-success"><b>Approved</b></span>' : (($y->status == 'Booking Declined') ? '<span class="text-danger"><b>Declined</b></span>' : '<span class="text-warning"><b>Pending</b></span>');

			$receiver_id_details = '<i class="fas fa-bank"></i> ' . $y->bank_name . ' <br /> 
										<i class="fas fa-credit-card"></i> ' . $y->bank_acc . ' <br /> 
										<i class="fa-solid fa-hashtag"></i> ' . $y->sort_code . '';

			$traveller_commission = (75 / 100) * $y->selected_price;

			$row = array();
			$row[] = checkbox_bulk_action($y->id);
			$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = $id_card;
			$row[] = $selfie;
			$row[] = $y->traveller_name;
			$row[] = $sign.number_format($traveller_commission, 2);
			$row[] = $sender_details;
			$row[] = $receiver_details;
			$row[] = $receiver_id_details;
			$row[] = $items;
			$row[] = $y->tracking_id;
			$row[] = $delivery_status;
			$row[] = $status;
			$row[] = x_datetime_full($y->date_added);
			$data[] = $row;
		}
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->current_model->count_all_records(),
			"recordsFiltered" => $this->current_model->count_filtered_records(),
			"data" => $data,
		);
		//output to json format
		echo json_encode($output);
	}


	public function edit_bookings($id)
	{
		//check booking exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$booking_details = $this->common_model->get_booking_details_by_id($id);
		$page_title = 'Edit Booking: ' . $booking_details->traveller_name;
		$this->admin_header($page_title, $page_title);
		$data['y'] = $booking_details;
		$this->load->view('admin/bookings/edit_booking', $data);
		$this->admin_footer();
	}


	public function edit_booking($id)
	{
		//check booking exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$data['booking'] = $this->common_model->get_booking_details_by_id($id);

		$traveller = $this->common_model->get_traveller_details_by_id($data['booking']->traveller_id);

		$data['traveller'] = $traveller;
		$data['traveller_details'] = $traveller;
		$data['currency'] = trim(strtolower($traveller->location)) == 'nigeria' ? 'naira' : 'pounds';
		$data['symbol'] = $data['currency'] == 'naira' ? '&#8358;' : '&pound;';
		//$data['one_pound'] = $data['booking']->rate;
		//$data['one_naira'] = 1 / $data['booking']->rate;

		$this->header('Edit Booking');
		$this->load->view('edit_booking', $data);
		$this->footer();
	}


	public function edit_booking_ajax($id)
	{
		// print_p($this->input->post());
		// return;

		$this->check_data_exists($id, 'id', 'bookings', 'admin');

		$errorUploadType = $statusMsg = '';

		//validate file size
		$validate_image = $this->validate_file_upload('id_photo', 'Image Upload ERROR');
		$validate_selfie = $this->validate_file_upload('selfie', 'Selfie Image - ERROR');

		if ($validate_image or $validate_selfie) {
			$error_list = (($validate_image) ? $validate_image : '') . (($validate_selfie) ? $validate_selfie : '');
			//upload does not happen when file is selected
			$res = ['status' => false, 'msg' => $error_list];
			echo json_encode($res); //show validation errors
			return;
		} else {

			//form validation
			$this->form_validation->set_rules('insurance', 'Insurance', 'trim');
			$this->form_validation->set_rules(
				'sender_name',
				'Sender Name',
				'trim|required',
				array('required' => 'Sender name field is empty')
			);
			$this->form_validation->set_rules(
				'sender_name',
				'Sender Name',
				'trim|required',
				array('required' => 'Sender name field is empty')
			);
			$this->form_validation->set_rules(
				'sender_address',
				'Sender Address',
				'trim|required',
				array('required' => 'Sender address field is empty')
			);
			$this->form_validation->set_rules('sender_phone', 'Mobile', 'trim|required', array('required' => 'Sender phone field is empty'));
			$this->form_validation->set_rules(
				'sender_email',
				'Email',
				'trim|required|valid_email',
				array('valid_email' => 'Invalid Email. @xyz.com')
			);
			// $this->form_validation->set_rules('id_owner', 'ID Owner', 'trim|required', array('required' => 'ID Owner field not selected'));
			$this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required', array('required' => 'Bank Name field is empty'));
			$this->form_validation->set_rules(
				'bank_acc',
				'Bank Account',
				'trim|is_natural|required',
				array(
					'required' => 'Bank Account field is empty',
					'is_natural' => 'Numbers only for Bank account'
				)
			);
			$this->form_validation->set_rules(
				'sort_code',
				'Sort Code',
				'trim|is_natural|required',
				array(
					'required' => 'Sort Code field is empty',
					'is_natural' => 'Numbers only for Sort Code'
				)
			);
			$this->form_validation->set_rules(
				'receiver_name',
				'Receiver Name',
				'trim|required',
				array('required' => 'Receiver address field is empty')
			);
			$this->form_validation->set_rules('receiver_address', 'Receiver Address', 'trim|required', array('required' => 'Receiver address field is empty'));
			$this->form_validation->set_rules('payment_acc', 'Payment Option', 'trim|required', array('required' => 'Payment Option is empty'));
			$this->form_validation->set_rules('receiver_phone', 'Receiver Mobile', 'trim|required', array('required' => 'Receiver phone field is empty'));
			$this->form_validation->set_rules(
				'receiver_email',
				'Receiver Email',
				'trim|required|valid_email',
				array('valid_email' => 'Invalid Email. @xyz.com')
			);
			$this->form_validation->set_rules(
				'id_type',
				'ID Type',
				'trim|required',
				array('required' => 'ID Type not selected')
			);


			if ($this->form_validation->run()) {
				//loading upload library
				$this->load->library('upload');

				$upload_error = "";

				// If id_photo file is selected to upload
				if (!empty($_FILES['id_photo']['name'])) {
					$_FILES['file']['name'] = $_FILES['id_photo']['name'];
					$_FILES['file']['type'] = $_FILES['id_photo']['type'];
					$_FILES['file']['tmp_name'] = $_FILES['id_photo']['tmp_name'];
					$_FILES['file']['error'] = $_FILES['id_photo']['error'];
					$_FILES['file']['size'] = $_FILES['id_photo']['size'];

					// File upload configuration 
					$config['upload_path'] = './assets/uploads'; //path to save the files
					$config['allowed_types'] = 'jpg|JPG|jpeg|JPEG|png|PNG'; //extensions which are allowed
					$config['max_size'] = 1024 * 5; //filesize cannot exceed 5MB
					$config['file_ext_tolower'] = TRUE; //force file extension to lower case
					$config['remove_spaces'] = TRUE; //replace space in file names with underscores to avoid break
					$config['detect_mime'] = TRUE; //detect type of file to avoid code injection
					$config['encrypt_name'] = TRUE; //encrypt file name

					//Creating new configuration for upload
					$this->upload->initialize($config);
					if ($this->upload->do_upload('file')) {
						$upload_data = $this->upload->data();
						$id_photo = $upload_data['file_name'];
					} else {
						// Handle the upload error
						$upload_error .= $this->upload->display_errors();

					}
				} else {
					$id_photo = false;
				}

				// If selfie file is selected to upload
				if (!empty($_FILES['selfie']['name'])) {
					$_FILES['file']['name'] = $_FILES['selfie']['name'];
					$_FILES['file']['type'] = $_FILES['selfie']['type'];
					$_FILES['file']['tmp_name'] = $_FILES['selfie']['tmp_name'];
					$_FILES['file']['error'] = $_FILES['selfie']['error'];
					$_FILES['file']['size'] = $_FILES['selfie']['size'];

					// File upload configuration 
					$config['upload_path'] = './assets/selfie'; //path to save the files
					$config['allowed_types'] = 'jpg|JPG|jpeg|JPEG|png|PNG'; //extensions which are allowed
					$config['max_size'] = 1024 * 5; //filesize cannot exceed 5MB
					$config['file_ext_tolower'] = TRUE; //force file extension to lower case
					$config['remove_spaces'] = TRUE; //replace space in file names with underscores to avoid break
					$config['detect_mime'] = TRUE; //detect type of file to avoid code injection
					$config['encrypt_name'] = TRUE; //encrypt file name

					//Creating new configuration for upload
					$this->upload->initialize($config);
					if ($this->upload->do_upload('file')) {
						$upload_data = $this->upload->data();
						$selfie = $upload_data['file_name'];
					} else {
						// Handle the upload error
						$upload_error .= $this->upload->display_errors();
					}
				} else {
					$selfie = false;
				}

				//displaying error for id_photo file, if any
				if (!empty($upload_error)) {

					$res = ['status' => false, 'msg' => $upload_error . 'Image File Error: ' . $id_photo . ')'];
					echo json_encode($res);
					return;

				}

				//displaying error for selfie file, if any
				if (!empty($upload_error)) {

					$res = ['status' => false, 'msg' => $upload_error . 'Image File Error: ' . $selfie . ')'];
					echo json_encode($res);
					return;

				}


				if ($this->bookings_model->edit_booking_to_db($id, $id_photo, $selfie)) {

					$res = ['status' => true];
					echo json_encode($res);
					return;

				} else {

					$res = ['status' => false, 'msg' => 'Booking could not be uploaded, try again later.'];
					echo json_encode($res);
					return;

				}
			} else {
				$res = ['status' => false, 'msg' => validation_errors()];
				echo json_encode($res); //show validation errors
				return;
			}

		}

	}


	/* ========== View Booking ========== */
	public function view_booking($id)
	{
		//check bookings exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$bookings_details = $this->common_model->get_booking_details_by_id($id);
		$page_title = 'Booking Info: ' . $bookings_details->sender_name;
		$this->admin_header($page_title, $page_title);
		$data['y'] = $bookings_details;
		$this->load->view('admin/bookings/view_booking', $data);
		$this->admin_footer();
	}


	/* ========== View Shipping ========== */
	public function view_shipping($booking_id)
	{
		// Check if booking exists
		$this->check_data_exists($booking_id, 'id', 'bookings', 'admin');

		// Get booking details
		$booking_details = $this->common_model->get_booking_details_by_id($booking_id);
		$page_title = 'Shipping Info: ' . $booking_details->sender_name;
		$this->admin_header($page_title, $page_title);

		// Get shipping details using the tracking ID from the booking
		$shipping_details = $this->common_model->get_shipping_details_by_tracking_id($booking_details->tracking_id);

		$data['shipping_details'] = $shipping_details;
		$this->load->view('admin/bookings/view_booking', $data);
		$this->admin_footer();
	}


	public function edit_shipping($booking_id)
	{
		$this->form_validation->set_rules('heading', 'Heading', 'trim|required');
		$this->form_validation->set_rules('body', 'Body', 'trim|min_length[2]|max_length[500]|required');
		$this->form_validation->set_rules('delivery_status', 'Delivery Status', 'trim|required');

		if ($this->form_validation->run()) {
			$this->shipping_model->edit_shipping($booking_id);
			$this->session->set_flashdata('status_msg', "Shipping updated successfully.");
			redirect($this->agent->referrer());
		} else {
			echo validation_errors();
		}
	}


	public function approve($id)
	{
		$this->bookings_model->approve_booking($id);
		$this->session->set_flashdata('status_msg', 'Booking Approved Successfully.');
		redirect($this->agent->referrer());
	}


	public function decline($id)
	{
		$this->bookings_model->decline_booking($id);
		$this->session->set_flashdata('status_msg', 'Booking Declined Successfully.');
		redirect($this->agent->referrer());
	}


	public function delete_booking($id)
	{
		//check admin exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$this->bookings_model->delete_booking($id);
		$this->session->set_flashdata('status_msg', 'Booking deleted successfully.');
		redirect($this->agent->referrer());
	}


	public function delete_shipping($id)
	{
		//check admin exists
		$this->check_data_exists($id, 'id', 'shipping', 'admin');
		$this->shipping_model->delete_shipping($id);
		$this->session->set_flashdata('status_msg', 'Shipping Info deleted successfully.');
		redirect($this->agent->referrer());
	}


	public function bulk_actions_booking()
	{
		$this->form_validation->set_rules('check_bulk_action', 'Bulk Select', 'trim');
		$selected_rows = count($this->input->post('check_bulk_action', TRUE));
		if ($this->form_validation->run()) {
			if ($selected_rows > 0) {
				$this->bookings_model->bulk_actions_booking();
			} else {
				$this->session->set_flashdata('status_msg_error', 'No item selected.');
			}
		} else {
			$this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
		}
		redirect($this->agent->referrer());
	}



	public function check()
	{
		var_dump($booking = $this->common_model->one_naira());

	}


}