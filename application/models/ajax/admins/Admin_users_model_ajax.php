<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Admin_users_model_ajax extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->load->model('admin_users_model');
	}

	var $table = 'admins';
	var $column_order = array(null, 'id', 'name', 'email', 'phone', 'level', 'roles'); //set column field database for datatable orderable
	var $column_search = array('id', 'name', 'email', 'phone', 'level', 'roles'); //set column field database for datatable searchable 
	var $order = array('id' => 'asc');

	
	private function the_query() {		
		$this->db->from($this->table);
		$i = 0;	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				} else {
					$this->db->or_like($item, $_POST['search']['value']);
				}
				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}		
		if(isset($_POST['order'])) { // here order processing 
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if(isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}
	

	function get_records() {
		$this->the_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
	    $query = $this->db->get();
		return $query->result();
	}
	

	function count_filtered_records() {
		$this->the_query();
	    $query = $this->db->get();
		return $query->num_rows();
	}
	
	
	public function count_all_records() {
	    $this->db->from($this->table);
		return $this->db->count_all_results();
	}
	
	
	public function actions($admin_id) {
		return '<p><a type="button" href="' . base_url('admin_users/edit_admin/'.$admin_id) .'" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-pencil" style="color: green"></i> &nbsp; Edit Admin </a></p>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#message'.$admin_id.'"> <i class="fa fa-envelope" style="color: green"></i> &nbsp; Message Admin </a></p>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete'.$admin_id.'"> <i class="fa fa-trash" style="color: red"></i> &nbsp; Delete </a></p>';
	}
	
	
	public function options($admin_id) {
		return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options'.$admin_id.'" title="Options"> <i class="fa fa-navicon"></i> </a></div>';
	}
	
	
	public function modal_options($admin_id) {
		$y = $this->common_model->get_admin_details_by_id($admin_id);
		return '<div class="modal fade" id="options'.$admin_id.'" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content modal-width">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Actions: ' .$y->name. '</h4>
					</div><!--/.modal-header-->
					<div class="modal-body">'
						.$this->actions($admin_id).
					'</div>
				</div>
			</div>
		</div>';
	} 
	
	
	public function message_admin_form($admin_id) {
		$y = $this->common_model->get_admin_details_by_id($admin_id);
		return form_open('admin_users/message_admin/'.$y->id). 
			'<div>
				<textarea class="t200 w-100 m-b-20" name="message" placeholder="Your message" required></textarea>
			</div>
			<div>
				<button class="btn btn-primary"> <i class="fa fa-arrow-circle-right"></i> Send Message</button>
			</div>'
		.form_close();
	}
	
	
	public function modal_message_admin($admin_id) {
		$y = $this->common_model->get_admin_details_by_id($admin_id);
		return '<div class="modal fade" id="message'.$admin_id.'" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content modal-form">
							<div class="modal-header">
								<div class="pull-right">
									<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
								</div>
								<h4 class="modal-title">Message: ' .$y->name. '</h4>
							</div><!--/.modal-header-->
							<div class="modal-body">'
								.$this->message_admin_form($admin_id).
							'</div>
						</div>
					</div>
				</div>';
	}
	
	
	public function modals($admin_id) {
		$y = $this->common_model->get_admin_details_by_id($admin_id);
		$modal_message_user = modal_message_user($admin_id, $y->name, 'admin_users/message_admin');
		$modal_delete_confirm = modal_delete_confirm($admin_id, $y->name, 'admin', 'admin_users/delete_admin');
		return 	$this->modal_options($admin_id) . 
				$modal_message_user . 
				$modal_delete_confirm;
	}
	
	
	
	
}