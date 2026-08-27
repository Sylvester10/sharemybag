<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Finances_ajax extends CI_Model
{
	private function requestLength()
	{
		return isset($_POST['length']) ? (int) $_POST['length'] : 10;
	}

	private function requestStart()
	{
		return isset($_POST['start']) ? (int) $_POST['start'] : 0;
	}
 
	private function applyCurrencyFilter($currency)
	{
		$allowed_values = currency_db_values($currency);
		$normalized_currency = currency_code_normalize($currency);

		$this->db->group_start();
		$this->db->where_in('bookings.currency', $allowed_values);
		if ($normalized_currency === 'GBP') {
			$this->db->or_where('bookings.currency IS NULL', null, false);
		}
		$this->db->group_end();
	}

	private function applyPaymentMethodFilter()
	{
		$this->db->where("(LOWER(COALESCE(bookings.payment_method, '')) IN ('paystack','stripe','offline','bank') OR bookings.payment_method IS NULL)", null, false);
	}

	public function __construct()
	{
		parent::__construct();
		$this->load->model('bookings_model');
	}

	var $table = 'bookings';
	var $column_order = array(null, 'bookings.traveller_departure_date', 'bookings.traveller_name', 'bookings.total_amount', 'bookings.selected_price', 'bookings.service_charge', null, null, null, 'bookings.selected_space', 'bookings.insurance', null, null, 'bookings.traveller_commission', 'bookings.payment_method');
	var $column_search = array('bookings.traveller_departure_date', 'bookings.traveller_name', 'bookings.total_amount', 'bookings.selected_price', 'bookings.service_charge', 'bookings.selected_space', 'bookings.vat', 'bookings.insurance', 'bookings.traveller_commission', 'bookings.payment_method');
	var $order = array('bookings.date_added' => 'desc');


	private function the_query()
	{
		$search_value = datatable_search_value();
		$this->db->from($this->table);
		ci_where_not_deleted($this->db, 'bookings');
		// --- JOIN TRAVELLERS TABLE FOR ROUTE FILTERING ---
		$this->db->join('travellers', 'bookings.traveller_id = travellers.id', 'left');
		ci_where_not_deleted($this->db, 'travellers');

		$i = 0;
		foreach ($this->column_search as $item) // loop column
		{
			if ($search_value !== '') // if datatable send POST for search
			{
				if ($i === 0) // first loop
				{
					$this->db->group_start();
					$this->db->like($item, $search_value);
				} else {
					$this->db->or_like($item, $search_value);
				}
				if (count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		if (isset($_POST['order']['0']['column'], $_POST['order']['0']['dir'])) { // here order processing
			$order_column_index = (int) $_POST['order']['0']['column'];
			$order_direction = strtolower($_POST['order']['0']['dir']) === 'asc' ? 'asc' : 'desc';
			if (isset($this->column_order[$order_column_index]) && $this->column_order[$order_column_index] !== null) {
				$this->db->order_by($this->column_order[$order_column_index], $order_direction);
			}
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}


	function get_records($month = null, $year = null, $route = null)
	{
		$this->the_query();
		$this->db->select('bookings.*');
		$length = $this->requestLength();
		if ($length !== -1) {
			$this->db->limit($length, $this->requestStart());
		}

		if (!empty($month)) {
			$this->db->where('MONTH(bookings.traveller_departure_date)', $month);
		}
		if (!empty($year)) {
			$this->db->where('YEAR(bookings.traveller_departure_date)', $year);
		}
		// --- FIX: Filter by Route using Joined Columns ---
		if (!empty($route)) {
			$parts = explode('-', $route);
			if (count($parts) == 2) {
				$this->db->where('travellers.location', $parts[0]);
				$this->db->where('travellers.destination', $parts[1]);
			}
		}

		$this->db->where('bookings.payment_status', 'completed');
		$this->applyCurrencyFilter('GBP');

		$this->applyPaymentMethodFilter();

		$query = $this->db->get();
		return $query->result();
	}


	function count_filtered_records($month = null, $year = null, $route = null)
	{
		$this->the_query();

		if (!empty($month)) {
			$this->db->where('MONTH(bookings.traveller_departure_date)', $month);
		}
		if (!empty($year)) {
			$this->db->where('YEAR(bookings.traveller_departure_date)', $year);
		}
		// --- FIX: Filter by Route ---
		if (!empty($route)) {
			$parts = explode('-', $route);
			if (count($parts) == 2) {
				$this->db->where('travellers.location', $parts[0]);
				$this->db->where('travellers.destination', $parts[1]);
			}
		}

		$this->db->where('bookings.payment_status', 'completed');
		$this->applyCurrencyFilter('GBP');

		$this->applyPaymentMethodFilter();

		$query = $this->db->get();
		return $query->num_rows();
	}


	function count_all_records($month = null, $year = null, $route = null)
	{
		if (!empty($month)) {
			$this->db->where('MONTH(bookings.traveller_departure_date)', $month);
		}
		if (!empty($year)) {
			$this->db->where('YEAR(bookings.traveller_departure_date)', $year);
		}
		// --- FIX: Filter by Route ---
		if (!empty($route)) {
			$parts = explode('-', $route);
			if (count($parts) == 2) {
				$this->db->where('travellers.location', $parts[0]);
				$this->db->where('travellers.destination', $parts[1]);
			}
		}

		$this->db->where('bookings.payment_status', 'completed');
		$this->applyCurrencyFilter('GBP');

		$this->applyPaymentMethodFilter();

		$this->db->from($this->table);
		// Need to join here too for the WHERE clause to work
		$this->db->join('travellers', 'bookings.traveller_id = travellers.id', 'left');
		ci_where_not_deleted($this->db, 'bookings');
		ci_where_not_deleted($this->db, 'travellers');
		return $this->db->count_all_results();
	}


	public function actions($booking)
	{
		$id = is_object($booking) ? $booking->id : $booking;

		return
			'<p><a type="button" href="' . base_url('admin_finances/view_finance/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-eye" style="color: green"></i> &nbsp; View Details</a></p>
			<p><a type="button" href="' . base_url('admin/invoice/' . $id) . '" target="_blank" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-file-invoice" style="color: #0c6cf2"></i> &nbsp; View Invoice</a></p>
			<p><a type="button" href="' . base_url('admin/invoice/download/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-download" style="color: #444"></i> &nbsp; Download Invoice</a></p>';
	}


	public function options($id)
	{
		return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $id . '" title="Options"> <i class="las la-bars"></i> </a></div>';
	}


	public function modal_options($booking)
	{
		$id = is_object($booking) ? $booking->id : $booking;
		$agent_name = is_object($booking) ? $booking->agent_name : '';
		return '<div class="modal fade" id="options' . $id . '" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content modal-width">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Actions: ' . $agent_name . '</h4>
					</div><div class="modal-body">'
			. $this->actions($booking) .
			'</div>
				</div>
			</div>
		</div>';
	}


	public function modals($booking)
	{
		$id = is_object($booking) ? $booking->id : $booking;
		$agent_name = is_object($booking) ? $booking->agent_name : '';
		$modal_delete_confirm = modal_delete_confirm($id, $agent_name, 'bookings', 'bookings/delete_booking');
		return $this->modal_options($booking) .
			$modal_delete_confirm;
	}
}
