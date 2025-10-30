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
    var $column_order = array(null, null, 'travel_date', 'fullname', 'phone', 'alt_phone', 'email', 'location', 'arrival_airport', 'destination', 'address', 'airline', 'arrival_date', 'original_bag_space', 'used_space',  'available_space', 'referred_by', 'status', 'date_added'); //set column field database for datatable orderable
    var $column_search = array('travel_date', 'fullname', 'phone', 'alt_phone', 'email', 'location', 'arrival_airport', 'destination', 'address', 'airline', 'arrival_date', 'original_bag_space', 'used_space',  'available_space', 'referred_by', 'status', 'date_added'); //set column field database for datatable searchable 
    var $order = array('travel_date' => 'desc');

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

    public function get_records($destination = null)
    {
        $this->db->from('travellers');
        $this->db->where('status', 'approved');
        $this->db->where('travel_date >=', date('Y-m-d'));

        if (!empty($destination)) {
            $this->db->where('destination', $destination);
        }

        if (!empty($_POST['search']['value'])) {
            $this->db->like('fullname', $_POST['search']['value']);
            $this->db->or_like('email', $_POST['search']['value']);
        }

        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);

        $query = $this->db->get();
        return $query->result();
    }


    public function count_all_records($destination = null)
    {
        $this->db->from('travellers');
        $this->db->where('status', 'approved');
        $this->db->where('travel_date >=', date('Y-m-d'));

        if (!empty($destination)) {
            $this->db->where('destination', $destination);
        }
        return $this->db->count_all_results();
    }

    public function count_filtered_records($destination = null)
    {
        $this->db->from('travellers');
        $this->db->where('status', 'approved');
        $this->db->where('travel_date >=', date('Y-m-d'));

        if (!empty($destination)) {
            $this->db->where('destination', $destination);
        }

        if (!empty($_POST['search']['value'])) {
            $this->db->like('fullname', $_POST['search']['value']);
            $this->db->or_like('email', $_POST['search']['value']);
        }

        return $this->db->count_all_results();
    }


    public function actions($id)
    {
        $y = $this->common_model->get_traveller_details_by_id($id);

        if ($y->bag_locked == 0) {
            $bag_lock_status = '<p><a type="button" href="' . base_url('admin_travellers/lock_traveller_bag/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-briefcase" style="color: green"></i> &nbsp; Lock Bag </a></p>';
        } else {
            $bag_lock_status = '<p><a type="button" href="' . base_url('admin_travellers/unlock_traveller_bag/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-lock-open" style="color: orange"></i> &nbsp; Unlock Bag </a></p>';
        }

        return '<p><a type="button" href="' . base_url('admin_travellers/traveller_profile/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-user" style="color: green"></i> &nbsp; View Traveller </a></p>

		<p><a type="button" href="' . base_url('admin_travellers/update_traveller/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-pencil" style="color: blue"></i> &nbsp; Update Traveller </a></p>
		
		<p><a type="button" href="' . base_url('admin_travellers/recycle_traveller/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-recycle" style="color: blue"></i> &nbsp; Recycle Traveller </a></p>

        <hr>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#offline' . $id . '"> <i class="fa fa-file-invoice" style="color: grey"></i> &nbsp; Offline Booking </a></p>

        ' . $bag_lock_status . '

		<hr />

		<p><a type="button" href="' . base_url('admin_travellers/unapprove_traveller/' . $id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="fa fa-ban" style="color: red"></i> &nbsp; Unapprove Traveller </a></p>

		<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $id . '"> <i class="fa fa-trash" style="color: red"></i> &nbsp; Delete </a></p>';
    }


    public function options($id)
    {
        return '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $id . '" title="Options"> <i class="fa fa-navicon"></i> </a></div>';
    }


    private function generate_bag_space_options($selected_value, $id)
    {
        $traveller_details = $this->common_model->get_traveller_details_by_id($id);
        $max_space = (int) $traveller_details->available_space;

        $options = '<option value="">Select</option>';
        for ($i = 1; $i <= $max_space; $i++) {
            $selected = ($i == $selected_value) ? 'selected' : '';
            $options .= '<option value="' . $i . '" ' . $selected . ' data-max-space="' . $max_space . '">' . $i . 'KG</option>';
        }

        return $options;
    }


    // public function modal_options($id)
    // {
    //     $y = $this->common_model->get_traveller_details_by_id($id);
    //     $bag_space_options = $this->generate_bag_space_options($y->available_space, $id);
    //     return '<div class="modal fade" id="options' . $id . '" role="dialog">
    //                 <div class="modal-dialog">
    //                     <div class="modal-content modal-width">
    //                         <div class="modal-header">
    //                             <div class="pull-right">
    //                                 <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
    //                             </div>
    //                             <h4 class="modal-title">Actions: ' . $y->fullname . '</h4>
    //                         </div><!--/.modal-header-->
    //                         <div class="modal-body">'
    //         . $this->actions($id) .
    //         '</div>
    //                     </div>
    //                 </div>
    //             </div>

    //             <div class="modal fade" id="offline' . $id . '" role="dialog">
    //                 <div class="modal-dialog">
    //                     <div class="modal-content modal-width">
    //                         <div class="modal-header">
    //                             <div class="pull-right">
    //                                 <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
    //                             </div>
    //                             <h4 class="modal-title">Update offline booking: ' . $y->fullname . '</h4>
    //                         </div>

    //                         ' . form_open_multipart('admin_travellers/add_offline_booking/' . $y->id, 'id="submit_button"', 'target="_blank"') . '

    //                         <div class="modal-body">
    //                             <div class="row">
    //                                 <div class="col-lg-6" style="margin-bottom: 10px;">  
    //                                     <div class="">
    //                                         <label class="form-control-label">First Name *</label>
    //                                         <br>
    //                                         <input type="text" name="firstname" value="' . set_value('firstname') . '" class="form-control" required />
    //                                     </div>  
    //                                 </div>

    //                                 <div class="col-lg-6" style="margin-bottom: 10px;">  
    //                                     <div class="">
    //                                         <label class="form-control-label">Last Name *</label>
    //                                         <br>
    //                                         <input type="text" name="lastname" value="' . set_value('lastname') . '" class="form-control" />
    //                                     </div>  
    //                                 </div>

    //                                 <div class="col-lg-12" style="margin-bottom: 10px;">  
    //                                     <div class="">
    //                                         <label class="form-control-label">Email *</label>
    //                                         <br>
    //                                         <input type="text" name="email" value="' . set_value('email') . '" class="form-control !tw-w-[200px]" required />
    //                                     </div>  
    //                                 </div>

    //                                 <div class="col-lg-12" style="margin-bottom: 10px;">  
    //                                     <div class="">
    //                                         <label class="form-control-label">How much Bag space was bought? *</label>
    //                                         <br>
    //                                         <select class="form-control !tw-w-[200px]" name="selected_space" required>
    //                                             ' . $bag_space_options . '
    //                                         </select>
    //                                     </div>
    //                                 </div>
    //                             </div>

    //                             <div class="pull-left">
    //                                 <button type="submit" id="send_mail_btn" class="btn btn-sm btn-primary">
    //                                     <span id="btn_text">Update Traveller</span>
    //                                     <span id="loading_icon" style="display: none;"><i class="fa fa-spinner fa-spin"></i></span>
    //                                 </button>
    //                             </div>
    //                         </div>

    //                         ' . form_close() . '

    //                         <div class="modal-footer">

    //                         </div>


    //                     </div>
    //                 </div>
    //             </div>';
    // }

    public function modal_options($id)
    {
        $y = $this->common_model->get_traveller_details_by_id($id);
        $bag_space_options = $this->generate_bag_space_options($y->available_space, $id);

        // Fetch all approved smb users
        $users = $this->common_model->get_approved_users(); // Create this method if you don't have it

        // **RECOMMENDED FIX for Alphabetical Order**
        // Sort the $users array by firstname
        usort($users, function ($a, $b) {
            return strcmp($a->firstname, $b->firstname);
        });

        $user_options = '';
        foreach ($users as $u) {
            $user_options .= '<option value="' . $u->id . '">' . htmlspecialchars($u->firstname . ' ' . $u->lastname . ' (' . $u->email . ')') . '</option>';
        }

        return '
        <div class="modal fade" id="options' . $id . '" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content modal-width">
                    <div class="modal-header">
                        <div class="pull-right">
                            <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                        </div>
                        <h4 class="modal-title">Actions: ' . $y->fullname . '</h4>
                    </div>
                    <div class="modal-body">' . $this->actions($id) . '</div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="offline' . $id . '" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content modal-width">
                    <div class="modal-header">
                        <div class="pull-right">
                            <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                        </div>
                        <h4 class="modal-title">Update offline booking: ' . $y->fullname . '</h4>
                    </div>

                    ' . form_open_multipart('admin_travellers/add_offline_booking/' . $y->id, 'id="offline_booking_form"') . '

                    <div class="modal-body">

                        <!-- SMB USER -->
                        <div class="col-md-6 mb-2">
                            <div class="form-group">
                                <label class="form-control-label">Select SMB User *</label>
                                <select name="user_id" id="user_id_' . $id . '" class="form-control select2-user" required>
                                    <option value="">-- Select User --</option>
                                    ' . $user_options . '
                                </select>
                            </div>
                        </div>

						<hr>
                        <!-- AGENT DETAILS -->
                        <h5 class="mt-3"><strong>Agent Details</strong></h5>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label>Full Name *</label>
								<br>
                                <input type="text" name="agent_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>Email *</label>
                                <input type="email" name="agent_email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>Phone *</label>
                                <input type="text" name="agent_phone" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label>Address *</label>
								<br>
                                <input type="text" name="agent_address" class="form-control" required>
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>City *</label>
                                <input type="text" name="agent_locality" class="form-control" required>
                            </div>
                            <div class="col-lg-6 mb-2">
                                <label>Postal Code *</label>
                                <input type="text" name="agent_postcode" class="form-control" required>
                            </div>
                        </div>

						<hr>
                        <!-- RECEIVER DETAILS -->
                        <h5 class="mt-3"><strong>Receiver Details</strong></h5>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label>Full Name *</label>
								<br>
                                <input type="text" name="receiver_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>Email *</label>
                                <input type="email" name="receiver_email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>Phone *</label>
                                <input type="text" name="receiver_phone" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label>Address *</label>
								<br>
                                <input type="text" name="receiver_address" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>City *</label>
                                <input type="text" name="receiver_locality" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>Postal Code *</label>
                                <input type="text" name="receiver_postcode" class="form-control" required>
                            </div>
                        </div>

						<hr>
                        <!-- BAG SPACE SELECTION -->
                        <h5 class="mt-3"><strong>Bag Space Details</strong></h5>
                        <div class="form-group mt-3">
                            <label>How much Bag Space was bought? *</label>
                            <select class="form-control" name="selected_space" required>
                                ' . $bag_space_options . '
                            </select>
                        </div>
                    </div>

					<div class="modal-footer">

                        <div class="mt-3">
                            <button type="submit" id="send_mail_btn" class="btn btn-sm btn-primary">
                                <span id="btn_text">Update Traveller</span>
                                <span id="loading_icon" style="display: none;"><i class="fa fa-spinner fa-spin"></i></span>
                            </button>
                        </div>

					</div>

                    ' . form_close() . '

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
