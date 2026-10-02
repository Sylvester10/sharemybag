<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Approved_travellers_ajax extends CI_Model
{
    private $approved_user_options = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_read_model');
    }

    var $table = 'travellers';
    var $column_order = array(null, null, 'travellers.travel_date', 'travellers.fullname', 'travellers.phone', 'travellers.alt_phone', 'travellers.email', 'travellers.location', 'travellers.arrival_airport', 'travellers.destination', 'travellers.address', 'travellers.airline', 'travellers.arrival_date', 'travellers.original_bag_space', 'travellers.used_space', 'travellers.available_space', 'travellers.status', 'travellers.date_added');
    var $column_search = array('travellers.travel_date', 'travellers.fullname', 'travellers.phone', 'travellers.alt_phone', 'travellers.email', 'travellers.location', 'travellers.arrival_airport', 'travellers.destination', 'travellers.address', 'travellers.airline', 'travellers.arrival_date', 'travellers.original_bag_space', 'travellers.used_space', 'travellers.available_space', 'travellers.status', 'travellers.date_added');
    var $order = array('travellers.travel_date' => 'desc');


    private function the_query()
    {
        $search_value = datatable_search_value();
        $this->db->select('travellers.*, users.firstname AS referrer_firstname');
        $this->db->from($this->table);
        $this->db->join('users', 'travellers.referred_by = users.username', 'left');
        ci_where_not_deleted($this->db, $this->table);
        ci_where_not_deleted($this->db, 'users');
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


    public function get_records($destination = null)
    {
        // This line runs the query builder that includes sorting and searching
        $this->the_query();

        $this->db->where_in('travellers.status', array('Approved', 'Unapproved'));

        if (!empty($destination)) {
            $this->db->where('travellers.destination', $destination);
        }

        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);

        $query = $this->db->get();
        return $query->result();
    }

    public function count_all_records($destination = null)
    {
        $this->db->from('travellers');
        ci_where_not_deleted($this->db, 'travellers');
        $this->db->where_in('travellers.status', array('Approved', 'Unapproved'));

        if (!empty($destination)) {
            $this->db->where('travellers.destination', $destination);
        }
        return $this->db->count_all_results();
    }

    public function count_filtered_records($destination = null)
    {
        $search_value = datatable_search_value();
        $this->db->from('travellers');
        ci_where_not_deleted($this->db, 'travellers');
        $this->db->where_in('travellers.status', array('Approved', 'Unapproved'));

        if (!empty($destination)) {
            $this->db->where('travellers.destination', $destination);
        }

        if ($search_value !== '') {
            $this->db->group_start();
            $this->db->like('travellers.fullname', $search_value);
            $this->db->or_like('travellers.email', $search_value);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }


    public function actions($traveller)
    {
        if ($traveller->bag_locked == 0) {
            $bag_lock_status = '<p><a type="button" href="' . base_url('admin_travellers/lock_traveller_bag/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-briefcase" style="color: green"></i> &nbsp; Lock Bag </a></p>';
        } else {
            $bag_lock_status = '<p><a type="button" href="' . base_url('admin_travellers/unlock_traveller_bag/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-lock-open" style="color: orange"></i> &nbsp; Unlock Bag </a></p>';
        }

        return '<p><a type="button" href="' . base_url('admin_travellers/traveller_profile/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-user" style="color: green"></i> &nbsp; View Traveller </a></p>

		<p><a type="button" href="' . base_url('admin_travellers/traveller_profile/' . $traveller->id) . '#table" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-box-open" style="color: #f36b24"></i> &nbsp; Manage Parcels </a></p>

		<p><a type="button" href="' . base_url('admin_travellers/update_traveller/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-pen" style="color: blue"></i> &nbsp; Update Traveller </a></p>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#add_bag' . $traveller->id . '"> <i class="las la-plus" style="color: green"></i> <i class="las la-briefcase" style="color: green"></i> &nbsp; Add Bag Space </a></p>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#remove_bag' . $traveller->id . '"> <i class="las la-minus" style="color: red"></i> <i class="las la-briefcase" style="color: red"></i> &nbsp; Remove Bag Space </a></p>

		<p><a type="button" href="' . base_url('admin_travellers/recycle_traveller/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-recycle" style="color: blue"></i> &nbsp; Recycle Traveller </a></p>

        <hr>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#offline' . $traveller->id . '"> <i class="las la-file-invoice" style="color: grey"></i> &nbsp; Offline Booking </a></p>

        ' . $bag_lock_status . '

		<hr />

		<p><a type="button" href="' . base_url('admin_travellers/unapprove_traveller/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-ban" style="color: red"></i> &nbsp; Disapprove Traveller </a></p>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $traveller->id . '"> <i class="las la-trash" style="color: red"></i> &nbsp; Delete </a></p>';
    }


    public function options($id)
    {
        return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $id . '" title="Options"> <i class="las la-bars"></i> </a></div>';
    }


    private function generate_bag_space_options($selected_value, $max_space)
    {
        $max_space = (int) $max_space;

        $options = '<option value="">Select</option>';
        for ($i = 1; $i <= $max_space; $i++) {
            $selected = ($i == $selected_value) ? 'selected' : '';
            $options .= '<option value="' . $i . '" ' . $selected . ' data-max-space="' . $max_space . '">' . $i . 'KG</option>';
        }

        return $options;
    }

    private function get_approved_user_options()
    {
        if ($this->approved_user_options !== null) {
            return $this->approved_user_options;
        }

        $users = $this->user_read_model->get_approved_users();
        usort($users, function ($a, $b) {
            return strcmp($a->firstname, $b->firstname);
        });

        $options = '';
        foreach ($users as $u) {
            $options .= '<option value="' . $u->id . '">' . htmlspecialchars($u->firstname . ' ' . $u->lastname . ' (' . $u->email . ')') . '</option>';
        }

        $this->approved_user_options = $options;
        return $this->approved_user_options;
    }


    public function modal_options($traveller)
    {
        $bag_space_options = $this->generate_bag_space_options($traveller->available_space, $traveller->available_space);
        $original_spaces = kilogram();
        $user_options = $this->get_approved_user_options();
        $original_space = '<option selected value="' . $traveller->original_bag_space . '">' . $traveller->original_bag_space . ' KG</option>';
        if (!empty($original_spaces)) {
            foreach ($original_spaces as $space) {
                $original_space .= '<option value="' . $space . '">' . $space . ' KG</option>';
            }
        }

        return '
        <div class="modal fade" id="options' . $traveller->id . '" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content modal-width">
                    <div class="modal-header">
                        <div class="pull-right">
                            <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                        </div>
                        <h4 class="modal-title">Actions: ' . $traveller->fullname . '</h4>
                    </div>
                    <div class="modal-body">' . $this->actions($traveller) . '</div>
                </div>
            </div>
        </div>

        <div class="modal fade admin-form-modal admin-form-modal--compact" id="add_bag' . $traveller->id . '" role="dialog" aria-modal="true" aria-labelledby="add_bag_title_' . $traveller->id . '">
            <div class="modal-dialog modal-md admin-form-modal__dialog">
                <div class="modal-content admin-form-modal__content">
                    <div class="modal-header ">
                        <div class="pull-right">
                            <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                        </div>
                        <h4 class="modal-title admin-form-modal__title" id="add_bag_title_' . $traveller->id . '">Add Original Bag Space: ' . $traveller->original_bag_space . 'KG</h4>
                    </div>

                    ' . form_open_multipart('admin_travellers/add_traveller_bag_space/' . $traveller->id, 'id="add_bag_form_' . $traveller->id . '" class="admin-form-modal__form"') . '

                    <div class="modal-body admin-form-modal__body">

                        <div class="form-group admin-form-modal__section">
                            <label class="form-control-label" for="add_bag_space_' . $traveller->id . '">Select Bag Space</label>
                            <select class="form-control select2-user" name="selected_space" id="add_bag_space_' . $traveller->id . '" required>
                                ' . $original_space . '
                            </select>
                            <small class="admin-form-modal__note">Selected space will be added to the original bag space.</small>
                        </div>
                    </div>

                    <div class="modal-footer admin-form-modal__footer">
                        <div class="mt-3">
                            <button type="submit" class="btn btn-md btn-primary">
                                <span>Update Bag Space</span>
                            </button>
                        </div>
                    </div>

                    ' . form_close() . '

                </div>
            </div>
        </div>

        <div class="modal fade admin-form-modal admin-form-modal--compact" id="remove_bag' . $traveller->id . '" role="dialog" aria-modal="true" aria-labelledby="remove_bag_title_' . $traveller->id . '">
            <div class="modal-dialog modal-md admin-form-modal__dialog">
                <div class="modal-content admin-form-modal__content">
                    <div class="modal-header ">
                        <div class="pull-right">
                            <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                        </div>
                        <h4 class="modal-title admin-form-modal__title" id="remove_bag_title_' . $traveller->id . '">Remove Original Bag Space: ' . $traveller->original_bag_space . 'KG</h4>
                    </div>

                    ' . form_open_multipart('admin_travellers/remove_traveller_bag_space/' . $traveller->id, 'id="remove_bag_form_' . $traveller->id . '" class="admin-form-modal__form"') . '

                    <div class="modal-body admin-form-modal__body">

                        <div class="form-group admin-form-modal__section">
                            <label class="form-control-label" for="remove_bag_space_' . $traveller->id . '">Select Bag Space</label>
                            <select class="form-control select2-user" name="selected_space" id="remove_bag_space_' . $traveller->id . '" required>
                                ' . $original_space . '
                            </select>
                            <small class="admin-form-modal__note">Selected space will be removed from the original bag space.</small>
                        </div>
                    </div>

                    <div class="modal-footer admin-form-modal__footer">
                        <div class="mt-3">
                            <button type="submit" class="btn btn-md btn-primary">
                                <span>Update Bag Space</span>
                            </button>
                        </div>
                    </div>

                    ' . form_close() . '

                </div>
            </div>
        </div>

        <div class="modal fade admin-form-modal admin-form-modal--wide admin-offline-booking-modal" id="offline' . $traveller->id . '" role="dialog" aria-modal="true" aria-labelledby="offline_booking_title_' . $traveller->id . '" data-form-action="' . html_escape(base_url('admin_travellers/add_offline_booking/' . $traveller->id)) . '">
            <div class="modal-dialog modal-lg admin-form-modal__dialog admin-offline-booking-dialog">
                <div class="modal-content modal-widths admin-form-modal__content admin-offline-booking-content">
                    <div class="modal-header ">
                        <div class="pull-right">
                            <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                        </div>
                        <h4 class="modal-title" id="offline_booking_title_' . $traveller->id . '">Update offline booking: ' . $traveller->fullname . '</h4>
                    </div>

                    ' . form_open_multipart('admin_travellers/add_offline_booking/' . $traveller->id, 'id="offline_booking_form_' . $traveller->id . '" class="admin-form-modal__form admin-offline-booking-form"') . '

                    <div class="modal-body admin-form-modal__body admin-offline-booking-body">

                        <div class="form-group admin-form-modal__section admin-offline-booking-user">
                            <label class="form-control-label">Select SMB User *</label>
                            <select name="user_id" id="user_id_' . $traveller->id . '" class="form-control select2-user" required>
                                <option value="">-- Select User --</option>
                                ' . $user_options . '
                            </select>
                        </div>

						<hr>
						<h5 class="mt-3 admin-form-modal__section-title admin-offline-booking-section-title"><strong>Agent Details</strong></h5>

                        <div class="form-check mb-2 admin-form-modal__assist admin-offline-booking-autofill">
                            <input class="form-check-input autofill-agent" type="checkbox" id="autofill-agent-' . $traveller->id . '">
                            <label class="form-check-label" for="autofill-agent-' . $traveller->id . '">
                                Fill with selected SMB User details
                            </label>
                        </div>

                        <div class="row admin-form-modal__grid admin-offline-booking-grid">
                            <div class="col-lg-12 mb-2">
                                <label>Full Name *</label>
                                <input type="text" name="agent_name" class="form-controls" required>
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>Email *</label>
                                <input type="email" name="agent_email" class="form-controls" required>
                            </div>
                            ' . render_phone_input(array(
            'wrapper_class' => 'col-lg-6 mb-2',
            'field_name' => 'agent_phone',
            'country_code_name' => 'agent_country_code',
            'country_code_id' => 'offline_agent_country_' . $traveller->id,
            'input_id' => 'offline_agent_phone_' . $traveller->id,
            'label' => 'Phone',
            'required' => true,
            'input_class' => 'form-controls smb-phone-input__number',
            'select_class' => 'form-controls smb-phone-input__country',
        )) . '
                            <div class="col-lg-12 mb-2">
                                <label>Address *</label>
                                <input type="text" name="agent_address" class="form-controls" required>
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>City *</label>
                                <input type="text" name="agent_locality" class="form-controls" required>
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>Postal Code *</label>
                                <input type="text" name="agent_postcode" class="form-controls" required>
                            </div>
                        </div>

						<hr>
                        <h5 class="mt-3 admin-form-modal__section-title admin-offline-booking-section-title"><strong>Receiver Details</strong></h5>

                        <div class="form-check mb-2 admin-form-modal__assist admin-offline-booking-autofill">
                            <input class="form-check-input autofill-receiver" type="checkbox" id="autofill-receiver-' . $traveller->id . '">
                            <label class="form-check-label" for="autofill-receiver-' . $traveller->id . '">
                                Fill with selected SMB User details
                            </label>
                        </div>

                        <div class="row admin-form-modal__grid admin-offline-booking-grid">
                            <div class="col-md-12 mb-2">
                                <label>Full Name *</label>
                                <input type="text" name="receiver_name" class="form-controls" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>Email *</label>
                                <input type="email" name="receiver_email" class="form-controls" required>
                            </div>
                            ' . render_phone_input(array(
            'wrapper_class' => 'col-md-6 mb-2',
            'field_name' => 'receiver_phone',
            'country_code_name' => 'receiver_country_code',
            'country_code_id' => 'offline_receiver_country_' . $traveller->id,
            'input_id' => 'offline_receiver_phone_' . $traveller->id,
            'label' => 'Phone',
            'required' => true,
            'input_class' => 'form-controls smb-phone-input__number',
            'select_class' => 'form-controls smb-phone-input__country',
        )) . '
                            <div class="col-md-12 mb-2">
                                <label>Address *</label>
                                <input type="text" name="receiver_address" class="form-controls" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>City *</label>
                                <input type="text" name="receiver_locality" class="form-controls" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>Postal Code *</label>
                                <input type="text" name="receiver_postcode" class="form-controls" required>
                            </div>
                        </div>

						<hr>
						<h5 class="mt-3 admin-form-modal__section-title admin-offline-booking-section-title"><strong>Bag Space Details</strong></h5>
                        <div class="form-group mt-3 admin-form-modal__section admin-offline-booking-space">
                            <label>How much Bag Space was bought? *</label>
                            <select class="form-control select2container" name="selected_space" required>
                                ' . $bag_space_options . '
                            </select>
                        </div>
                    </div>

					<div class="modal-footer admin-form-modal__footer admin-offline-booking-footer">

                        <div class="mt-3">
                            <button type="submit" id="send_mail_btn" class="btn btn-md btn-primary">
                                <span id="btn_text">Update Traveller</span>
                                <span id="loading_icon" style="display: none;"><i class="las la-spinner la-spin"></i></span>
                            </button>
                        </div>

					</div>

                    ' . form_close() . '

                </div>
            </div>
        </div>';
    }


    public function modals($traveller)
    {
        $modal_delete_confirm = modal_delete_confirm($traveller->id, $traveller->fullname, 'travellers', 'admin_travellers/delete_traveller');
        return $this->modal_options($traveller) . $modal_delete_confirm;
    }
}
