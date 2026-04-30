<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Unapproved_travellers_ajax extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	var $table = 'travellers';
	var $column_order = array(null, 'travellers.id', 'travellers.itinerary_photo', 'travellers.fullname', 'travellers.phone', 'travellers.email', 'travellers.airline', 'travellers.travel_date', 'travellers.bag_space');
	var $column_search = array('travellers.id', 'travellers.itinerary_photo', 'travellers.fullname', 'travellers.phone', 'travellers.email', 'travellers.airline', 'travellers.travel_date', 'travellers.bag_space');
	var $order = array('travellers.travel_date' => 'ASC');


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

		$where = "status = 'Unapproved' ";

		$this->db->where($where);
		$query = $this->db->get();
		return $query->result();
	}


	function count_filtered_records()
	{
		$this->the_query();

		$where = "status = 'Unapproved' ";

		$this->db->where($where);
		$query = $this->db->get();
		return $query->num_rows();
	}


	public function count_all_records()
	{

		$where = "status = 'Unapproved' ";

		$this->db->where($where);
		$this->db->from($this->table);
		ci_where_not_deleted($this->db, $this->table);
		return $this->db->count_all_results();
	}


	public function actions($traveller)
	{
		return '<p><a type="button" href="' . base_url('admin_travellers/traveller_profile/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-user" style="color: green"></i> &nbsp; View Traveller </a></p>

		<p><a type="button" href="' . base_url('admin_travellers/update_traveller/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-pen" style="color: green"></i> &nbsp; Update Traveller </a></p>

		<hr />

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $traveller->id . '"> <i class="las la-trash" style="color: red"></i> &nbsp; Delete </a></p>';
	}


	public function options($id)
	{
		return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $id . '" title="Options"> <i class="las la-bars"></i> </a></div>';
	}


	public function modal_options($traveller)
	{
		return '<div class="modal fade" id="options' . $traveller->id . '" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content modal-width">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Actions: ' . $traveller->fullname . '</h4>
					</div><!--/.modal-header-->
					<div class="modal-body">'
			. $this->actions($traveller) .
			'</div>
				</div>
			</div>
		</div>';
	}


	public function modals($traveller)
	{
		$modal_delete_confirm = modal_delete_confirm($traveller->id, $traveller->fullname, 'travellers', 'admin_travellers/delete_traveller');
		return $this->modal_options($traveller) .
			$modal_delete_confirm;
	}
}
