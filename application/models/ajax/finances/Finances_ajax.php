<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Finances_ajax extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('bookings_model');
	}

	var $table = 'bookings';
	// Column order updated to use traveller_departure_date
	var $column_order = array(null, 'traveller_departure_date', 'traveller_name', 'total_amount', 'selected_price', 'service_charge', 'vat', 'insurance', 'traveller_commission', 'payment_method', 'payment_status');
	var $column_search = array('traveller_departure_date', 'traveller_name', 'total_amount', 'selected_price', 'service_charge', 'vat', 'insurance', 'traveller_commission', 'payment_method', 'payment_status');
	var $order = array('date_added' => 'desc');


	private function the_query()
	{
		$this->db->from($this->table);
		$i = 0;
		foreach ($this->column_search as $item) // loop column
		{
			if ($_POST['search']['value']) // if datatable send POST for search
			{
				if ($i === 0) // first loop
				{
					$this->db->group_start();
					$this->db->like($item, $_POST['search']['value']);
				} else {
					$this->db->or_like($item, $_POST['search']['value']);
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


	function get_records($month = null, $year = null)
	{
		$this->the_query();
		if ($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);

		// --- FIX: Filter by Traveller Travel Date (traveller_departure_date) ---
		if (!empty($month)) {
			$this->db->where('MONTH(traveller_departure_date)', $month);
		}
		if (!empty($year)) {
			$this->db->where('YEAR(traveller_departure_date)', $year);
		}

		$this->db->where('payment_status', 'completed');
		$this->db->where('currency', 'pounds');

		$this->db->group_start();
		$this->db->where_in('payment_method', ['paystack', 'stripe', 'offline']);
		$this->db->or_where('payment_method IS NULL', null, false);
		$this->db->group_end();

		$query = $this->db->get();
		return $query->result();
	}


	function count_filtered_records($month = null, $year = null)
	{
		$this->the_query();

		// --- FIX: Filter by Traveller Travel Date ---
		if (!empty($month)) {
			$this->db->where('MONTH(traveller_departure_date)', $month);
		}
		if (!empty($year)) {
			$this->db->where('YEAR(traveller_departure_date)', $year);
		}

		$this->db->where('payment_status', 'completed');
		$this->db->where('currency', 'pounds');

		$this->db->group_start();
		$this->db->where_in('payment_method', ['paystack', 'stripe', 'offline']);
		$this->db->or_where('payment_method IS NULL', null, false);
		$this->db->group_end();

		$query = $this->db->get();
		return $query->num_rows();
	}


	function count_all_records($month = null, $year = null)
	{
		// --- FIX: Filter by Traveller Travel Date ---
		if (!empty($month)) {
			$this->db->where('MONTH(traveller_departure_date)', $month);
		}
		if (!empty($year)) {
			$this->db->where('YEAR(traveller_departure_date)', $year);
		}

		$this->db->where('payment_status', 'completed');
		$this->db->where('currency', 'pounds');

		$this->db->group_start();
		$this->db->where_in('payment_method', ['paystack', 'stripe', 'offline']);
		$this->db->or_where('payment_method IS NULL', null, false);
		$this->db->group_end();

		$this->db->from($this->table);
		return $this->db->count_all_results();
	}


	public function actions($id)
	{
		$y = $this->common_model->get_booking_details_by_id($id);

		return
			'<p><a type="button" href="' . base_url('admin_finances/view_finance/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-eye" style="color: green"></i> &nbsp; View Details</a></p>';
	}


	public function options($id)
	{
		return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $id . '" title="Options"> <i class="fa fa-navicon"></i> </a></div>';
	}


	public function modal_options($id)
	{
		$y = $this->common_model->get_booking_details_by_id($id);
		return '<div class="modal fade" id="options' . $id . '" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content modal-width">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Actions: ' . $y->agent_name . '</h4>
					</div><div class="modal-body">'
			. $this->actions($id) .
			'</div>
				</div>
			</div>
		</div>';
	}


	public function modals($id)
	{
		$y = $this->common_model->get_booking_details_by_id($id);
		$modal_delete_confirm = modal_delete_confirm($id, $y->agent_name, 'bookings', 'bookings/delete_booking');
		return $this->modal_options($id) .
			$modal_delete_confirm;
	}
}
