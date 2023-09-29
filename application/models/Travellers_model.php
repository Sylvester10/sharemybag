<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation ===== 
Name: Traveller
Role: Model
Description: Controls the DB processes of Travellers_model from admin panel
Controller: Travellers
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Travellers_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}




	public function add_traveller_to_db()
	{
		$name = ucwords($this->input->post('name', TRUE));
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
		//$destination_address = $this->input->post('destination_address', TRUE);
		$airline = $this->input->post('airline', TRUE);
		$address = ucfirst($this->input->post('address', TRUE));
		$available_space = $this->input->post('available_space', TRUE);
		$unwanted_items = $this->input->post('unwanted_items', TRUE);
		if (is_array($unwanted_items)) {
			// If $unwanted_items is an array, implode it with ', ' as separator
			$unwanted_items = ucfirst(implode(', ', $unwanted_items));
		} else {
			// If $unwanted_items is not an array, set it to an empty string
			$unwanted_items = '';
		}
		$status = 'Pending';

		$data = array(
			'name' => $name,
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
			//'destination_address' => $destination_address,
			'airline' => $airline,
			'address' => $address,
			'available_space' => $available_space,
			'unwanted_items' => $unwanted_items,
			'status' => $status,
		);

		//Send email to Admin
		try {

			require_once 'application/third_party/mail.php';
			$mail->addAddress('admin@sharemybag.uk');
			//$mail->AddEmbeddedImage('application/third_party/image/check.png', 'check', 'check.png');
			$mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
			$body = '<!DOCTYPE html>
				<html>
					<head>
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<style type="text/css">
							*{

								box-sizing: border-box;
								padding:0;
								margin: 0;
								font-family: "Montserrat", sans-serif;
								font-size: 13px;

						}
						</style>
					</head>
					<body>
						<!--styling the container-->
						<div id="container" style="width: 100%; height: auto; padding: 10px 10px 10px 10px; background-color: white; position: relative; border-radius:5px; ">

							<!--Message -->
							<div id="amount" style="position: relative; width: 80%; margin:auto; text-align: left; font-size: 13px; margin-top: 20px; margin-bottom: 20px;">
								Hi, 

								<br><br> 

								You have a new traveler sign up.

								<div id="amount" style="position: relative; font-size: 13px; margin-top: 50px; margin-bottom: 20px;">
									<a href="https://sharemybag.uk/admin" target="_blank" style="text-decoration: none; padding: 10px;background-color: #3d3dce; color: white; border-radius: 5px;">Login</a>
								</div>
							</div>

							<!-- Footer -->
							<div id="fooooo" style="background-color:rgb(240, 240, 240); width: 100%; text-align: center; color: #494949; padding: 20px; ">
								<!--Logo-->
								<div id="icon" style="position: center; margin-bottom: 10px">
									<img src="cid:logo" style="width:100px;">
								</div>
								<span>&copy;</span>2023 ShareMyBag. All rights reserved.
							</div>

						</div>

					</body>
				</html>';

			// Content

			$mail->isHTML(true); // Set email format to HTML
			$mail->Subject = "New Traveler SignUp";
			$mail->Body = $body;
			$mail->AltBody = $body;


			$mail->send();
		} catch (Exception $e) {
		}

		$this->db->insert('travellers', $data);

		return;
	}


	public function update_traveller($id)
	{
		$name = ucwords($this->input->post('name', TRUE));
		$phone = $this->input->post('phone', TRUE);
		$alt_phone = $this->input->post('alt_phone', TRUE);
		$email = $this->input->post('email', TRUE);
		$location = $this->input->post('location', TRUE);
		$current_state = $this->input->post('current_state', TRUE);
		$drop_address1 = $this->input->post('drop_address1', TRUE);
		$drop_date1 = $this->input->post('drop_date1', TRUE);
		$departure_state = $this->input->post('departure_state', TRUE);
		$drop_address2 = $this->input->post('drop_address2', TRUE);
		$drop_date2 = $this->input->post('drop_date2', TRUE);
		$destination = $this->input->post('destination', TRUE);
		$travel_date = $this->input->post('travel_date', TRUE);
		$arrival_date = $this->input->post('arrival_date', TRUE);
		$airline = $this->input->post('airline', TRUE);
		$address = ucfirst($this->input->post('address', TRUE));
		$available_space = $this->input->post('available_space', TRUE);
		$address = ucfirst($this->input->post('address', TRUE));
		$unwanted_items = implode(", ", $this->input->post('unwanted_items', TRUE));
		$status = 'Approved';

		$data = array(
			'name' => $name,
			'phone' => $phone,
			'alt_phone' => $alt_phone,
			'email' => $email,
			'location' => $location,
			'current_state' => $current_state,
			'drop_address1' => $drop_address1,
			'drop_date1' => $drop_date1,
			'departure_state' => $departure_state,
			'drop_address2' => $drop_address2,
			'drop_date2' => $drop_date2,
			'destination' => $destination,
			'travel_date' => $travel_date,
			'arrival_date' => $arrival_date,
			'airline' => $airline,
			'address' => $address,
			'original_bag_space' => $available_space,
			'available_space' => $available_space,
			'unwanted_items' => $unwanted_items,
			'status' => $status,
		);

		//Send email to traveler
		try {

			require_once 'application/third_party/mail.php';
			$mail->addAddress($email);
			//$mail->AddEmbeddedImage('application/third_party/image/check.png', 'check', 'check.png');
			$mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
			$body = '<!DOCTYPE html>
			<html>
				<head>
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<style type="text/css">
						*{

							box-sizing: border-box;
							padding:0;
							margin: 0;
							font-family: "Montserrat", sans-serif;
							font-size: 13px;

					}
					</style>
				</head>
				<body>
					<!--styling the container-->
					<div id="container" style="width: 100%; height: auto; padding: 10px 10px 10px 10px; background-color: white; position: relative; border-radius:5px; ">

						<!--Message -->
						<div id="amount" style="position: relative; width: 80%; margin:auto; text-align: left; font-size: 13px; margin-top: 20px; margin-bottom: 20px;">
							Hi, <br><br> You have been approved as one of our travelers. You will be contacted as soon as we have a sender ready. 
							<br><br>
							Thanks for working with us.
						</div>

						<!-- Footer -->
						<div id="fooooo" style="background-color:rgb(240, 240, 240); width: 100%; text-align: center; color: #494949; padding: 20px; ">
							<!--Logo-->
							<div id="icon" style="position: center; margin-bottom: 10px">
								<img src="cid:logo" style="width:100px;">
							</div>
							<span>&copy;</span>2023 ShareMyBag. All rights reserved.
						</div>

					</div>

				</body>
			</html>';

			// Content

			$mail->isHTML(true); // Set email format to HTML
			$mail->Subject = "Approved";
			$mail->Body = $body;
			$mail->AltBody = $body;


			$mail->send();
		} catch (Exception $e) {
		}

		$this->db->where('id', $id);
		if ($this->db->update('travellers', $data)) {
			$this->update_traveller_space($id);
			return true;
		}
		return false;

	}


	function update_traveller_space($id)
	{
		$traveller = $this->common_model->get_traveller_details_by_id($id);
		$this->db->select_sum('selected_space');
		$query = $this->db->get_where('bookings', array('traveller_id' => $id));
		if ($query->num_rows()) {
			$selected_space = $query->row()->selected_space;
			$data['used_space'] = $selected_space;
			$data['available_space'] = $traveller->original_bag_space - $selected_space;
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


	public function delete_booking_id_photo($id)
	{
		$y = $this->common_model->get_booking_details_by_traveller_id($id);
		$file_path = './assets/uploads/' . $y->id_photo;
		if (!empty($y->id_photo) && file_exists($file_path)) { // Delete the id photo file
			unlink($file_path);
		}
	}

	public function delete_booking_selfie($id)
	{
		$y = $this->common_model->get_booking_details_by_traveller_id($id);
		$file_path = './assets/selfie/' . $y->selfie;
		if (!empty($y->selfie) && file_exists($file_path)) { // Delete the id photo file
			unlink($file_path);
		}
	}


	public function delete_traveller($id)
	{
		$y = $this->common_model->get_traveller_details_by_id($id);

		// Delete the traveler from the bookings table
		$this->db->delete('travellers', array('id' => $id));

		// Delete the all bookings that shares the same traveller id from the bookings table
		$this->db->delete('bookings', array('traveller_id' => $id));

		// remove image files from server
		$this->delete_booking_id_photo($id);
		$this->delete_booking_selfie($id);
		
		// Delete the all shipping that shares the same traveller id from the bookings table
		$this->db->delete('shipping', array('tracking_id' => $id));
		return;
	}


	public function bulk_actions_traveller()
	{
		$selected_rows = count($this->input->post('check_bulk_action', TRUE));
		$bulk_action_type = $this->input->post('bulk_action_type', TRUE);
		$row_id = $this->input->post('check_bulk_action', TRUE);
		foreach ($row_id as $id) {
			switch ($bulk_action_type) {
				case 'approve':
					$this->approve_traveller($id);
					$this->session->set_flashdata('status_msg', "{$selected_rows} Traveller(s) Approved.");
					break;
				case 'unapprove':
					$this->unapprove_traveller($id);
					$this->session->set_flashdata('status_msg', "{$selected_rows} Traveller(s) Unapproved.");
					break;
				case 'delete':
					$this->delete_traveller($id);
					$this->session->set_flashdata('status_msg', "{$selected_rows} Traveller(s) deleted.");
					break;
			}
		}
	}



}