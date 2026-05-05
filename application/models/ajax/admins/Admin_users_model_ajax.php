<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Admin_users_model_ajax extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin_user_model');
		$this->load->model('user_read_model');
	}

	var $table = 'users';
	var $column_order = array(null, 'selfie', 'id_card', 'firstname', 'number', 'email', 'country', 'account_status', 'last_login', 'date_registered'); //set column field database for datatable orderable
	var $column_search = array('selfie', 'id_card', 'firstname', 'number', 'email', 'country', 'account_status', 'last_login', 'date_registered'); //set column field database for datatable searchable
	var $order = array('date_registered' => 'DESC');


	private function the_query()
	{
		$search_value = datatable_search_value();
		$this->db->from($this->table);
		ci_where_not_deleted($this->db, $this->table);
		$i = 0;
		foreach ($this->column_search as $item) // loop column 
		{
			if ($search_value !== '') // if datatable send POST for search
			{
				if ($i === 0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $search_value);
				} else {
					$this->db->or_like($item, $search_value);
				}
				if (count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		if (isset($_POST['order'])) { // here order processing
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}


	function get_records()
	{
		$this->the_query();
		if ($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}


	function count_filtered_records()
	{
		$this->the_query();
		$query = $this->db->get();
		return $query->num_rows();
	}


	public function count_all_records()
	{
		$this->db->from($this->table);
		ci_where_not_deleted($this->db, $this->table);
		return $this->db->count_all_results();
	}


	public function actions($user)
	{
		$y = is_object($user) ? $user : $this->user_read_model->get_user_details_by_id($user);
		$id = $y->id;

		if ($y->account_status == 1) {
			$block_action = '<p><a type="button" href="' . base_url('admin_users/block_user/' . $y->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-times" style="color: red"></i> &nbsp; Block User </a></p>';
		} else {
			$block_action = '<p><a type="button" href="' . base_url('admin_users/unblock_user/' . $y->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-check" style="color: green"></i> &nbsp; Unblock User </a></p>';
		};

		if ($y->is_verified == VERIFY_NONE) {

			$verify_action = null;

		} elseif ($y->is_verified == VERIFY_PENDING) {

			$verify_action = '<p><a type="button" href="' . base_url('admin_users/verify_user/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-check" style="color: green"></i> &nbsp; Verify Account </a></p>

			' . $this->unverify_action_button($id);

		} elseif ($y->is_verified == VERIFY_APPROVED) {

			$verify_action = $this->unverify_action_button($id);

		}

		return '<p><a type="button" href="' . base_url('admin_users/user_login_admin/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable" target="_blank"> <i class="las la-sign-in-alt text-success"></i> Login as Super User </a></p>

		'. $verify_action . '

		<p><a type="button" href="' . base_url('admin_users/user_profile/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-user" style="color: green"></i> &nbsp; View Profile </a></p>

		' . $block_action . '

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $id . '"> <i class="las la-trash" style="color: red"></i> &nbsp; Delete </a></p>';
	}


	public function options($id)
	{

		return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $id . '" title="Options"> <i class="las la-bars"></i> </a></div>';
	}


	public function modal_options($user)
	{
		$y = is_object($user) ? $user : $this->user_read_model->get_user_details_by_id($user);
		$id = $y->id;
		return '<div class="modal fade" id="options' . $id . '" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content modal-width">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Actions: ' . $y->firstname . '</h4>
					</div><!--/.modal-header-->
					<div class="modal-body">'
			. $this->actions($y) .
			'</div>
				</div>
			</div>
		</div>';
	}


	public function message_admin_form($id)
	{
		$y = $this->user_read_model->get_user_details_by_id($id);
		return form_open('admin_users/message_admin/' . $y->id) .
			'<div>
				<textarea class="t200 w-100 m-b-20" name="message" placeholder="Your message" required></textarea>
			</div>
			<div>
				<button class="btn btn-primary"> <i class="las la-arrow-circle-right"></i> Send Message</button>
			</div>'
			. form_close();
	}


	public function modal_message_admin($id)
	{
		$y = $this->user_read_model->get_user_details_by_id($id);
		return '<div class="modal fade" id="message' . $id . '" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content modal-form">
							<div class="modal-header">
								<div class="pull-right">
									<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
								</div>
								<h4 class="modal-title">Message: ' . $y->firstname . '</h4>
							</div><!--/.modal-header-->
							<div class="modal-body">'
			. $this->message_admin_form($id) .
			'</div>
						</div>
					</div>
				</div>';
	}


	private function unverify_action_button($id)
	{
		return '<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#unverify' . $id . '"> <i class="las la-times" style="color: red"></i> &nbsp; Un-verify Account </a></p>';
	}


	private function modal_unverify($user)
	{
		$y = is_object($user) ? $user : $this->user_read_model->get_user_details_by_id($user);
		$id = $y->id;
		$options = verification_rejection_reason_options();
		$reason_options = '<option value="">Select a reason</option>';
		foreach ($options as $value => $label) {
			$reason_options .= '<option value="' . html_escape($value) . '">' . html_escape($label) . '</option>';
		}

		return '<div class="modal fade" id="unverify' . $id . '" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content modal-form">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Un-verify: ' . html_escape($y->firstname) . '</h4>
					</div>
					<div class="modal-body">'
						. form_open('admin_users/unverify_user/' . $id) .
						'<div class="form-group">
							<label>Reason *</label>
							<select name="rejection_reason" class="form-control" required>' . $reason_options . '</select>
						</div>
						<div class="form-group" style="margin-top: 15px;">
							<label>Additional note</label>
							<textarea name="rejection_note" class="form-control" rows="4" maxlength="500" placeholder="Optional extra guidance for the user."></textarea>
						</div>
						<div style="margin-top: 20px;">
							<button type="submit" class="btn btn-danger">Send Feedback and Reopen Verification</button>
						</div>'
						. form_close() .
					'</div>
				</div>
			</div>
		</div>';
	}


	public function modals($user)
	{
		$y = is_object($user) ? $user : $this->user_read_model->get_user_details_by_id($user);
		$modal_delete_confirm = modal_delete_confirm($y->id, $y->firstname, 'user', 'admin_users/delete_user');
		return 	$this->modal_options($y) .
			$this->modal_unverify($y) .
			$modal_delete_confirm;
	}
}
