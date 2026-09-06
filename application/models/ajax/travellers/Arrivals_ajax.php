<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Arrivals_ajax extends CI_Model
{
    private $column_order = array(
        null,
        'travellers.travel_date',
        'travellers.fullname',
        'travellers.phone',
        'travellers.email',
        'travellers.location',
        'travellers.destination',
        'travellers.arrival_airport',
        'travellers.arrival_date',
        'travellers.original_bag_space',
        'travellers.used_space',
        'travellers.available_space',
        'booking_count',
        'travellers.status',
    );

    private $column_search = array(
        'travellers.fullname',
        'travellers.phone',
        'travellers.alt_phone',
        'travellers.email',
        'travellers.location',
        'travellers.current_state',
        'travellers.destination',
        'travellers.arrival_state',
        'travellers.arrival_airport',
        'bookings.tracking_id',
        'bookings.user_fullname',
    );

    private function buildEligibleQuery($includeSearch = true)
    {
        $this->db->from('travellers');
        $this->db->join('bookings', 'bookings.traveller_id = travellers.id', 'inner');
        ci_where_not_deleted($this->db, 'travellers');
        ci_where_not_deleted($this->db, 'bookings');
        $this->db->where('travellers.travel_date <', date('Y-m-d'));
        $this->db->where('bookings.payment_status', 'completed');

        $destination = trim((string) $this->input->post('destination', true));
        if ($destination !== '') {
            $this->db->where('travellers.destination', $destination);
        }

        if (!$includeSearch) {
            return;
        }

        $searchValue = datatable_search_value();
        if ($searchValue === '') {
            return;
        }

        $this->db->group_start();
        foreach ($this->column_search as $index => $column) {
            if ($index === 0) {
                $this->db->like($column, $searchValue);
            } else {
                $this->db->or_like($column, $searchValue);
            }
        }
        $this->db->group_end();
    }

    public function get_records()
    {
        $this->db->select('travellers.*, COUNT(DISTINCT bookings.id) AS booking_count', false);
        $this->buildEligibleQuery(true);
        $this->db->group_by('travellers.id');

        if (isset($_POST['order'][0]['column'], $_POST['order'][0]['dir'])) {
            $index = (int) $_POST['order'][0]['column'];
            $direction = strtolower($_POST['order'][0]['dir']) === 'asc' ? 'asc' : 'desc';
            if (isset($this->column_order[$index]) && $this->column_order[$index]) {
                $this->db->order_by($this->column_order[$index], $direction);
            }
        } else {
            $this->db->order_by('travellers.travel_date', 'desc');
        }

        $length = isset($_POST['length']) ? (int) $_POST['length'] : 10;
        $start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
        if ($length !== -1) {
            $this->db->limit($length, $start);
        }

        return $this->db->get()->result();
    }

    public function count_filtered_records()
    {
        $this->db->select('COUNT(DISTINCT travellers.id) AS total', false);
        $this->buildEligibleQuery(true);
        $row = $this->db->get()->row();
        return $row ? (int) $row->total : 0;
    }

    public function count_all_records()
    {
        $this->db->select('COUNT(DISTINCT travellers.id) AS total', false);
        $this->buildEligibleQuery(false);
        $row = $this->db->get()->row();
        return $row ? (int) $row->total : 0;
    }

    public function get_eligible_traveller($travellerId)
    {
        $this->db->select('travellers.*, COUNT(DISTINCT bookings.id) AS booking_count', false);
        $this->buildEligibleQuery(false);
        $this->db->where('travellers.id', (int) $travellerId);
        $this->db->group_by('travellers.id');
        return $this->db->get()->row();
    }

    public function action_menu($traveller)
    {
        $id = (int) $traveller->id;
        $modalId = 'arrivalOptions' . $id;
        $button = '<div class="text-center"><a href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#' . $modalId . '" title="Options"><i class="las la-bars"></i></a></div>';
        $modal = '<div class="modal fade" id="' . $modalId . '" role="dialog">
            <div class="modal-dialog"><div class="modal-content modal-width">
                <div class="modal-header"><div class="pull-right"><button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close">&times;</button></div>
                    <h4 class="modal-title">Actions: ' . html_escape($traveller->fullname) . '</h4>
                </div>
                <div class="modal-body">
                    <p><a href="' . base_url('shipping/arrival_traveller/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"><i class="las la-user" style="color: green"></i> &nbsp; View Traveller</a></p>
                    <p><a href="' . base_url('shipping/arrival_traveller/' . $id . '#arrival-bookings') . '" class="btn btn-default btn-sm btn-block action-btn clickable"><i class="las la-box-open" style="color: #f36b24"></i> &nbsp; View Bookings</a></p>
                </div>
            </div></div>
        </div>';

        return $button . $modal;
    }
}
