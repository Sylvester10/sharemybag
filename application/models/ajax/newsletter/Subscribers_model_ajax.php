<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Subscribers_model_ajax extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->load->model('newsletter_model');
	}

	var $table = 'newsletter_subscribers';
	var $column_order = array(null, 'id', 'name', 'email', 'date'); //set column field database for datatable orderable
	var $column_search = array('id', 'name', 'email', 'date'); //set column field database for datatable searchable 
	var $order = array('id' => 'desc'); 

	
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
	
	
	public function options($id) {
		return '<div class="text-center"><a type="button" href="#" class="btn btn-danger btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#delete'.$id.'" title="Delete Subscriber"> <i class="fa fa-trash"></i> </a></div>';
	}

	
	public function modals($id) {
		$y = $this->newsletter_model->get_subscriber_details($id);
		$modal_delete_confirm = modal_delete_confirm($id, $y->name, 'subscriber', 'newsletter/delete_subscriber');
		return $modal_delete_confirm;
	}
	
	
	
	
}