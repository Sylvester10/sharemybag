<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Canceled_bookings_ajax extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    var $table = 'bookings';
    var $column_order = array(null, 'bookings.date_added', 'bookings.traveller_name', 'bookings.selected_space', 'bookings.user_fullname', 'bookings.agent_name', 'bookings.receiver_name', 'bookings.need_help', 'bookings.items', 'bookings.payment_status');
    var $column_search = array('bookings.date_added', 'bookings.traveller_name', 'bookings.currency', 'bookings.selected_space', 'bookings.user_fullname', 'bookings.agent_name', 'bookings.receiver_name', 'bookings.need_help', 'bookings.items', 'bookings.payment_status');
    var $order = array('bookings.date_added' => 'desc');


    private function the_query()
    {
        $search_value = datatable_search_value();
        $this->db->select('bookings.*, travellers.destination AS traveller_destination');
        $this->db->from($this->table);
        $this->db->join('travellers', 'bookings.traveller_id = travellers.id', 'left');
        ci_where_not_deleted($this->db, $this->table);
        ci_where_not_deleted($this->db, 'travellers');
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

        $where = "bookings.payment_status = 'canceled' ";
        $this->db->where($where);

        $query = $this->db->get();
        return $query->result();
    }


    function count_filtered_records()
    {
        $this->the_query();

        $where = "bookings.payment_status = 'canceled' ";

        $this->db->where($where);
        $query = $this->db->get();
        return $query->num_rows();
    }


    public function count_all_records()
    {

        $where = "bookings.payment_status = 'canceled' ";

        $this->db->where($where);
        $this->db->from($this->table);
        ci_where_not_deleted($this->db, $this->table);
        return $this->db->count_all_results();
    }


    public function agent_details($booking)
    {
        return 'Name: ' . $booking->agent_name . '<br />
				Phone No: ' . $booking->agent_phone . '<br />
				Email: ' . $booking->agent_email . '<br />
				Address: ' . $booking->agent_address;
    }


    public function actions($booking)
    {
        if (payment_status_normalize($booking->payment_status) != 'completed') {
			$booking_action = '<p><a type="button" href="' . base_url('admin_bookings/confirm_booking/' . $booking->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-check" style="color: green"></i> &nbsp; Confirm Booking </a></p>';
		} else {
			$booking_action = '<p><a type="button" href="' . base_url('admin_bookings/cancel_booking/' . $booking->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-times" style="color: red"></i> &nbsp; Cancel Booking </a></p>';
		};

		return $booking_action . '
		
		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $booking->id . '"> <i class="las la-trash" style="color: red"></i> &nbsp; Delete </a></p>';
    }


    public function options($id)
    {
        return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $id . '" title="Options"> <i class="las la-bars"></i> </a></div>';
    }


    public function modal_options($booking)
    {
        return '<div class="modal fade" id="options' . $booking->id . '" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content modal-width">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Actions: ' . $booking->agent_name . '</h4>
					</div><!--/.modal-header-->
					<div class="modal-body">'
            . $this->actions($booking) .
            '</div>
				</div>
			</div>
		</div>';
    }


    public function modals($booking)
    {
        $modal_delete_confirm = modal_delete_confirm($booking->id, $booking->agent_name, 'bookings', 'admin_bookings/delete_booking');
        return $this->modal_options($booking) .
            $modal_delete_confirm;
    }
}
