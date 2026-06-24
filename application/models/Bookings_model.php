<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation =====
Name: Bookings_model
Role: Model
Description: Controls booking persistence, pricing updates, and booking-side effects
Controller: User_bookings, Admin_bookings, Home
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Bookings_model extends \MY_Model
{
	public $traveller_details;
	public function __construct()
	{
		parent::__construct();
		$this->table = 'bookings';
		$this->primary_cols = array('id');
		$this->load->model('booking_read_model');
		$this->load->model('traveller_read_model');
		$this->load->model('travellers_model');
		$this->load->model('finance_read_model');
		$this->load->model('shipping_read_model');
		$this->traveller_details = $this->traveller_read_model->get_traveller_details_by_id($this->session->id);
	}



	public function add_booking_to_db($id_photo, $selfie)
	{
		//generate and update tracking ID
		$tracking_id = generate_unique_tracking_id('bookings', 'tracking_id');

		$calculations = json_decode($this->input->post('price_calculations'));

		// ── EXPLICIT FIELD ALLOWLIST (SEC-007) ──
		$data = array(
			'status'            => booking_status_normalize('Pending'),
			'delivery_status'   => delivery_status_normalize('Pending'),
			'tracking_id'       => $tracking_id,
			'id_photo'          => $id_photo,
			'selfie'            => $selfie,
			'new'               => 0,
			'insurance'       => isset($calculations->insurance)     ? (float) $calculations->insurance     : 0,
			'total_amount'    => isset($calculations->totalAmount)   ? round((float) $calculations->totalAmount, 2)   : 0,
			'sub_total'       => isset($calculations->subTotal)      ? round((float) $calculations->subTotal, 2)      : 0,
			'vat'             => isset($calculations->vat)           ? round((float) $calculations->vat, 2)           : 0,
			'service_charge'  => isset($calculations->serviceCharge) ? round((float) $calculations->serviceCharge, 2) : 0,
			'selected_space'  => isset($calculations->selectedSpace) ? (float) $calculations->selectedSpace : 0,
			'selected_price'  => isset($calculations->selectedPrice) ? round((float) $calculations->selectedPrice, 2) : 0,
			'traveller_id'              => (int) $this->input->post('traveller_id', TRUE),
			'traveller_name'            => $this->input->post('traveller_name', TRUE),
			'traveller_email'           => $this->input->post('traveller_email', TRUE),
			'traveller_contact'         => $this->input->post('traveller_contact', TRUE),
			'traveller_travel_date'     => $this->input->post('traveller_travel_date', TRUE),
			'traveller_departure_date'  => $this->input->post('traveller_departure_date', TRUE),
			'traveller_arrival_date'    => $this->input->post('traveller_arrival_date', TRUE),
			'traveller_drop_address1'   => $this->input->post('traveller_drop_address1', TRUE),
			'traveller_drop_date1'      => $this->input->post('traveller_drop_date1', TRUE),
			'traveller_drop_address2'   => $this->input->post('traveller_drop_address2', TRUE),
			'traveller_drop_date2'      => $this->input->post('traveller_drop_date2', TRUE),
			'traveller_departure_state' => $this->input->post('traveller_departure_state', TRUE),
			'traveller_current_state'   => $this->input->post('traveller_current_state', TRUE),
			'traveller_arrival_airport' => $this->input->post('traveller_arrival_airport', TRUE),
			'traveller_arrival_state'   => $this->input->post('traveller_arrival_state', TRUE),
			'agent_name'      => $this->input->post('agent_name', TRUE),
			'agent_phone'     => $this->input->post('agent_country_code', TRUE) . $this->input->post('agent_phone', TRUE),
			'agent_email'     => $this->input->post('agent_email', TRUE),
			'agent_address'   => $this->input->post('agent_address', TRUE),
			'agent_locality'  => $this->input->post('agent_locality', TRUE),
			'agent_postcode'  => $this->input->post('agent_postcode', TRUE),
			'receiver_name'      => $this->input->post('receiver_name', TRUE),
			'receiver_phone'     => $this->input->post('receiver_country_code', TRUE) . $this->input->post('receiver_phone', TRUE),
			'receiver_email'     => $this->input->post('receiver_email', TRUE),
			'receiver_address'   => $this->input->post('receiver_address', TRUE),
			'receiver_locality'  => $this->input->post('receiver_locality', TRUE),
			'receiver_postcode'  => $this->input->post('receiver_postcode', TRUE),
			'payment_method'  => $this->input->post('payment_method', TRUE),
			'items'           => $this->input->post('items', TRUE),
			'need_help'       => $this->input->post('need_help', TRUE),
		);


		$this->db->trans_start();

		$this->db->insert('bookings', $data);

		//Update the tracking ID, used space and available space in the traveller table
		$this->travellers_model->update_traveller_space($data['traveller_id']);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Transaction failed in ' . __METHOD__);
			return false;
		}

		$this->finance_read_model->clearFinanceSummaryCaches();
		$this->booking_read_model->clearBookingCountCaches();

		//Send email to Admin
		try {

			require_once 'application/third_party/mail.php';
			$mail->addAddress('onyekaesso10@gmail.com');
			$mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
			$mail->isHTML(true); // Set email format to HTML
			$mail->Subject = "New Booking";
			$body = $this->load->view('mail/admin_booking_notify_email', $data, true);
			$mail->Body = $body;
			$mail->AltBody = $body;
			$mail->send();
		} catch (Exception $e) {
		}

		return true;
	}


	public function edit_booking_to_db($id, $id_photo, $selfie)
	{
		//Get previous booking details
		$booking = $this->booking_read_model->get_booking_details_by_id($id);

		$calculations = json_decode($this->input->post('price_calculations'));

		// Define the file path
		$id_photo_path = 'assets/uploads/' . $booking->id_photo;
		$selfie_photo_path = 'assets/selfie/' . $booking->selfie;

		// Delete the image file
		($id_photo) ? unlink($id_photo_path) : '';
		($selfie) ? unlink($selfie_photo_path) : '';

		// ── EXPLICIT FIELD ALLOWLIST (SEC-007) ──
		$data = array(
			'status'            => booking_status_normalize('Pending'),
			'delivery_status'   => delivery_status_normalize('Pending'),
			'insurance'       => isset($calculations->insurance)     ? (float) $calculations->insurance     : 0,
			'total_amount'    => isset($calculations->totalAmount)   ? round((float) $calculations->totalAmount, 2)   : 0,
			'sub_total'       => isset($calculations->subTotal)      ? round((float) $calculations->subTotal, 2)      : 0,
			'vat'             => isset($calculations->vat)           ? round((float) $calculations->vat, 2)           : 0,
			'service_charge'  => isset($calculations->serviceCharge) ? round((float) $calculations->serviceCharge, 2) : 0,
			'selected_space'  => isset($calculations->selectedSpace) ? (float) $calculations->selectedSpace : 0,
			'selected_price'  => isset($calculations->selectedPrice) ? round((float) $calculations->selectedPrice, 2) : 0,
			'traveller_id'              => (int) $this->input->post('traveller_id', TRUE),
			'traveller_name'            => $this->input->post('traveller_name', TRUE),
			'traveller_email'           => $this->input->post('traveller_email', TRUE),
			'traveller_contact'         => $this->input->post('traveller_contact', TRUE),
			'traveller_travel_date'     => $this->input->post('traveller_travel_date', TRUE),
			'traveller_departure_date'  => $this->input->post('traveller_departure_date', TRUE),
			'traveller_arrival_date'    => $this->input->post('traveller_arrival_date', TRUE),
			'traveller_drop_address1'   => $this->input->post('traveller_drop_address1', TRUE),
			'traveller_drop_date1'      => $this->input->post('traveller_drop_date1', TRUE),
			'traveller_drop_address2'   => $this->input->post('traveller_drop_address2', TRUE),
			'traveller_drop_date2'      => $this->input->post('traveller_drop_date2', TRUE),
			'traveller_departure_state' => $this->input->post('traveller_departure_state', TRUE),
			'traveller_current_state'   => $this->input->post('traveller_current_state', TRUE),
			'traveller_arrival_airport' => $this->input->post('traveller_arrival_airport', TRUE),
			'traveller_arrival_state'   => $this->input->post('traveller_arrival_state', TRUE),
			'agent_name'      => $this->input->post('agent_name', TRUE),
			'agent_phone'     => $this->input->post('agent_country_code', TRUE) . $this->input->post('agent_phone', TRUE),
			'agent_email'     => $this->input->post('agent_email', TRUE),
			'agent_address'   => $this->input->post('agent_address', TRUE),
			'agent_locality'  => $this->input->post('agent_locality', TRUE),
			'agent_postcode'  => $this->input->post('agent_postcode', TRUE),
			'receiver_name'      => $this->input->post('receiver_name', TRUE),
			'receiver_phone'     => $this->input->post('receiver_country_code', TRUE) . $this->input->post('receiver_phone', TRUE),
			'receiver_email'     => $this->input->post('receiver_email', TRUE),
			'receiver_address'   => $this->input->post('receiver_address', TRUE),
			'receiver_locality'  => $this->input->post('receiver_locality', TRUE),
			'receiver_postcode'  => $this->input->post('receiver_postcode', TRUE),
			'payment_method'  => $this->input->post('payment_method', TRUE),
			'items'           => $this->input->post('items', TRUE),
			'need_help'       => $this->input->post('need_help', TRUE),
		);
		if ($id_photo) $data['id_photo'] = $id_photo;
		if ($selfie) $data['selfie'] = $selfie;


		$this->update($data, $id);

		//Update the tracking ID, used space and available space in the traveller table
		$this->travellers_model->update_traveller_space($data['traveller_id']);
		$this->finance_read_model->clearFinanceSummaryCaches();
		$this->booking_read_model->clearBookingCountCaches();

		return true;
	}


	public function get_available_bookings($limit, $offset)
	{
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("date_added", "DESC"); //order by date_unix ASC so that the dates appear chronologically
		$query = $this->db->where('status', 'Available');
		$this->applyNotDeleted();
		$query = $this->db->get('bookings');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}


	public function get_booking($limit, $offset)
	{
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("date_added", "DESC"); //order by date_unix ASC so that the dates appear chronologically
		$this->applyNotDeleted();
		$query = $this->db->get_where('bookings');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}


	public function count_available_bookings()
	{ //get all available booking
		$query = $this->db->where('status', 'Available');
		$this->applyNotDeleted();
		return $this->db->get_where('bookings')->num_rows();
	}


	public function count_pending_bookings()
	{ //get all available booking
		$query = $this->db->where('status', 'Pending');
		$this->applyNotDeleted();
		return $this->db->get_where('bookings')->num_rows();
	}


	public function count_unavailable_bookings()
	{ //get all available booking
		$query = $this->db->where('status', 'Unavailable');
		$this->applyNotDeleted();
		return $this->db->get_where('bookings')->num_rows();
	}


	public function count_bookings()
	{ //get all booking
		$this->applyNotDeleted();
		return $this->db->get_where('bookings')->num_rows();
	}


	public function approve_booking($id)
	{
		$data = array(
			'status' => booking_status_normalize('Approved'),
		);

		$this->update($data, $id);

		$y = $this->booking_read_model->get_booking_details_by_id($id);
		$data['agent_name'] = $y->agent_name;
		$data['agent_phone'] = $y->agent_phone;
		$data['agent_email'] = $y->agent_email;
		$data['receiver_name'] = $y->receiver_name;
		$data['receiver_phone'] = $y->receiver_phone;
		$data['receiver_email'] = $y->receiver_email;
		$data['total_amount'] = $y->total_amount;

		//get 75% of selected items
		$data['selected_amount'] = (75 / 100) * $y->selected_price;

		$data['tracking_id'] = $y->tracking_id;
		$data['date_added'] = x_datetime_full($y->date_added);
		$data['traveller_name'] = $y->traveller_name;
		$data['traveller_email'] = $y->traveller_email;
		//$data['traveller_available_space'] = $y->traveller_available_space;
		$data['traveller_drop_address1'] = ($y->traveller_drop_address1 == '') ? 'N/A' : $y->traveller_drop_address1;
		$data['traveller_drop_date1'] = ($y->traveller_drop_date1 == '') ? 'N/A' : x_date($y->traveller_drop_date1);
		$data['traveller_drop_address2'] = ($y->traveller_drop_address2 == '') ? 'N/A' : $y->traveller_drop_address2;
		$data['traveller_drop_date2'] = ($y->traveller_drop_date2 == '') ? 'N/A' : x_date($y->traveller_drop_date2);
		$data['traveller_contact'] = $y->traveller_contact;
		$data['traveller_departure_date'] = x_date($y->traveller_departure_date);
		$data['traveller_arrival_date'] = x_date($y->traveller_arrival_date);
		$data['traveller_departure_state'] = ($y->traveller_departure_state == '') ? 'N/A' : $y->traveller_departure_state;
		$data['currency_symbol'] = currency_symbol($y->currency);
		$data['items'] = $y->items;
		$data['insurance'] = ($y->insurance == '0') ? 'N/A' : $y->insurance;
		$data['new'] = 0;

		//Send email to agent
		try {

			require_once 'application/third_party/mail.php';
			$mail->addAddress($y->agent_email);
			//$mail->AddEmbeddedImage('application/third_party/image/check.png', 'check', 'check.png');
			$mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
			$mail->isHTML(true); // Set email format to HTML
			$mail->Subject = "Booking Successful";
			$body = $this->load->view('mail/agent_booking_notify_email', $data, true);
			$mail->Body = $body;
			$mail->AltBody = $body;


			$mail->send();
		} catch (Exception $e) {
		}

		//Send email to Traveller
		try {

			require_once 'application/third_party/mail.php';
			$mail->addAddress($y->traveller_email);
			//$mail->AddEmbeddedImage('application/third_party/image/check.png', 'check', 'check.png');
			$mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
			$mail->isHTML(true); // Set email format to HTML
			$mail->Subject = "Bag Space Bought";
			$body = $this->load->view('mail/traveller_booking_notify_email', $data, true);
			$mail->Body = $body;
			$mail->AltBody = $body;


			$mail->send();
		} catch (Exception $e) {
		}

		return;
	}


	public function decline_booking($id)
	{
		$data = array(
			'status' => booking_status_normalize('Declined'),
		);
		$this->db->where('id', $id);
		$this->db->update('bookings', $data);

		return;
	}


	public function confirm_booking($id)
	{
		$this->db->trans_start();

		$data = array(
			'payment_status' => payment_status_normalize('completed'),
		);
		$this->db->where('id', $id);
		$this->db->update('bookings', $data);

		$booking = $this->booking_read_model->get_booking_details_by_id($id);
		$traveller_id = $booking->traveller_id;
		$email = $booking->agent_email;

		//Update the tracking ID, used space and available space in the traveller table
		$this->travellers_model->update_traveller_space($traveller_id);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Transaction failed in ' . __METHOD__);
			return false;
		}

		$this->finance_read_model->clearFinanceSummaryCaches();
		$this->booking_read_model->clearBookingCountCaches();

		$data['tracking_id'] = $booking->tracking_id;
		$data['total_amount'] = $booking->total_amount;
		$data['agent_name'] = $booking->agent_name;
		$data['date_added'] = x_date($booking->date_added);
		$data['items'] = $booking->items;
		$data['insurance'] = $booking->insurance;
		$data['traveller_name'] = $booking->traveller_name;
		$data['traveller_contact'] = $booking->traveller_contact;
		$data['traveller_departure_state'] = $booking->traveller_departure_state;
		$data['traveller_drop_address1'] = $booking->traveller_drop_address1;
		$data['traveller_drop_date1'] = x_date($booking->traveller_drop_date1);
		$data['traveller_drop_address2'] = $booking->traveller_drop_address2 == '' ? 'N/A' : $booking->traveller_drop_address2;
		$data['traveller_drop_date2'] = $booking->traveller_drop_date2 == '' ? 'N/A' : $booking->traveller_drop_date2;
		$data['traveller_departure_date'] = x_date($booking->traveller_departure_date);
		$data['traveller_arrival_date'] = $booking->traveller_arrival_date == '' ? 'N/A' : x_date($booking->traveller_arrival_date);

		$data['traveller_current_state'] = $booking->traveller_current_state;
		$data['traveller_arrival_airport'] = $booking->traveller_arrival_airport;
		$data['traveller_arrival_state'] = $booking->traveller_arrival_state;

		// Send email to Admin and User
		// send_email_notification($this, 'customers@sharemybag.co.uk', 'New Booking', $data, 'admin_booking_notification_email');
		send_email_notification($this, $email, 'Booking Successful', $data, 'user_booking_notification_email');

		return;
	}


	public function add_parcel($booking_id, $item_name, $category, $item_size, $notes = '')
	{
		$booking = $this->booking_read_model->get_booking_details_by_id($booking_id);
		if (!$booking || payment_status_normalize($booking->payment_status) !== 'completed') {
			return ['status' => false, 'msg' => 'Booking not found or not in a completed state.'];
		}

		$traveller = $this->traveller_read_model->get_traveller_details_by_id($booking->traveller_id);
		if (!$traveller) {
			return ['status' => false, 'msg' => 'Traveller not found.'];
		}

		if ($item_size > (float) $traveller->available_space) {
			return [
				'status' => false,
				'msg' => "Not enough space. Traveller only has {$traveller->available_space}KG available."
			];
		}

		$item_price = $this->calculate_item_price($category, $item_size, $booking->currency, $traveller);
		$new_item = [
			'item_name' => $item_name,
			'category'  => $category,
			'size'      => $item_size,
			'price'     => $item_price,
			'unit'      => booking_category_unit($category),
		];

		$current_items = json_decode($booking->items, true) ?: [];
		$current_items[] = $new_item;
		$new_items_json = json_encode($current_items);

		$old_total = (float) $booking->total_amount;
		$old_commission = (float) $booking->traveller_commission;
		$special_charge = booking_category_price_type($category) === 'special' ? 10.00 : 0.00;
		$new_total = round($old_total + $item_price + $special_charge, 2);
		$new_commission = round($old_commission + $this->get_category_commission_delta($category, $item_size, $traveller), 2);

		$this->db->trans_start();

		$this->db->where('id', $booking_id);
		$this->db->update('bookings', [
			'items'                => $new_items_json,
			'total_amount'         => $new_total,
			'selected_space'       => (float) $booking->selected_space + $item_size,
			'traveller_commission' => $new_commission,
		]);

		$this->travellers_model->update_traveller_space($booking->traveller_id);
		$this->log_parcel_change($booking_id, 'add', $new_item, $old_total, $new_total, $old_commission, $new_commission, $notes);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Transaction failed in ' . __METHOD__);
			return ['status' => false, 'msg' => 'Failed to update booking parcel.'];
		}

		$this->finance_read_model->clearFinanceSummaryCaches();
		$this->booking_read_model->clearBookingCountCaches();

		$this->notify_traveller_bag_update($booking, $traveller, 'add', $new_item, $new_total, $new_commission, $booking->currency);

		return [
			'status' => true,
			'msg' => 'Parcel added successfully. Traveller has been notified by email.',
			'new_total' => $new_total,
			'new_commission' => $new_commission,
		];
	}


	public function remove_parcel($booking_id, $item_index, $notes = '')
	{
		$booking = $this->booking_read_model->get_booking_details_by_id($booking_id);
		if (!$booking || payment_status_normalize($booking->payment_status) !== 'completed') {
			return ['status' => false, 'msg' => 'Booking not found or not in a completed state.'];
		}

		$traveller = $this->traveller_read_model->get_traveller_details_by_id($booking->traveller_id);
		$current_items = json_decode($booking->items, true) ?: [];

		if (!isset($current_items[$item_index])) {
			return ['status' => false, 'msg' => 'Item not found at the specified index.'];
		}

		if (count($current_items) <= 1) {
			return ['status' => false, 'msg' => 'Cannot remove the last item from a booking. Cancel the booking instead.'];
		}

		$removed_item = $current_items[$item_index];
		$removed_price = (float) ($removed_item['price'] ?? 0);
		$removed_size = (float) ($removed_item['size'] ?? 0);
		$removed_category = $removed_item['category'] ?? '';

		$old_total = (float) $booking->total_amount;
		$old_commission = (float) $booking->traveller_commission;
		$special_charge = booking_category_price_type($removed_category) === 'special' ? 10.00 : 0.00;
		$new_total = max(0, round($old_total - $removed_price - $special_charge, 2));
		$new_commission = max(0, round($old_commission - $this->get_category_commission_delta($removed_category, $removed_size, $traveller), 2));

		array_splice($current_items, $item_index, 1);
		$new_items_json = json_encode(array_values($current_items));
		$new_selected_space = max(0, (float) $booking->selected_space - $removed_size);

		$this->db->trans_start();

		$this->db->where('id', $booking_id);
		$this->db->update('bookings', [
			'items'                => $new_items_json,
			'total_amount'         => $new_total,
			'selected_space'       => $new_selected_space,
			'traveller_commission' => $new_commission,
		]);

		$this->travellers_model->update_traveller_space($booking->traveller_id);
		$this->log_parcel_change($booking_id, 'remove', $removed_item, $old_total, $new_total, $old_commission, $new_commission, $notes);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Transaction failed in ' . __METHOD__);
			return ['status' => false, 'msg' => 'Failed to update booking parcel.'];
		}

		$this->finance_read_model->clearFinanceSummaryCaches();
		$this->booking_read_model->clearBookingCountCaches();

		$this->notify_traveller_bag_update($booking, $traveller, 'remove', $removed_item, $new_total, $new_commission, $booking->currency);

		return [
			'status' => true,
			'msg' => 'Parcel removed successfully. Traveller has been notified by email.',
			'new_total' => $new_total,
			'new_commission' => $new_commission,
		];
	}


	private function calculate_item_price($category, $size, $currency, $traveller)
	{
		$route_pricing = booking_route_pricing($traveller->location, $traveller->destination);
		return round(booking_category_rate($route_pricing, $category) * $size, 2);
	}


	private function get_category_commission_delta($category, $size, $traveller = null)
	{
		if (!$traveller) {
			return 0.00;
		}

		$route_pricing = booking_route_pricing($traveller->location, $traveller->destination);
		return round(booking_category_payout_rate($route_pricing, $category) * (float) $size, 2);
	}


	private function log_parcel_change($booking_id, $action, $item, $old_total, $new_total, $old_commission, $new_commission, $notes = '')
	{
		$admin = $this->common_model->get_admin_details($this->session->admin_email);
		$this->db->insert('booking_item_logs', [
			'booking_id'     => $booking_id,
			'admin_id'       => $admin ? $admin->id : 0,
			'action'         => $action,
			'item_name'      => $item['item_name'] ?? '',
			'category'       => $item['category'] ?? '',
			'item_price'     => $item['price'] ?? 0,
			'item_size'      => $item['size'] ?? 0,
			'old_total'      => $old_total,
			'new_total'      => $new_total,
			'old_commission' => $old_commission,
			'new_commission' => $new_commission,
			'notes'          => $notes,
		]);
	}


	private function notify_traveller_bag_update($booking, $traveller, $action, $item, $new_total, $new_commission, $currency)
	{
		$symbol = currency_symbol_text($currency);
		$action_word = ($action === 'add') ? 'added to' : 'removed from';
		$email_data = [
			'traveller_name'  => $traveller->fullname,
			'action_word'     => $action_word,
			'item_name'       => $item['item_name'] ?? '',
			'item_category'   => $item['category'] ?? '',
			'item_size'       => ($item['size'] ?? 0) . 'KG',
			'tracking_id'     => $booking->tracking_id,
			'new_total'       => $symbol . number_format($new_total, 2),
			'new_commission'  => $symbol . number_format($new_commission, 2),
			'currency_symbol' => $symbol,
		];

		$traveller_email = $booking->traveller_email ?? ($traveller->email ?? null);
		if ($traveller_email) {
			send_email_notification($this, $traveller_email, 'Your Bag Has Been Updated - ' . $booking->tracking_id, $email_data, 'traveller_bag_update_email');
		}

		send_email_notification($this, $_ENV['ADMIN_NOTIFICATION_EMAIL'] ?? 'customers@sharemybag.co.uk', 'Bag Updated: ' . $booking->tracking_id, $email_data, 'admin_general_notification_email');
	}


	public function cancel_booking($id)
	{
		$booking = $this->booking_read_model->get_booking_details_by_id($id);
		if (!$booking) {
			return false;
		}

		$this->db->trans_start();

		$this->db->where('id', $id);
		$this->db->update('bookings', array(
			'payment_status' => payment_status_normalize('canceled'),
		));

		$this->travellers_model->update_traveller_space($booking->traveller_id);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Transaction failed in ' . __METHOD__);
			return false;
		}

		$this->finance_read_model->clearFinanceSummaryCaches();
		$this->booking_read_model->clearBookingCountCaches();

		return true;
	}


	public function update_new_status($id)
	{
		$data['new'] = 1;
		$this->db->where('id', $id);
		$this->db->update('bookings', $data);

		return;
	}


	public function delete_booking($id)
	{
		// Retrieve the selected_space value from the bookings table
		$y = $this->booking_read_model->get_booking_details_by_id($id);
		if (!$y) {
			return false;
		}
		$selected_space = $y->selected_space;

		$this->db->trans_start();

		// Soft delete the booking from the bookings table
		$this->softDelete($id);

		// Retrieve the traveller_id from the booking
		$traveller_id = $y->traveller_id;

		// Retrieve the current available space for the traveller from the travellers table
		$this->db->select('available_space');
		$this->db->where('id', $traveller_id);
		ci_where_not_deleted($this->db, 'travellers');
		$query = $this->db->get('travellers');
		$current_row = $query->row();
		$current_space = $current_row ? $current_row->available_space : NULL;

		if ($current_space !== NULL) {
			// Calculate the new available space
			$new_space = $current_space + $selected_space;

			// Update the available_space in the travellers table
			$data = array(
				'available_space' => $new_space,
			);
			$this->db->where('id', $traveller_id);
			ci_where_not_deleted($this->db, 'travellers');
			$this->db->update('travellers', $data);
		}

		// Delete rows from the shipping table with the same Tracking ID
		$this->db->delete('shipping', array('tracking_id' => $y->tracking_id));

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Transaction failed in ' . __METHOD__);
			return false;
		}

		$this->finance_read_model->clearFinanceSummaryCaches();
		$this->booking_read_model->clearBookingCountCaches();
		$this->shipping_read_model->clearShippingCountCaches();

		return true;
	}


	public function bulk_actions_booking($selected_rows)
	{
		$bulk_action_type = $this->input->post('bulk_action_type', TRUE);

		if (is_array($selected_rows)) {
			foreach ($selected_rows as $id) {
				switch ($bulk_action_type) {
				    case 'update_new_status':
						$this->update_new_status($id);
						break;
				    case 'confirm':
						$this->confirm_booking($id);
						break;
					case 'cancel':
						$this->cancel_booking($id);
						break;
					case 'delete':
						$this->delete_booking($id);
						break;
				}
			}
			$this->session->set_flashdata('status_msg', count($selected_rows) . " Bookings deleted.");
		}
	}
}
