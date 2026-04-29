<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Upcoming_travellers_ajax extends CI_Model
{
    private $approved_user_options = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_read_model');
    }

    var $table = 'travellers';
    var $column_order = array(null, null, 'travellers.travel_date', 'travellers.fullname', 'travellers.phone', 'travellers.alt_phone', 'travellers.email', 'travellers.location', 'travellers.arrival_airport', 'travellers.destination', 'travellers.address', 'travellers.airline', 'travellers.arrival_date', 'travellers.original_bag_space', 'travellers.used_space',  'travellers.available_space', 'travellers.referred_by', 'travellers.status', 'travellers.date_added');
    var $column_search = array('travellers.travel_date', 'travellers.fullname', 'travellers.phone', 'travellers.alt_phone', 'travellers.email', 'travellers.location', 'travellers.arrival_airport', 'travellers.destination', 'travellers.address', 'travellers.airline', 'travellers.arrival_date', 'travellers.original_bag_space', 'travellers.used_space',  'travellers.available_space', 'travellers.referred_by', 'travellers.status', 'travellers.date_added');
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
        foreach ($this->column_search as $item) {
            if ($search_value !== '') {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
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

    public function get_records($destination = null)
    {
        // This line runs the query builder that includes sorting and searching
        $this->the_query();

        $this->db->where('travellers.status', 'Approved');
        $this->db->where('travellers.travel_date >=', date('Y-m-d'));

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
        $this->db->where('travellers.status', 'Approved');
        $this->db->where('travellers.travel_date >=', date('Y-m-d'));

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
        $this->db->where('travellers.status', 'Approved');
        $this->db->where('travellers.travel_date >=', date('Y-m-d'));

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

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#remove_bag' . $traveller->id . '"> <i class="las la-minus" style="color: red"></i> <i class="las la-briefcase" style="color: red"></i> </i> &nbsp; Remove Bag Space </a></p>

		<p><a type="button" href="' . base_url('admin_travellers/recycle_traveller/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-recycle" style="color: blue"></i> &nbsp; Recycle Traveller </a></p>

        <hr>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#offline' . $traveller->id . '"> <i class="las la-file-invoice" style="color: grey"></i> &nbsp; Offline Booking </a></p>

        ' . $bag_lock_status . '

		<hr />

		<p><a type="button" href="' . base_url('admin_travellers/unapprove_traveller/' . $traveller->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-ban" style="color: red"></i> &nbsp; Unapprove Traveller </a></p>

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
        $original_spaces = kilogram(); // Assuming this helper exists
        $user_options = $this->get_approved_user_options();

        // Generate Available Space Options HTML
        $original_space = '<option selected value="' . $traveller->original_bag_space . '">' . $traveller->original_bag_space . ' KG</option>';
        if (!empty($original_spaces)) {
            foreach ($original_spaces as $space) {
                // so we generate standard options here.
                $original_space .= '<option value="' . $space . '">' . $space . ' KG</option>';
            }
        }

        // Return the HTML String
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

            <div class="modal fade" id="add_bag' . $traveller->id . '" role="dialog">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="pull-right">
                                <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                            </div>
                            <h4 class="modal-title">Add Original Bag Space: ' . $traveller->original_bag_space . 'KG</h4>
                        </div>

                        ' . form_open_multipart('admin_travellers/add_traveller_bag_space/' . $traveller->id, 'id="update_bag_form"') . '

                        <div class="modal-body">

                            <div class="form-group">
                                <label class="form-control-label">Select Bag Space</label>
                                <br>
                                <select class="form-control select2-user" name="selected_space" required>
                                    ' . $original_space . '
                                </select>
                            </div>
                            <br>
                            <small>Selected space will be added to the original bag space</small>
                        </div>

                        <div class="modal-footer">
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

            <div class="modal fade" id="remove_bag' . $traveller->id . '" role="dialog">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="pull-right">
                                <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                            </div>
                            <h4 class="modal-title">Remove Original Bag Space: ' . $traveller->original_bag_space . 'KG</h4>
                        </div>

                        ' . form_open_multipart('admin_travellers/remove_traveller_bag_space/' . $traveller->id, 'id="update_bag_form"') . '

                        <div class="modal-body">

                            <div class="form-group">
                                <label class="form-control-label">Select Bag Space</label>
                                <br>
                                <select class="form-control select2-user" name="selected_space" required>
                                    ' . $original_space . '
                                </select>
                            </div>
                            <br>
                            <small>Selected space will be added to the original bag space</small>
                        </div>

                        <div class="modal-footer">
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

            <div class="modal fade" id="offline' . $traveller->id . '" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content modal-widths">
                        <div class="modal-header">
                            <div class="pull-right">
                                <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                            </div>
                            <h4 class="modal-title">Update offline booking: ' . $traveller->fullname . '</h4>
                        </div>

                        ' . form_open_multipart('admin_travellers/add_offline_booking/' . $traveller->id, 'id="offline_booking_form"') . '

                        <div class="modal-body">

                            <div class="form-group">
                                <label class="form-control-label">Select SMB User *</label>
                                <select name="user_id" id="user_id_' . $traveller->id . '" class="form-control select2-user" required>
                                    <option value="">-- Select User --</option>
                                    ' . $user_options . '
                                </select>
                            </div>

                            <hr>
                            <h5 class="mt-3"><strong>Agent Details</strong></h5>

                            <div class="form-check mb-2">
                                <input class="form-check-input autofill-agent" type="checkbox" id="autofill-agent-' . $traveller->id . '">
                                <label class="form-check-label" for="autofill-agent-' . $traveller->id . '">
                                    Fill with selected SMB User details
                                </label>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <label>Full Name *</label>
                                    <br>
                                    <input type="text" name="agent_name" class="form-controls" required>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <label>Email *</label>
                                    <br>
                                    <input type="email" name="agent_email" class="form-controls" required>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <label>Phone *</label>
                                    <br>
                                    <input type="text" name="agent_phone" class="form-controls" required>
                                </div>
                                <div class="col-lg-12 mb-2">
                                    <label>Address *</label>
                                    <br>
                                    <input type="text" name="agent_address" class="form-controls" required>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <label>City *</label>
                                    <br>
                                    <input type="text" name="agent_locality" class="form-controls" required>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <label>Postal Code *</label>
                                    <br>
                                    <input type="text" name="agent_postcode" class="form-controls" required>
                                </div>
                            </div>

                            <hr>
                            <h5 class="mt-3"><strong>Receiver Details</strong></h5>

                            <div class="form-check mb-2">
                                <input class="form-check-input autofill-receiver" type="checkbox" id="autofill-receiver-' . $traveller->id . '">
                                <label class="form-check-label" for="autofill-receiver-' . $traveller->id . '">
                                    Fill with selected SMB User details
                                </label>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label>Full Name *</label>
                                    <br>
                                    <input type="text" name="receiver_name" class="form-controls" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label>Email *</label>
                                    <input type="email" name="receiver_email" class="form-controls" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label>Phone *</label>
                                    <input type="text" name="receiver_phone" class="form-controls" required>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label>Address *</label>
                                    <br>
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
                            <h5 class="mt-3"><strong>Bag Space Details</strong></h5>
                            <div class="form-group mt-3">
                                <label>How much Bag Space was bought? *</label>
                                <br>
                                <select class="form-control select2container" name="selected_space" required>
                                    ' . $bag_space_options . '
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="mt-3">
                                <button type="submit" class="btn btn-md btn-primary">
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

    //                     <h4 class="modal-title">Update offline booking: ' . $y->fullname . '</h4>
    //                 </div>

    //                 ' . form_open_multipart('admin_travellers/add_offline_booking/' . $y->id, 'id="offline_booking_form"') . '

    //                 <div class="modal-body">

    //                     <div class="form-group">
    //                         <label class="form-control-label">Select SMB User *</label>
    //                         <select name="user_id" id="user_id_' . $id . '" class="form-control select2-user" required>
    //                             <option value="">-- Select User --</option>
    //                             ' . $user_options . '
    //                         </select>
    //                     </div>

    // 					<hr>
    //                     <h5 class="mt-3"><strong>Agent Details</strong></h5>

    //                     <div class="form-check mb-2">
    //                         <input class="form-check-input autofill-agent" type="checkbox" id="autofill-agent-' . $id . '">
    //                         <label class="form-check-label" for="autofill-agent-' . $id . '">
    //                             Fill with selected SMB User details
    //                         </label>
    //                     </div>

    //                     <div class="row">
    //                         <div class="col-lg-12 mb-2">
    //                             <label>Full Name *</label>
    // 							<br>
    //                             <input type="text" name="agent_name" class="form-controls" required>
    //                         </div>
    //                         <div class="col-lg-6 mb-2">
    //                             <label>Email *</label>
    // 							<br>
    //                             <input type="email" name="agent_email" class="form-controls" required>
    //                         </div>
    //                         <div class="col-lg-6 mb-2">
    //                             <label>Phone *</label>
    // 							<br>
    //                             <input type="text" name="agent_phone" class="form-controls" required>
    //                         </div>
    //                         <div class="col-lg-12 mb-2">
    //                             <label>Address *</label>
    // 							<br>
    //                             <input type="text" name="agent_address" class="form-controls" required>
    //                         </div>
    //                         <div class="col-lg-6 mb-2">
    //                             <label>City *</label>
    // 							<br>
    //                             <input type="text" name="agent_locality" class="form-controls" required>
    //                         </div>
    //                         <div class="col-lg-6 mb-2">
    //                             <label>Postal Code *</label>
    // 							<br>
    //                             <input type="text" name="agent_postcode" class="form-controls" required>
    //                         </div>
    //                     </div>

    // 					<hr>
    //                     <h5 class="mt-3"><strong>Receiver Details</strong></h5>

    //                     <div class="form-check mb-2">
    //                         <input class="form-check-input autofill-receiver" type="checkbox" id="autofill-receiver-' . $id . '">
    //                         <label class="form-check-label" for="autofill-receiver-' . $id . '">
    //                             Fill with selected SMB User details
    //                         </label>
    //                     </div>

    //                     <div class="row">
    //                         <div class="col-md-12 mb-2">
    //                             <label>Full Name *</label>
    // 							<br>
    //                             <input type="text" name="receiver_name" class="form-controls" required>
    //                         </div>
    //                         <div class="col-md-6 mb-2">
    //                             <label>Email *</label>
    //                             <input type="email" name="receiver_email" class="form-controls" required>
    //                         </div>
    //                         <div class="col-md-6 mb-2">
    //                             <label>Phone *</label>
    //                             <input type="text" name="receiver_phone" class="form-controls" required>
    //                         </div>
    //                         <div class="col-md-12 mb-2">
    //                             <label>Address *</label>
    // 							<br>
    //                             <input type="text" name="receiver_address" class="form-controls" required>
    //                         </div>
    //                         <div class="col-md-6 mb-2">
    //                             <label>City *</label>
    //                             <input type="text" name="receiver_locality" class="form-controls" required>
    //                         </div>
    //                         <div class="col-md-6 mb-2">
    //                             <label>Postal Code *</label>
    //                             <input type="text" name="receiver_postcode" class="form-controls" required>
    //                         </div>
    //                     </div>

    // 					<hr>
    //                     <h5 class="mt-3"><strong>Bag Space Details</strong></h5>
    //                     <div class="form-group mt-3">
    //                         <label>How much Bag Space was bought? *</label>
    //                         <br>
    //                         <select class="form-control select2container" name="selected_space" required>
    //                             ' . $bag_space_options . '
    //                         </select>
    //                     </div>
    //                 </div>

    // 				<div class="modal-footer">

    //                     <div class="mt-3">
    //                         <button type="submit" id="send_mail_btn" class="btn btn-md btn-primary">
    //                             <span id="btn_text">Update Traveller</span>
    //                             <span id="loading_icon" style="display: none;"><i class="las la-spinner la-spin"></i></span>
    //                         </button>
    //                     </div>

    // 				</div>

    //                 ' . form_close() . '

    //             </div>
    //         </div>
    //     </div>';
    // }


    public function modals($traveller)
    {
        $modal_delete_confirm = modal_delete_confirm($traveller->id, $traveller->fullname, 'travellers', 'admin_travellers/delete_traveller');
        return $this->modal_options($traveller) .
            $modal_delete_confirm;
    }
}
