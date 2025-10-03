<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Upcoming_travellers_ajax extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('travellers_model');
    }

    var $table = 'travellers';
    var $column_order = array(null, 'travel_date', 'itinerary_photo', 'fullname', 'phone', 'alt_phone', 'email', 'location', 'arrival_airport', 'destination', 'address', 'airline', 'arrival_date', 'available_space', 'referred_by', 'status', 'date_added'); //set column field database for datatable orderable
    var $column_search = array('travel_date', 'itinerary_photo', 'fullname', 'phone', 'alt_phone', 'email', 'location', 'arrival_airport', 'destination', 'address', 'airline', 'arrival_date', 'available_space', 'referred_by', 'status', 'date_added'); //set column field database for datatable searchable 
    var $order = array('travel_date' => 'asc');

    private function the_query()
    {
        $this->db->from($this->table);
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($_POST['search']['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }
        if (isset($_POST['order'])) {
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

        $this->db->where('status', 'Approved');
        $this->db->where('travel_date >=', date('Y-m-d'));
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered_records()
    {
        $this->the_query();
        $this->db->where('status', 'Approved');
        $this->db->where('travel_date >=', date('Y-m-d'));
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all_records()
    {
        $this->db->where('status', 'Approved');
        $this->db->where('travel_date >=', date('Y-m-d'));
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }


    public function actions($id)
    {
        $y = $this->common_model->get_traveller_details_by_id($id);

        return '<p><a type="button" href="' . base_url('admin_travellers/traveller_profile/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-user" style="color: green"></i> &nbsp; View Traveller </a></p>

		<p><a type="button" href="' . base_url('admin_travellers/update_traveller/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-pencil" style="color: blue"></i> &nbsp; Update Traveller </a></p>

		<p><a type="button" href="' . base_url('admin_travellers/bag_full_traveller/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-briefcase" style="color: green"></i> &nbsp; Bag is Full </a></p>
		

		<p><a type="button" href="' . base_url('admin_travellers/recycle_traveller/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-recycle" style="color: blue"></i> &nbsp; Recycle Traveller </a></p>

		<hr />

		<p><a type="button" href="' . base_url('admin_travellers/unapprove_traveller/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-ban" style="color: red"></i> &nbsp; Unapprove Traveller </a></p>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $id . '"> <i class="fa fa-trash" style="color: red"></i> &nbsp; Delete </a></p>';
    }


    public function options($id)
    {
        return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $id . '" title="Options"> <i class="fa fa-navicon"></i> </a></div>';
    }


    public function modal_options($id)
    {
        $y = $this->common_model->get_traveller_details_by_id($id);
        return '<div class="modal fade" id="options' . $id . '" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content modal-width">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Actions: ' . $y->fullname . '</h4>
					</div><!--/.modal-header-->
					<div class="modal-body">'
            . $this->actions($id) .
            '</div>
				</div>
			</div>
		</div>';
    }


    public function modals($id)
    {
        $y = $this->common_model->get_traveller_details_by_id($id);
        $modal_delete_confirm = modal_delete_confirm($id, $y->fullname, 'travellers', 'admin_travellers/delete_traveller');
        return $this->modal_options($id) .
            $modal_delete_confirm;
    }
}
