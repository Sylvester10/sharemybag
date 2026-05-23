<?php
defined('BASEPATH') or exit('Direct access to script not allowed');


class Admin_user_model extends \CI_Model
{
	public $admin_details;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_read_model');
		$this->admin_details = $this->common_model->get_admin_details($this->session->email);
	}



	public function update_user($id)
	{
		$data['firstname'] = ucfirst($this->input->post('firstname', TRUE));
		$data['lastname'] = ucfirst($this->input->post('lastname', TRUE));
		$data['number'] = $this->input->post('number', TRUE);
		$data['email'] = $this->input->post('email', TRUE);
		$data['country'] = $this->input->post('country', TRUE);
		$data['address'] = $this->input->post('address', TRUE);
		$data['state'] = $this->input->post('state', TRUE);
		$data['post_code'] = $this->input->post('post_code', TRUE);

		$this->db->where('id', $id);
		$this->db->update('users', $data);
		$this->user_read_model->clearUserCountCaches();
		return true;
	}


	public function get_active_user($limit, $offset)
	{
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("date_registered", "DESC"); //order by date_unix ASC so that the dates appear chronologically
		$query = $this->db->where('active', TRUE);
		ci_where_not_deleted($this->db, 'users');
		$query = $this->db->get('users');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}


	public function get_user($limit, $offset)
	{
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("date_registered", "DESC"); //order by date_unix ASC so that the dates appear chronologically
		ci_where_not_deleted($this->db, 'users');
		$query = $this->db->get_where('users');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}


	public function count_active_user()
	{ //get all active users
		$query = $this->db->where('active', TRUE);
		ci_where_not_deleted($this->db, 'users');
		return $this->db->get_where('users')->num_rows();
	}


	public function count_user()
	{ //get all users
		ci_where_not_deleted($this->db, 'users');
		return $this->db->get_where('users')->num_rows();
	}


	public function message_user($id)
	{
		$subject = ucfirst($this->input->post('subject', TRUE));
		$message = nl2br(ucfirst($this->input->post('message', TRUE)));

		$data = array(
			'subject' => 'subject',
			'message' => 'message',
		);
		$y = $this->user_read_model->get_user_details_by_id($id);

		try {

			require_once 'application/third_party/mail.php';
			$y = $this->user_read_model->get_user_details_by_id($id);
			$mail->addAddress($y->email, $y->first_name);
			//$mail->AddEmbeddedImage('application/third_party/image/logo.png','logo','logo.png');
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
				<div id="container" style="width: 100%; height: auto; padding: 10px 10px 10px 10px; background-color:rgb(240, 240, 240); position: relative; border-radius:5px; ">

					<!--Logo-->
					<div id="icon_cont" style="position: relative; width: width: 92%; margin-top: 5px; margin-bottom: 5px;">
						<div id="icon" style="position: relative; width: 100px; margin:auto;">
							<img src="cid:logo" style="width:150px;" width="150">
						</div>
					</div>

					<div id="paymentContainer" style="max-width: 400px; height: auto; background-color: white; margin:auto; position: relative; padding:10px 0px 10px 0px; border-radius:20px; ">


						<!--Message -->
						<div id="msg" style="position: relative; width: 80%; margin:auto; text-align: left; color: rgb(90,90,90); margin-top: 10px;"><b style="color:rgb(40,40,40); font-size: 1.2em;">Hello ' . $y->last_name . ', </b>
						</div>

						<!--Body -->
						<div id="amount" style="position: relative; width: 80%; margin:auto; text-align: left; font-size: 13px; margin-top: 20px;">
							' . $message . '
						</div>

						<div id="amount" style="position: relative; text-align: center; font-size: 13px; margin-top: 20px; margin-bottom: 20px;">
							<a href="https://cryptomatrades.com/login" target="_blank" style="text-decoration: none; padding: 10px;background-color: #3d3dce; color: white; border-radius: 5px;">Login</a>
						</div>
					</div>

					<div id="fooooo" style="width: 100%; text-align: center; margin-top: 20px; color: #494949; margin-bottom: 10px; ">
						<a href="https://cryptomatrades.com/privacy_policy" target="_blank">Privacy Policy</a>
					</div>

					<div id="fooooo" style="width: 100%; text-align: center; margin-top: 20px; color: #494949; margin-bottom: 10px; ">
						<a href="https://cryptomatrades.com/terms_conditions" target="_blank">Terms and Conditions</a>
					</div>

					<div id="fooooo" style="width: 100%; text-align: center; margin-top: 20px; color: #494949; margin-bottom: 10px; ">
						<span>&copy;</span>2022 Cryptoma. All rights reserved.
					</div>

				</div>

			</body>
		</html>';

			// Content

			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = $body;


			$mail->send();
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}


	public function verify_user($id)
	{
		$data = array(
			'is_verified' => VERIFY_APPROVED,
		);
		if ($this->db->field_exists('verification_rejection_reason', 'users')) {
			$data['verification_rejection_reason'] = null;
		}
		if ($this->db->field_exists('verification_rejection_note', 'users')) {
			$data['verification_rejection_note'] = null;
		}
		if ($this->db->field_exists('verification_rejected_at', 'users')) {
			$data['verification_rejected_at'] = null;
		}
		if ($this->db->field_exists('verification_rejected_by', 'users')) {
			$data['verification_rejected_by'] = null;
		}
		$this->db->where('id', $id);
		$this->db->update('users', $data);
		$this->user_read_model->clearUserCountCaches();

		$y = $this->user_read_model->get_user_details_by_id($id);
		$email = $y->email;
		$data['firstname'] = $y->firstname;

		//Send email to user
		send_email_notification($this, $email, 'Identity Verification Successful', $data, 'user_document_verification_success_email');

		return;
	}


	public function unverify_user($id, $reason = null, $note = null)
	{
		$reason = trim((string) $reason);
		$note = trim((string) $note);
		if ($reason === '' || !array_key_exists($reason, verification_rejection_reason_options())) {
			$reason = 'other';
		}

		$data = array(
			'is_verified' => VERIFY_NONE,
		);
		if ($this->db->field_exists('verification_rejection_reason', 'users')) {
			$data['verification_rejection_reason'] = $reason;
		}
		if ($this->db->field_exists('verification_rejection_note', 'users')) {
			$data['verification_rejection_note'] = ($note !== '') ? $note : null;
		}
		if ($this->db->field_exists('verification_rejected_at', 'users')) {
			$data['verification_rejected_at'] = date('Y-m-d H:i:s');
		}
		if ($this->db->field_exists('verification_rejected_by', 'users')) {
			$data['verification_rejected_by'] = $this->admin_details->id ?? null;
		}
		$this->db->where('id', $id);
		$this->db->update('users', $data);
		$this->user_read_model->clearUserCountCaches();

		$y = $this->user_read_model->get_user_details_by_id($id);
		$email = $y->email;
		$data['firstname'] = $y->firstname;
		$data['rejection_reason'] = verification_rejection_reason_label($reason);
		$data['rejection_note'] = $note;

		//Send email to user
		send_email_notification($this, $email, 'Identity Verification Unsuccessful', $data, 'user_document_verification_failed_email');

		return;
	}


	public function block_user($id)
	{
		$data = array(
			'account_status' => 0,
		);
		$this->db->where('id', $id);
		$this->db->update('users', $data);

		return;
	}


	public function unblock_user($id)
	{
		$data = array(
			'account_status' => 1,
		);
		$this->db->where('id', $id);
		$this->db->update('users', $data);

		return;
	}


	public function delete_user_photo($id)
	{
		$y = $this->user_read_model->get_user_details_by_id($id);
		if ($y->photo != NULL && $y->photo_thumb != NULL) {
			unlink(FCPATH .  './assets/selfie/' . $y->selfie); //delete the photo
			unlink(FCPATH .  './assets/id_cards/' . $y->id_card); //delete the thumbnail
		}
	}


	public function delete_user($id)
	{
		$y = $this->user_read_model->get_user_details_by_id($id);
		if (!$y) {
			return false;
		}
		$this->delete_user_photo($id); //remove image files from server
		$this->db->where('id', $id);
		ci_where_not_deleted($this->db, 'users');
		$this->db->set('deleted_at', 'NOW()', false);
		$updated = $this->db->update('users');
		if ($updated) {
			$this->user_read_model->clearUserCountCaches();
		}
		return $updated;
	}


	public function bulk_actions_user($selected_rows)
	{
		$bulk_action_type = $this->input->post('bulk_action_type', TRUE);

		if (is_array($selected_rows)) {
			foreach ($selected_rows as $id) {
				switch ($bulk_action_type) {
					case 'verify':
						$this->verify_user($id);
						break;
					case 'unverify':
						$this->unverify_user($id);
						break;
					case 'block':
						$this->block_user($id);
						break;
					case 'unblock':
						$this->unblock_user($id);
						break;
					case 'delete':
						$this->delete_user($id);
						break;
				}
			}

			// Set the flash message using count of the selected rows
			switch ($bulk_action_type) {
				case 'verify':
					$action_message = 'user(s) verified successfully.';
					break;
				case 'unverify':
					$action_message = 'user(s) unverified successfully.';
					break;
				case 'block':
					$action_message = 'user(s) blocked successfully.';
					break;
				case 'unblock':
					$action_message = 'user(s) unblocked successfully.';
					break;
				case 'delete':
					$action_message = 'user(s) deleted successfully.';
					break;
				default:
					$action_message = 'action completed successfully.';
					break;
			}

			$this->session->set_flashdata('status_msg', count($selected_rows) . " " . $action_message);
		} else {
			$this->session->set_flashdata('status_msg_error', 'No users selected for bulk action.');
		}
	}
}
