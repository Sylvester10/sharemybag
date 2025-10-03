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



class Admin_bookings extends MY_Controller
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
			$sign = '&pound;';
			$traveller = $this->common_model->get_traveller_details_by_id($y->traveller_id);

			// traveller details
			$traveller_details = '<i class="fa-solid fa-user"></i> ' . $y->traveller_name . '<br />
								<i class="fa-solid fa-phone"></i> ' . $y->traveller_contact . '<br />
								<i class="fa-solid fa-location"></i> ' . $y->traveller_drop_address1 . '<br />
								<i class="fa-solid fa-calendar"></i> ' . x_date($y->traveller_drop_date1);

			$user_details = $y->payment_method == 'offline'
				? 'N/A'
				: '<i class="fa-solid fa-user"></i> ' . $y->user_fullname . '<br />
							<i class="fa-solid fa-at"></i> ' . $y->user_email;

			$agent_details = $y->payment_method == 'offline'
				? 'N/A'
				: '<i class="fa-solid fa-user"></i> ' . $y->agent_name . '<br />
							<i class="fa-solid fa-phone"></i> ' . $y->agent_phone . '<br /> 
							<i class="fa-solid fa-at"></i> ' . $y->agent_email . '<br /> 
							<i class="fa-solid fa-location-dot"></i> ' . $y->agent_address;

			// receiver details
			$receiver_details = $y->payment_method == 'offline'
				? 'N/A'
				: '<i class="fa-solid fa-user"></i> ' . $y->receiver_name . ' <br />
								<i class="fa-solid fa-phone"></i> ' . $y->receiver_phone . ' <br /> 
								<i class="fa-solid fa-at"></i> ' . $y->receiver_email . ' <br /> 
								<i class="fa-solid fa-location-dot"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

			$item_details = ''; // Initialize $item_details variable
			$item_details .= '<table class="table text-nowrap fs-2">';
			$item_details .= '<thead><tr><th>Item</th><th>Category</th><th>Size</th><th>Price</th></tr></thead>';
			$item_details .= '<tbody>';

			// Decode items JSON safely
			$decoded_items = json_decode($y->items);
			if (!is_array($decoded_items)) {
				$decoded_items = []; // Handle invalid JSON
			}

			// Initialize total size and currency sign
			$total_size = 0;
			$sign = '£'; // Example currency sign

			foreach ($decoded_items as $item) {
				$item_details .= '<tr>';
				$item_details .= '<td>' . $item->item_name . '</td>';
				$item_details .= '<td>' . $item->category . '</td>';
				$item_details .= '<td>' . $item->size . 'KG</td>';

				// if ($traveller->destination === 'United Kingdom') {

				// 	$item_details .= '<td>' . $item->size . 'KG</td>';
				// } else {

				// 	$item_details .= '<td>' . $item->size . ' Piece(s)</td>';
				// }

				$item_details .= '<td>' . $sign . number_format($item->price, 2) . '</td>';
				$item_details .= '</tr>';

				$total_size += $item->size; // Calculate total size
			}

			$item_details .= '</tbody>';
			$item_details .= '</table>';

			$delivery_status = match ($y->delivery_status) {
				'Delivered' => '<span class="text-success"><b>Delivered</b></span>',
				'In Transit' => '<span class="text-primary"><b>In Transit</b></span>',
				'Shipment Created' => '<span class="text-warning"><b>Shipment Created</b></span>',
				default => '<span class="text-danger"><b>Pending</b></span>',
			};

			$payment_status = $y->payment_status == 'completed' ? '<span class="text-success"><b>Paid</b></span>' : ($y->payment_status == 'canceled' ? '<span class="text-danger"><b>Canceled</b></span>' :
				'<span class="text-warning"><b>Pending</b></span>');

			// Calculate traveller commission
			// $traveller_commission = 5 * $y->selected_space;

			$traveller_commission = $traveller->destination == 'Nigeria'
				? 4.50 * $y->selected_space
				: 5 * $y->selected_space;

			$commission = $y->payment_status == 'completed'
				? $sign . number_format($traveller_commission, 2)
				: 'N/A';

			$total_amount =  $y->payment_method == 'offline'
				? 'Payment method: Offline'
				: 'Total amount: £' . $y->total_amount . ' <br />
							Payment method: ' . $y->payment_method . '';

			$row = array();
			$row[] = checkbox_bulk_action($y->id);
			$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = x_datetime_full($y->date_added);
			$row[] = $traveller_details;
			$row[] = $commission;
			$row[] = $user_details;
			$row[] = $agent_details;
			$row[] = $receiver_details;
			$row[] = $y->need_help;
			$row[] = $item_details;
			$row[] = $total_amount;
			$row[] = $payment_status;
			// 			$row[] = $delivery_status;
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


	public function completed_bookings()
	{
		$inner_page_title = 'Completed Bookings (' . count($this->common_model->get_completed_bookings()) . ')';
		$this->admin_header('Admin', $inner_page_title);
		$this->load->view('admin/bookings/completed_bookings');
		$this->admin_footer();
	}


	public function completed_bookings_ajax()
	{
		$this->load->model('ajax/bookings/completed_bookings_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		foreach ($list as $y) {
			$sign = '&pound;';
			$traveller = $this->common_model->get_traveller_details_by_id($y->traveller_id);

			// traveller details
			$traveller_details = '<i class="fa-solid fa-user"></i> ' . $y->traveller_name . '<br />
								<i class="fa-solid fa-phone"></i> ' . $y->traveller_contact . '<br />
								<i class="fa-solid fa-location"></i> ' . $y->traveller_drop_address1 . '<br />
								<i class="fa-solid fa-calendar"></i> ' . x_date($y->traveller_drop_date1);

			$user_details = $y->payment_method == 'offline'
				? 'N/A'
				: '<i class="fa-solid fa-user"></i> ' . $y->user_fullname . '<br />
							<i class="fa-solid fa-at"></i> ' . $y->user_email;

			$agent_details = $y->payment_method == 'offline'
				? 'N/A'
				: '<i class="fa-solid fa-user"></i> ' . $y->agent_name . '<br />
							<i class="fa-solid fa-phone"></i> ' . $y->agent_phone . '<br /> 
							<i class="fa-solid fa-at"></i> ' . $y->agent_email . '<br /> 
							<i class="fa-solid fa-location-dot"></i> ' . $y->agent_address;

			// receiver details
			$receiver_details = '<i class="fa-solid fa-user"></i> ' . $y->receiver_name . ' <br />
								<i class="fa-solid fa-phone"></i> ' . $y->receiver_phone . ' <br /> 
								<i class="fa-solid fa-at"></i> ' . $y->receiver_email . ' <br /> 
								<i class="fa-solid fa-location-dot"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

			$item_details = '';
			$item_details .= '<table class="table text-nowrap fs-2">';
			$item_details .= '<thead><tr>
								<th>Item</th>
								<th>Category</th>
								<th>Size</th>
								<th>Price</th>
							</tr></thead>';
			$item_details .= '<tbody>';

			// Decode items JSON safely
			$decoded_items = json_decode($y->items);
			if (!is_array($decoded_items) && !is_object($decoded_items)) {
				$decoded_items = [];
			}

			if (!empty($decoded_items)) {
				foreach ($decoded_items as $item) {
					$item_details .= '<tr>';
					$item_details .= '<td>' . $item->item_name . '</td>';
					$item_details .= '<td>' . $item->category . '</td>';
					$item_details .= '<td>' . $item->size . 'KG</td>';
					$item_details .= '<td>&pound;' . number_format($item->price, 2) . '</td>';
					$item_details .= '</tr>';
				}
			} else {
				$item_details .= '<tr><td colspan="5">No items found</td></tr>';
			}

			$item_details .= '</tbody>';
			$item_details .= '</table>';

			$delivery_status = match ($y->delivery_status) {
				'Delivered' => '<span class="text-success"><b>Delivered</b></span>',
				'In Transit' => '<span class="text-primary"><b>In Transit</b></span>',
				'Shipment Created' => '<span class="text-warning"><b>Shipment Created</b></span>',
				default => '<span class="text-danger"><b>Pending</b></span>',
			};

			$payment_method = match ($y->payment_method) {
				'stripe' => '<img src="' . base_url('assets/general/stripe.svg') . '" alt="Stripe" width="50" height="20">',
				'paystack' => '<img src="' . base_url('assets/general/paystack.svg') . '" alt="Paystack" width="70" height="20">',
				default => 'N/A',
			};

			$payment_status = $y->payment_status == 'completed' ? '<span class="text-success"><b>Paid</b></span>' : ($y->payment_status == 'canceled' ? '<span class="text-danger"><b>Canceled</b></span>' :
				'<span class="text-warning"><b>Pending</b></span>');

			// Calculate traveller commission
			// $traveller_commission = 5 * $y->selected_space;

			$traveller_commission = $traveller->destination == 'Nigeria'
				? 4.50 * $y->selected_space
				: 5 * $y->selected_space;

			$commission = $y->payment_status == 'completed'
				? $sign . number_format($traveller_commission, 2)
				: 'N/A';

			$total_amount = 'Total amount: £' . $y->total_amount . ' <br />
                             Payment method: ' . $payment_method . ' ';

			$row = array();
			$row[] = checkbox_bulk_action($y->id);
			$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = x_datetime_full($y->date_added);
			$row[] = $traveller_details;
			$row[] = $commission;
			$row[] = $user_details;
			$row[] = $agent_details;
			$row[] = $receiver_details;
			$row[] = $y->need_help;
			$row[] = $item_details;
			$row[] = $total_amount;
			$row[] = $payment_status;
			// $row[] = $delivery_status;
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

	public function canceled_bookings()
	{
		$inner_page_title = 'Canceled Bookings (' . count($this->common_model->get_canceled_bookings()) . ')';
		$this->admin_header('Admin', $inner_page_title);
		$this->load->view('admin/bookings/canceled_bookings');
		$this->admin_footer();
	}

	public function canceled_bookings_ajax()
	{
		$this->load->model('ajax/bookings/canceled_bookings_ajax', 'current_model');
		$list = $this->current_model->get_records();
		$data = array();
		foreach ($list as $y) {
			$sign = '&pound;';
			$traveller = $this->common_model->get_traveller_details_by_id($y->traveller_id);

			// traveller details
			$traveller_details = '<i class="fa-solid fa-user"></i> ' . $y->traveller_name . '<br />
								<i class="fa-solid fa-phone"></i> ' . $y->traveller_contact . '<br />
								<i class="fa-solid fa-location"></i> ' . $y->traveller_drop_address1 . '<br />
								<i class="fa-solid fa-calendar"></i> ' . x_date($y->traveller_drop_date1);

			$user_details = $y->payment_method == 'offline'
				? 'N/A'
				: '<i class="fa-solid fa-user"></i> ' . $y->user_fullname . '<br />
							<i class="fa-solid fa-at"></i> ' . $y->user_email;

			$agent_details = $y->payment_method == 'offline'
				? 'N/A'
				: '<i class="fa-solid fa-user"></i> ' . $y->agent_name . '<br />
							<i class="fa-solid fa-phone"></i> ' . $y->agent_phone . '<br /> 
							<i class="fa-solid fa-at"></i> ' . $y->agent_email . '<br /> 
							<i class="fa-solid fa-location-dot"></i> ' . $y->agent_address;

			// receiver details
			$receiver_details = '<i class="fa-solid fa-user"></i> ' . $y->receiver_name . ' <br />
									<i class="fa-solid fa-phone"></i> ' . $y->receiver_phone . ' <br /> 
									<i class="fa-solid fa-at"></i> ' . $y->receiver_email . ' <br /> 
									<i class="fa-solid fa-location-dot"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

			$item_details = ''; // Initialize $item_details variable
			$item_details .= '<table class="table text-nowrap fs-2">';
			$item_details .= '<thead><tr><th>Item</th><th>Category</th><th>Size</th><th>Price</th></tr></thead>';
			$item_details .= '<tbody>';

			// Decode items JSON safely
			$decoded_items = json_decode($y->items);
			if (!is_array($decoded_items)) {
				$decoded_items = []; // Handle invalid JSON
			}

			// Initialize total size and currency sign
			$total_size = 0;
			$sign = '£'; // Example currency sign

			foreach ($decoded_items as $item) {
				$item_details .= '<tr>';
				$item_details .= '<td>' . $item->item_name . '</td>';
				$item_details .= '<td>' . $item->category . '</td>';

				if ($traveller->destination === 'United Kingdom') {

					$item_details .= '<td>' . $item->size . 'KG</td>';
				} else {

					$item_details .= '<td>' . $item->size . ' Piece(s)</td>';
				}

				$item_details .= '<td>' . $sign . number_format($item->price, 2) . '</td>';
				$item_details .= '</tr>';

				$total_size += $item->size; // Calculate total size
			}

			$item_details .= '</tbody>';
			$item_details .= '</table>';

			$delivery_status = match ($y->delivery_status) {
				'Delivered' => '<span class="text-success"><b>Delivered</b></span>',
				'In Transit' => '<span class="text-primary"><b>In Transit</b></span>',
				'Shipment Created' => '<span class="text-warning"><b>Shipment Created</b></span>',
				default => '<span class="text-danger"><b>Pending</b></span>',
			};

			$payment_status = $y->payment_status == 'completed' ? '<span class="text-success"><b>Paid</b></span>' : ($y->payment_status == 'canceled' ? '<span class="text-danger"><b>Canceled</b></span>' :
				'<span class="text-warning"><b>Pending</b></span>');

			// Calculate traveller commission
			// $traveller_commission = 5 * $y->selected_space;

			$traveller_commission = $traveller->destination == 'Nigeria'
				? 4.50 * $y->selected_space
				: 5 * $y->selected_space;

			$commission = $y->payment_status == 'completed'
				? $sign . number_format($traveller_commission, 2)
				: 'N/A';

			$total_amount = 'Total amount: £' . $y->total_amount . ' <br />
                             Payment method: ' . $y->payment_method . '';

			$row = array();
			$row[] = checkbox_bulk_action($y->id);
			$row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
			$row[] = x_datetime_full($y->date_added);
			$row[] = $traveller_details;
			$row[] = $commission;
			$row[] = $user_details;
			$row[] = $agent_details;
			$row[] = $receiver_details;
			$row[] = $y->need_help;
			$row[] = $item_details;
			$row[] = $total_amount;
			$row[] = $payment_status;
			// $row[] = $delivery_status;
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


	/* ========== View Booking ========== */
	public function view_booking($id)
	{
		//check bookings exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$bookings_details = $this->common_model->get_booking_details_by_id($id);
		$page_title = 'Booking Info: ' . $bookings_details->agent_name;
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
		$page_title = 'Shipping Info: ' . $booking_details->agent_name;
		$this->admin_header($page_title, $page_title);

		// Get shipping details using the tracking ID from the booking
		$shipping_details = $this->common_model->get_shipping_details_by_tracking_id($booking_details->tracking_id);

		$data['shipping_details'] = $shipping_details;
		$this->load->view('admin/bookings/view_booking', $data);
		$this->admin_footer();
	}


	/* ========== Update shipping ========== */
	public function update_shipping($id)
	{
		//check travellers exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$shipping_details = $this->common_model->get_booking_details_by_id($id);
		$page_title = 'Update Shipping Info: ' . $shipping_details->agent_name;
		$this->admin_header($page_title, $page_title);
		$data['y'] = $shipping_details;
		$data['traveller_details'] = $this->common_model->get_traveller_details_by_id($id);
		$this->load->view('admin/shipping/update_shipping', $data);
		$this->admin_footer();
	}


	public function update_shipping_ajax()
	{
		// validation rules
		$this->form_validation->set_rules('tracking_id', 'Tracking ID', 'trim|required');
		$this->form_validation->set_rules('heading', 'Heading', 'trim|required');
		$this->form_validation->set_rules('body', 'Body', 'trim|min_length[2]|max_length[500]|required');

		if ($this->form_validation->run()) {

			$shipping_details = $this->common_model->get_booking_details_by_id($id);
			$booking_id = $shipping_details->id;
			$this->shipping_model->add_shipping_to_db();
			$this->session->set_flashdata('status_msg', "Shipping info updated successfully.");
			redirect($this->agent->referrer());
		} else {
			echo validation_errors();
		}
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


	public function confirm_booking($id)
	{
		$this->bookings_model->confirm_booking($id);
		$this->session->set_flashdata('status_msg', 'Booking Confirmed Successfully.');
		redirect($this->agent->referrer());
	}


	public function cancel_booking($id)
	{
		$this->bookings_model->cancel_booking($id);
		$this->session->set_flashdata('status_msg', 'Booking Cancelled Successfully.');
		redirect($this->agent->referrer());
	}


	public function delete_booking($id)
	{
		//check admin exists
		$this->check_data_exists($id, 'id', 'bookings', 'admin');
		$this->bookings_model->delete_booking($id);

		// Recalculate bag space
		$this->travellers_model->update_traveller_space($id);
		$this->session->set_flashdata('status_msg', 'Booking data deleted successfully.');
		redirect($this->agent->referrer());
	}


	public function delete_shipping($id)
	{
		//check admin exists
		$this->check_data_exists($id, 'id', 'shipping', 'admin');
		$this->shipping_model->delete_shipping($id);
		$this->session->set_flashdata('status_msg', 'Shipping data deleted successfully.');
		redirect($this->agent->referrer());
	}


	public function bulk_actions_booking()
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
				$this->bookings_model->bulk_actions_booking($selected_rows);
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
