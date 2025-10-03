<?php
defined('BASEPATH') or exit('Direct access to script not allowed');


class Travellers_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->table = 'travellers';
		$this->primary_cols = array('id');
	}




	public function add_traveller_to_db($serialized_images, $thumbnails)
	{
		$fullname = ucwords($this->input->post('fullname', TRUE));
		$c_code1 = $this->input->post('c_code1', TRUE);
		$number1 = $this->input->post('phone', TRUE);

		$phone = $c_code1 . "" . $number1;

		$c_code2 = $this->input->post('c_code2', TRUE);
		$number2 = $this->input->post('alt_phone', TRUE);

		$alt_phone = $c_code2 . "" . $number2;

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
		$status = 'Pending';

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

		return;
	}


	public function update_traveller($id)
	{
		$data['fullname'] = ucwords($this->input->post('fullname', TRUE));
		$data['phone'] = $this->input->post('phone', TRUE);
		$data['alt_phone'] = $this->input->post('alt_phone', TRUE);
		$data['email'] = $this->input->post('email', TRUE);
		$data['location'] = $this->input->post('location', TRUE);
		$data['current_state'] = $this->input->post('current_state', TRUE);
		$data['drop_address1'] = $this->input->post('drop_address1', TRUE);
		$data['drop_date1'] = $this->input->post('drop_date1', TRUE);
		$data['departure_state'] = $this->input->post('departure_state', TRUE);
		$data['arrival_airport'] = $this->input->post('arrival_airport', TRUE);
		$data['arrival_state'] = $this->input->post('arrival_state', TRUE);
		$data['drop_address2'] = $this->input->post('drop_address2', TRUE);
		$data['drop_date2'] = $this->input->post('drop_date2', TRUE);
		$data['destination'] = $this->input->post('destination', TRUE);
		$data['travel_date'] = $this->input->post('travel_date', TRUE);
		$data['arrival_date'] = $this->input->post('arrival_date', TRUE);
		$data['airline'] = $this->input->post('airline', TRUE);
		$data['address'] = ucfirst($this->input->post('address', TRUE));
		$data['available_space'] = $this->input->post('available_space', TRUE);
		$data['original_bag_space'] = $this->input->post('available_space', TRUE);
		$data['address'] = ucfirst($this->input->post('address', TRUE));
		// $data['unwanted_items'] = implode(", ", $this->input->post('unwanted_items', TRUE));
		$unwanted_items = $this->input->post('unwanted_items', TRUE);
		$data['unwanted_items'] = is_array($unwanted_items) ? implode(", ", $unwanted_items) : '';
		$data['status'] = 'Approved';
		$hash = getRandomName(134);
		$data['hash'] = $hash;

		//Send email to traveller
		$email = $this->input->post('email', TRUE);
		send_email_notification($this, $email, 'Approved', $data, 'traveller_approval_notification_email');

		$this->db->where('id', $id);
		$this->db->update('travellers', $data);
		return true;
	}



	public function recycle_traveller($id, $itinerary_photo, $thumbnail)
	{
		$data['fullname'] = ucwords($this->input->post('fullname', TRUE));
		$data['phone'] = $this->input->post('phone', TRUE);
		$data['alt_phone'] = $this->input->post('alt_phone', TRUE);
		$data['email'] = $this->input->post('email', TRUE);
		$data['location'] = $this->input->post('location', TRUE);
		$data['current_state'] = $this->input->post('current_state', TRUE);
		$data['drop_address1'] = $this->input->post('drop_address1', TRUE);
		$data['drop_date1'] = $this->input->post('drop_date1', TRUE);
		$data['departure_state'] = $this->input->post('departure_state', TRUE);
		$data['arrival_airport'] = $this->input->post('arrival_airport', TRUE);
		$data['arrival_state'] = $this->input->post('arrival_state', TRUE);
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
		$data['status'] = 'Approved';
		$data['hash'] = getRandomName(134);

		$email = $this->input->post('email', TRUE);
		send_email_notification($this, $email, 'Approved', $data, 'traveller_approval_notification_email');

		return $this->db->insert('travellers', $data);
	}


	function update_traveller_space($id)
	{
		$traveller = $this->common_model->get_traveller_details_by_id($id);
		if (!$traveller) {
			return false;
		}
		$this->db->select_sum('selected_space');
		$query = $this->db->get_where('bookings', array('traveller_id' => $id, 'payment_status' => 'completed'));
		if ($query->num_rows()) {
			$selected_space = $query->row()->selected_space;
			$data['used_space'] = $selected_space;
			$data['available_space'] = $traveller->original_bag_space - $data['used_space'];
			$this->db->where('id', $traveller->id);
			return $this->db->update('travellers', $data);
		}
		return false;
	}


	public function get_approved_travellers($limit, $offset)
	{
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("travel_date", "asc"); //order by date_unix ASC so that the dates appear chronologically
		$query = $this->db->where('status', 'Approved');
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
		$query = $this->db->where('status', 'Approved');
		return $this->db->get_where('travellers')->num_rows();
	}


	public function count_pending_travellers()
	{ //count all pending traveller
		$query = $this->db->where('status', 'Pending');
		return $this->db->get_where('travellers')->num_rows();
	}


	public function count_unapproved_travellers()
	{ //count all unapproved traveller
		$query = $this->db->where('status', 'Unapproved');
		return $this->db->get_where('travellers')->num_rows();
	}


	public function count_travellers()
	{ //count all traveller
		return $this->db->get_where('travellers')->num_rows();
	}


	public function lock_traveller_bag($id)
	{
		$data = [
			'available_space' => 0, // force bag full
			'bag_locked' => 1       // lock it
		];
		$this->db->where('id', $id);
		return $this->db->update('travellers', $data);
	}


	public function unlock_traveller_bag($id)
	{
		$traveller = $this->common_model->get_traveller_details_by_id($id);
		if (!$traveller) return false;

		$data = [
			'bag_locked' => 0,
			'available_space' => $traveller->original_bag_space - $traveller->used_space
		];

		$this->db->where('id', $id);
		return $this->db->update('travellers', $data);
	}


	public function approve_traveller($id)
	{
		$data = array(
			'status' => 'Approved',
		);
		$this->db->where('id', $id);
		return $this->db->update('travellers', $data);
	}


	public function unapprove_traveller($id)
	{
		$data = array(
			'status' => 'Unapproved',
		);
		$this->db->where('id', $id);
		return $this->db->update('travellers', $data);
	}


	public function delete_traveller($id)
	{
		$y = $this->common_model->get_traveller_details_by_id($id);

		// Delete the traveller from the bookings table
		$this->db->delete('travellers', array('id' => $id));

		// Delete the all bookings that shares the same traveller id from the bookings table
		$this->db->delete('bookings', array('traveller_id' => $id));

		// Delete the all shipping that shares the same traveller id from the bookings table
		$this->db->delete('shipping', array('tracking_id' => $id));
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
			$action_message = match ($bulk_action_type) {
				'approve' => 'Traveller(s) approved successfully.',
				'unapprove' => 'Traveller(s) unapproved successfully.',
				'delete' => 'Traveller(s) deleted successfully.',
				default => 'action completed successfully.'
			};

			$this->session->set_flashdata('status_msg', count($selected_rows) . " " . $action_message);
		} else {
			$this->session->set_flashdata('status_msg_error', 'No Traveller selected for bulk action.');
		}
	}
}
