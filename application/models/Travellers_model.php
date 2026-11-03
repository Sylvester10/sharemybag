<?php
defined('BASEPATH') or exit('Direct access to script not allowed');
require_once APPPATH . 'helpers/booking_capacity_helper.php';


class Travellers_model extends \MY_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->table = 'travellers';
		$this->primary_cols = array('id');
		$this->load->model('traveller_read_model');
		$this->load->model('shipping_read_model');
	}




	public function add_traveller_to_db($serialized_images, $thumbnails)
	{
		$fullname = ucwords($this->input->post('fullname', TRUE));
		$c_code1 = $this->input->post('c_code1', TRUE);
		$number1 = $this->input->post('phone', TRUE);

		$phone = normalize_phone_number($c_code1, $number1);

		$c_code2 = $this->input->post('c_code2', TRUE);
		$number2 = $this->input->post('alt_phone', TRUE);

		$alt_phone = normalize_phone_number($c_code2, $number2);

		$email = $this->input->post('email', TRUE);
		$travel_date = $this->input->post('travel_date', TRUE);
		$arrival_date = $this->input->post('arrival_date', TRUE);
		$location = $this->input->post('location', TRUE);
		$current_state = $this->input->post('current_state', TRUE);
		$drop_address1 = $this->input->post('drop_address1', TRUE);
		$drop_date1 = $this->input->post('drop_date1', TRUE);
		$departure_state = $this->input->post('departure_state', TRUE);
		$drop_address2 = $this->input->post('drop_address2', TRUE);
		$drop_date2 = $this->input->post('drop_date2', TRUE);
		$destination = $this->input->post('destination', TRUE);
		// $payment_type = $this->input->post('payment_type', TRUE);
		$airline = $this->input->post('airline', TRUE);
		$address = ucfirst($this->input->post('address', TRUE));
		$available_space = $this->input->post('available_space', TRUE);
		$unwanted_items = $this->input->post('unwanted_items', TRUE);
		$referred_by = strtolower($this->input->post('referred_by', TRUE));

		if (is_array($unwanted_items)) {
			// If $unwanted_items is an array, implode it with ', ' as separator
			$unwanted_items = ucfirst(implode(', ', $unwanted_items));
		} else {
			// If $unwanted_items is not an array, set it to an empty string
			$unwanted_items = '';
		}
		$status = traveller_status_normalize('Pending');

		$data = array(
			'fullname' => $fullname,
			'phone' => $phone,
			'alt_phone' => $alt_phone,
			'email' => $email,
			'travel_date' => $travel_date,
			'arrival_date' => $arrival_date,
			'location' => $location,
			'current_state' => $current_state,
			'drop_address1' => $drop_address1,
			'drop_date1' => $drop_date1,
			'departure_state' => $departure_state,
			'drop_address2' => $drop_address2,
			'drop_date2' => $drop_date2,
			'destination' => $destination,
			// 			'payment_type' => $payment_type,
			'airline' => $airline,
			'address' => $address,
			'available_space' => $available_space,
			'original_bag_space' => $available_space,
			'unwanted_items' => $unwanted_items,
			'status' => $status,
			'itinerary_photo' => $serialized_images, // Store serialized array of image filenames
			'itinerary_photo_thumb' => $thumbnails, // Store serialized array of image filenames
			'referred_by' => $referred_by,
		);


		//Send email to Admin
		$data['fullname'] = $fullname;
		send_email_notification($this, 'admin@sharemybag.co.uk', 'New Traveller Alert', $data, 'admin_new_traveller_notification_email');

		$this->db->insert('travellers', $data);
		$this->traveller_read_model->clearTravellerCountCaches();
		$this->shipping_read_model->clearShippingCountCaches();

		return;
	}


	public function update_traveller($id)
	{
		$existing_traveller = $this->traveller_read_model->get_traveller_details_by_id($id);
		$was_pending_traveller = $existing_traveller && traveller_status_normalize($existing_traveller->status) === 'Pending';

		$data['fullname'] = ucwords($this->input->post('fullname', TRUE));
		$data['phone'] = normalize_phone_number($this->input->post('c_code1', TRUE), $this->input->post('phone', TRUE));
		$data['alt_phone'] = normalize_phone_number($this->input->post('c_code2', TRUE), $this->input->post('alt_phone', TRUE));
		$data['email'] = $this->input->post('email', TRUE);
		$data['location'] = $this->input->post('location', TRUE);
		$data['current_state'] = $this->input->post('current_state', TRUE);
		$data['drop_address1'] = $this->input->post('drop_address1', TRUE);
		$data['drop_date1'] = $this->input->post('drop_date1', TRUE);
		$data['departure_state'] = $this->input->post('departure_state', TRUE);
		$data['arrival_airport'] = $this->input->post('arrival_airport', TRUE);
		$data['arrival_state'] = $this->input->post('arrival_state', TRUE);
		$data['destination_area'] = ucfirst($this->input->post('destination_area', TRUE));
		$data['drop_address2'] = $this->input->post('drop_address2', TRUE);
		$data['drop_date2'] = $this->input->post('drop_date2', TRUE);
		$data['destination'] = $this->input->post('destination', TRUE);
		$data['travel_date'] = $this->input->post('travel_date', TRUE);
		$data['arrival_date'] = $this->input->post('arrival_date', TRUE);
		$data['airline'] = $this->input->post('airline', TRUE);
		$data['address'] = ucfirst($this->input->post('address', TRUE));
		$data['available_space'] = $this->input->post('available_space', TRUE);
		$data['original_bag_space'] = $this->input->post('available_space', TRUE);
		$data['area'] = ucfirst($this->input->post('area', TRUE));
		$data['additional_info'] = $this->input->post('additional_info', TRUE);
		$unwanted_items = $this->input->post('unwanted_items', TRUE);
		$data['unwanted_items'] = is_array($unwanted_items) ? implode(", ", $unwanted_items) : '';
		$data['status'] = traveller_status_normalize('Approved');
		$hash = getRandomName(134);
		$data['hash'] = $hash;

		if (!$this->db->trans_begin()) { return false; }
		$locked = booking_lock_traveller($this->db, $id);
		if (!$locked) { $this->db->trans_rollback(); return false; }
		$current_capacity = booking_capacity_snapshot($this->db, $locked);
		if (!$current_capacity) { $this->db->trans_rollback(); return false; }
		foreach (['location','destination','travel_date'] as $field) {
			if ($current_capacity['held'] > 0 && (string) $locked->$field !== (string) $data[$field]) {
				$this->db->trans_rollback(); return false;
			}
		}
		$locked->original_bag_space = (float) $data['original_bag_space'];
		$capacity = booking_capacity_snapshot($this->db, $locked);
		if (!$capacity || $capacity['overcommitted']) { $this->db->trans_rollback(); return false; }
		$data['used_space'] = $capacity['used'];
		$data['available_space'] = $capacity['available'];
		$updated = $this->db->where('id', $id)->update('travellers', $data);
		if (!$updated || !$this->db->trans_status() || !$this->db->trans_commit()) {
			$this->db->trans_rollback(); return false;
		}

		if ($updated) {
			$this->traveller_read_model->clearTravellerCountCaches();
			if ($was_pending_traveller) {
				send_email_notification($this, $data['email'], 'Approved', $data, 'traveller_approval_notification_email');
			}
			return true;
		}

		return false;
	}


	public function add_traveller_bag_space($id, $space_to_add)
	{
		return $this->change_capacity($id, max(0, (float) $space_to_add));
	}

	public function remove_traveller_bag_space($id, $space_to_remove)
	{
		return $this->change_capacity($id, -max(0, (float) $space_to_remove));
	}

	private function change_capacity($id, $delta)
	{
		if (!$this->db->trans_begin()) { return false; }
		$traveller = booking_lock_traveller($this->db, $id);
		if (!$traveller) { $this->db->trans_rollback(); return false; }
		$traveller->original_bag_space = round((float) $traveller->original_bag_space + $delta, 3);
		$capacity = booking_capacity_snapshot($this->db, $traveller);
		if (!$capacity || $capacity['overcommitted'] || $traveller->original_bag_space < 0) {
			$this->db->trans_rollback(); return false;
		}
		$updated = $this->db->where('id', (int) $id)->update('travellers', [
			'original_bag_space' => $traveller->original_bag_space,
			'used_space' => $capacity['used'], 'available_space' => $capacity['available']]);
		if (!$updated || !$this->db->trans_status() || !$this->db->trans_commit()) {
			$this->db->trans_rollback(); return false;
		}
		$this->traveller_read_model->clearTravellerCountCaches();
		return true;
	}


	public function recycle_traveller($id, $itinerary_photo, $thumbnail)
	{
		$data['fullname'] = ucwords($this->input->post('fullname', TRUE));
		$data['phone'] = normalize_phone_number($this->input->post('c_code1', TRUE), $this->input->post('phone', TRUE));
		$data['alt_phone'] = normalize_phone_number($this->input->post('c_code2', TRUE), $this->input->post('alt_phone', TRUE));
		$data['email'] = $this->input->post('email', TRUE);
		$data['location'] = $this->input->post('location', TRUE);
		$data['current_state'] = $this->input->post('current_state', TRUE);
		$data['drop_address1'] = $this->input->post('drop_address1', TRUE);
		$data['drop_date1'] = $this->input->post('drop_date1', TRUE);
		$data['departure_state'] = $this->input->post('departure_state', TRUE);
		$data['arrival_airport'] = $this->input->post('arrival_airport', TRUE);
		$data['arrival_state'] = $this->input->post('arrival_state', TRUE);
		$data['destination_area'] = ucfirst($this->input->post('destination_area', TRUE));
		$data['drop_address2'] = $this->input->post('drop_address2', TRUE);
		$data['drop_date2'] = $this->input->post('drop_date2', TRUE);
		$data['destination'] = $this->input->post('destination', TRUE);
		$data['travel_date'] = $this->input->post('travel_date', TRUE);
		$data['arrival_date'] = $this->input->post('arrival_date', TRUE);
		$data['airline'] = $this->input->post('airline', TRUE);
		$data['address'] = ucfirst($this->input->post('address', TRUE));
		$data['available_space'] = $this->input->post('available_space', TRUE);
		$data['original_bag_space'] = $this->input->post('available_space', TRUE);
		$unwanted_items_post = $this->input->post('unwanted_items', TRUE);
		$data['unwanted_items'] = is_array($unwanted_items_post) ? implode(", ", $unwanted_items_post) : '';
		$data['itinerary_photo'] = $itinerary_photo;
		$data['itinerary_photo'] = $itinerary_photo;
		$data['itinerary_photo_thumb'] = $thumbnail;
		$data['status'] = traveller_status_normalize('Approved');
		$data['hash'] = getRandomName(134);

		$email = $this->input->post('email', TRUE);
		send_email_notification($this, $email, 'Approved', $data, 'traveller_approval_notification_email');

		$inserted = $this->db->insert('travellers', $data);
		if ($inserted) {
			$this->traveller_read_model->clearTravellerCountCaches();
		}
		return $inserted;
	}


	function update_traveller_space($id, $return_snapshot = false)
	{
		if (!$this->db->trans_begin()) { return false; }
		$traveller = booking_lock_traveller($this->db, $id);
		$capacity = $traveller ? booking_capacity_snapshot($this->db, $traveller) : false;
		if (!$capacity || $capacity['overcommitted']) { $this->db->trans_rollback(); return false; }
		$updated = $this->db->where('id', (int) $id)->update('travellers', [
			'used_space' => $capacity['used'], 'available_space' => $capacity['available']]);
		if (!$updated || !$this->db->trans_status() || !$this->db->trans_commit()) {
			$this->db->trans_rollback(); return false;
		}
		$traveller->used_space = $capacity['used'];
		$traveller->available_space = $capacity['available'];
		return $return_snapshot ? $traveller : true;
	}


	public function get_approved_travellers($limit, $offset)
	{
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("travel_date", "asc"); //order by date_unix ASC so that the dates appear chronologically
		$query = $this->db->where('status', 'Approved');
		$this->applyNotDeleted();
		$query = $this->db->get('travellers');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}


	public function get_traveller($limit, $offset)
	{
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("travel_date", "DESC"); //order by date_unix ASC so that the dates appear chronologically
		$this->applyNotDeleted();
		$query = $this->db->get_where('travellers');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}


	public function count_approved_travellers()
	{ //count all approved travellers
		return $this->traveller_read_model->getTravellerStatusCountSummary()->approved_travellers;
	}


	public function count_pending_travellers()
	{ //count all pending traveller
		return $this->traveller_read_model->getTravellerStatusCountSummary()->pending_travellers;
	}


	public function count_unapproved_travellers()
	{ //count all unapproved traveller
		return $this->traveller_read_model->getTravellerStatusCountSummary()->unapproved_travellers;
	}


	public function count_travellers()
	{ //count all traveller
		$this->applyNotDeleted();
		return $this->db->get_where('travellers')->num_rows();
	}


	public function lock_traveller_bag($id)
	{
		$data = [
			'available_space' => 0, // force bag full
			'bag_locked' => 1       // lock it
		];
		$this->db->where('id', $id);
		$updated = $this->db->update('travellers', $data);
		if ($updated) {
			$this->traveller_read_model->clearTravellerCountCaches();
		}
		return $updated;
	}


	public function unlock_traveller_bag($id)
	{
		if (!$this->db->trans_begin()) { return false; }
		$traveller = booking_lock_traveller($this->db, $id);
		if (!$traveller) { $this->db->trans_rollback(); return false; }
		$traveller->bag_locked = 0;
		$capacity = booking_capacity_snapshot($this->db, $traveller);
		if (!$capacity || $capacity['overcommitted'] || !$this->db->where('id', (int) $id)->update('travellers',
			['bag_locked' => 0, 'used_space' => $capacity['used'], 'available_space' => $capacity['available']])
			|| !$this->db->trans_status() || !$this->db->trans_commit()) {
			$this->db->trans_rollback(); return false;
		}
		$this->traveller_read_model->clearTravellerCountCaches();
		return true;
	}


	public function approve_traveller($id)
	{
		$existing_traveller = $this->traveller_read_model->get_traveller_details_by_id($id);
		if (!$existing_traveller) {
			return false;
		}
		$was_pending_traveller = traveller_status_normalize($existing_traveller->status) === 'Pending';
		$data = array(
			'status' => traveller_status_normalize('Approved'),
		);
		$this->db->where('id', $id);
		$updated = $this->db->update('travellers', $data);
		if ($updated) {
			$this->traveller_read_model->clearTravellerCountCaches();
			if ($was_pending_traveller) {
				send_email_notification($this, $existing_traveller->email, 'Approved', array(
					'fullname' => $existing_traveller->fullname,
				), 'traveller_approval_notification_email');
			}
		}
		return $updated;
	}


	public function unapprove_traveller($id)
	{
		$data = array(
			'status' => traveller_status_normalize('Unapproved'),
		);
		$this->db->where('id', $id);
		return $this->db->update('travellers', $data);
	}


	public function delete_traveller($id)
	{
		$y = $this->traveller_read_model->get_traveller_details_by_id($id);
		if (!$y) {
			return false;
		}

		// Collect booking tracking IDs BEFORE soft deleting bookings (DB-013 fix)
		$booking_rows = $this->db
			->select('tracking_id')
			->where('traveller_id', $id)
			->where('deleted_at IS NULL', null, false)
			->get('bookings')
			->result_array();
		$tracking_ids = array_column($booking_rows, 'tracking_id');

		$this->db->trans_start();

		// Soft delete the traveller
		$this->softDelete($id);

		// Soft delete all bookings for this traveller
		$this->db->where('traveller_id', $id);
		$this->db->where('deleted_at IS NULL', null, false);
		$this->db->set('deleted_at', 'NOW()', false);
		$this->db->update('bookings');

		// Delete shipping records by their actual tracking IDs
		if (!empty($tracking_ids)) {
			$this->db->where_in('tracking_id', $tracking_ids);
			$this->db->delete('shipping');
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Transaction failed in ' . __METHOD__);
			return false;
		}

		$this->traveller_read_model->clearTravellerCountCaches();

		return;
	}


	public function bulk_actions_traveller($selected_rows)
	{
		$bulk_action_type = $this->input->post('bulk_action_type', TRUE);

		if (is_array($selected_rows)) {
			foreach ($selected_rows as $id) {
				switch ($bulk_action_type) {
					case 'approve':
						$this->approve_traveller($id);
						break;
					case 'unapprove':
						$this->unapprove_traveller($id);
						break;
					case 'delete':
						$this->delete_traveller($id);
						break;
				}
			}

			// Set the flash message using count of the selected rows
			switch ($bulk_action_type) {
				case 'approve':
					$action_message = 'Traveller(s) approved successfully.';
					break;
				case 'unapprove':
					$action_message = 'Traveller(s) unapproved successfully.';
					break;
				case 'delete':
					$action_message = 'Traveller(s) deleted successfully.';
					break;
				default:
					$action_message = 'action completed successfully.';
					break;
			}

			$this->session->set_flashdata('status_msg', count($selected_rows) . " " . $action_message);
		} else {
			$this->session->set_flashdata('status_msg_error', 'No Traveller selected for bulk action.');
		}
	}
}
