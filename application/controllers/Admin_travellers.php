<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation =====
Name: Admin_travellers
Role: Controller
Description: Controls access to Travellers pages and functions in admin panel
Models: Traveller_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023

UPDATED:
  - traveller_support: full access to travellers section
  - customer_support:  read-only access (view only, no add/edit/delete)
  - super_admin:       full access
*/



class Admin_travellers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted();
        // customer_support can't manage travellers directly
        // they can see bookings but not edit traveller listings
        $this->admin_role_restricted(['super_admin', 'traveller_support']);
        $this->load->model('travellers_model');
        $this->load->model('users_model');
        $this->load->model('user_read_model');
        $this->load->model('booking_read_model');
        $this->load->model('traveller_read_model');
        $this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
    }



    /* ========== Upcoming travellers ========== */
    public function index()
    {
        $inner_page_title = 'Upcoming Travellers (' . $this->traveller_read_model->count_active_approved_travellers() . ')';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/travellers/upcoming_travellers');
        $this->admin_footer();
    }


    public function upcoming_travellers_ajax()
    {
        $this->load->model('ajax/travellers/upcoming_travellers_ajax', 'current_model');

        $destination = $this->input->post('destination'); // New filter input

        $list = $this->current_model->get_records($destination); // Pass to model
        $data = array();
        foreach ($list as $y) {

            $itinerary_src = base_url('assets/itinerary/' . $y->itinerary_photo);
            $itinerary = traveller_itinerary_table_link($y->itinerary_photo, $itinerary_src, user_avatar);

            $referrer = $y->referrer_firstname ?: 'No Referral';

            $status = traveller_status_badge($y->status);
            $original_bag_space = "$y->original_bag_space KG";
            $used_space = empty($y->used_space) ? '0 KG' : "$y->used_space KG";
            $available_space = empty($y->available_space) ? '0 KG' : "$y->available_space KG";
            $arrival_date = ($y->arrival_date == '') ? 'No Information' : $y->arrival_date;

            $paymentTypes = [
                '£5_per_kg' => '£5 per kg',
                'guaranteed_£115' => 'Guaranteed £115 for 23kg'
            ];

            $payment_type = $paymentTypes[$y->payment_type] ?? 'None Selected';
            $bag_locked = ($y->bag_locked == 1) ? '<i class="las la-lock" style="color: red"></i>' : '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = x_date($y->travel_date);
            $row[] = $itinerary;
            $row[] = ucwords($y->fullname) . ' ' . $bag_locked;
            $row[] = $y->phone;
            $row[] = $y->alt_phone;
            $row[] = $y->email;
            $row[] = $y->area . ', ' . $y->current_state;
            $row[] = $y->arrival_airport;
            $row[] = traveller_destination_label($y->arrival_state, $y->destination, isset($y->destination_area) ? $y->destination_area : '');
            $row[] = $y->address;
            $row[] = $y->airline;
            $row[] = x_date($arrival_date);
            $row[] = $original_bag_space;
            $row[] = $used_space;
            $row[] = $available_space;
            $row[] = $referrer;
            // 			$row[] = $payment_type;
            $row[] = $status;
            $row[] = x_datetime_full($y->date_added);
            $data[] = $row;
        }
        $output = array(
            "draw" => (int) $this->input->post('draw'),
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );
        echo json_encode($output);
    }


    public function pending_travellers()
    {
        $inner_page_title = 'Pending Travellers';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/travellers/pending_travellers');
        $this->admin_footer();
    }


    public function pending_travellers_ajax()
    {
        $this->load->model('ajax/travellers/pending_travellers_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {

            $itinerary_src = base_url('assets/itinerary/' . $y->itinerary_photo);
            $itinerary = traveller_itinerary_table_link($y->itinerary_photo, $itinerary_src, user_avatar);

            $status = traveller_status_badge($y->status);

            $paymentTypes = [
                '£5_per_kg' => '£5 per kg',
                'guaranteed_£115' => 'Guaranteed £115 for 23kg'
            ];

            $payment_type = $paymentTypes[$y->payment_type] ?? 'None Selected';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = $itinerary;
            $row[] = ucfirst($y->fullname);
            $row[] = $y->phone;
            $row[] = $y->alt_phone;
            $row[] = $y->email;
            $row[] = $y->location;
            $row[] = $y->destination;
            $row[] = x_date($y->travel_date);
            $row[] = $status;
            $row[] = x_datetime_full($y->date_added);
            $data[] = $row;
        }
        $output = array(
            "draw" => (int) $this->input->post('draw'),
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );
        echo json_encode($output);
    }


    public function approved_travellers()
    {
        $inner_page_title = 'All Travellers';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/travellers/approved_travellers');
        $this->admin_footer();
    }


    public function approved_travellers_ajax()
    {
        $this->load->model('ajax/travellers/approved_travellers_ajax', 'current_model');

        $destination = $this->input->post('destination'); // New filter input

        $list = $this->current_model->get_records($destination); // Pass to model
        $data = array();
        foreach ($list as $y) {

            $itinerary_src = base_url('assets/itinerary/' . $y->itinerary_photo);
            $itinerary = traveller_itinerary_table_link($y->itinerary_photo, $itinerary_src, user_avatar);

            $referrer = $y->referrer_firstname ?: 'No Referral';

            $status = traveller_status_badge($y->status);
            $original_bag_space = "$y->original_bag_space KG";
            $used_space = empty($y->used_space) ? '0 KG' : "$y->used_space KG";
            $available_space = empty($y->available_space) ? '0 KG' : "$y->available_space KG";
            $arrival_date = ($y->arrival_date == '') ? 'No Information' : $y->arrival_date;

            $paymentTypes = [
                '£5_per_kg' => '£5 per kg',
                'guaranteed_£115' => 'Guaranteed £115 for 23kg'
            ];

            $payment_type = $paymentTypes[$y->payment_type] ?? 'None Selected';


            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = x_date($y->travel_date);
            $row[] = $itinerary;
            $row[] = ucfirst($y->fullname);
            $row[] = $y->phone;
            $row[] = $y->alt_phone;
            $row[] = $y->email;
            $row[] = $y->area . ', ' . $y->current_state;
            $row[] = $y->arrival_airport;
            $row[] = traveller_destination_label($y->arrival_state, $y->destination, isset($y->destination_area) ? $y->destination_area : '');
            $row[] = $y->address;
            $row[] = $y->airline;
            $row[] = x_date($arrival_date);
            $row[] = $original_bag_space;
            $row[] = $used_space;
            $row[] = $available_space;
            $row[] = $referrer;
            // 			$row[] = $payment_type;
            $row[] = $status;
            $row[] = x_datetime_full($y->date_added);
            $data[] = $row;
        }
        $output = array(
            "draw" => (int) $this->input->post('draw'),
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );
        echo json_encode($output);
    }


    public function unapproved_travellers()
    {
        redirect('admin_travellers/approved_travellers');
    }


    public function unapproved_travellers_ajax()
    {
        $this->load->model('ajax/travellers/unapproved_travellers_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {

            $itinerary_src = base_url('assets/itinerary/' . $y->itinerary_photo);
            $itinerary = user_avatar_table($y->itinerary_photo, $itinerary_src, user_avatar);

            $status = traveller_status_badge($y->status);
            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = $itinerary;
            $row[] = ucfirst($y->fullname);
            $row[] = $y->phone;
            $row[] = $y->alt_phone;
            $row[] = $y->email;
            $row[] = $y->location;
            $row[] = $y->destination;
            $row[] = x_date($y->travel_date);
            $row[] = $status;
            $row[] = x_date($y->date_added);
            $data[] = $row;
        }
        $output = array(
            "draw" => (int) $this->input->post('draw'),
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );
        echo json_encode($output);
    }


    public function update_traveller($id)
    {
        $this->check_data_exists($id, 'id', 'travellers', 'admin_travellers');
        $traveller_details = $this->traveller_read_model->get_traveller_details_by_id($id);
        $page_title = 'Update Traveller: ' . $traveller_details->fullname;
        $this->admin_header($page_title, $page_title);
        $data['y'] = $traveller_details;
        $this->load->view('admin/travellers/update_traveller', $data);
        $this->admin_footer();
    }


    public function update_traveller_ajax($id, $error = array('error' => ''))
    {
        //check travellers exists
        $this->check_data_exists($id, 'id', 'travellers', 'admin');
        // validation rules
        $this->form_validation->set_rules('fullname', 'Name', 'trim|min_length[2]|max_length[500]|required');
        $this->form_validation->set_rules('phone', 'Mobile', 'trim|required');
        $this->form_validation->set_rules(
            'email',
            'Email',
            'trim|required|valid_email',
            array('valid_email' => 'Enter a valid email.')
        );
        $this->form_validation->set_rules('travel_date', 'Travel Date', 'trim|required');
        $this->form_validation->set_rules('arrival_date', 'Arrival Date', 'trim|required');
        $this->form_validation->set_rules('location', 'Current Location', 'trim|required');
        $this->form_validation->set_rules('current_state', 'State', 'trim');
        $this->form_validation->set_rules('destination', 'Destination', 'trim|required');
        $this->form_validation->set_rules('arrival_airport', 'Arrival Airport', 'trim|required');
        $this->form_validation->set_rules('arrival_state', 'Final Destination', 'trim|required');
        $this->form_validation->set_rules('destination_area', 'Final Destination Area', 'trim|max_length[150]');
        $this->form_validation->set_rules('airline', 'Airline', 'required');
        $this->form_validation->set_rules('area', 'Area', 'trim|min_length[2]|max_length[100]');
        $this->form_validation->set_rules('address', 'Address', 'trim|min_length[2]|max_length[500]');
        $this->form_validation->set_rules('drop_area1', 'First Drop Off Area', 'trim|max_length[150]');
        $this->form_validation->set_rules('drop_area2', 'Last Drop Off Area', 'trim|max_length[150]');
        $this->form_validation->set_rules('available_space', 'Available Space', 'trim|required');
        $this->form_validation->set_rules('unwanted_items[]', 'Unwanted Items', 'trim');

        if (!$this->form_validation->run()) {
            $this->update_traveller($id);
            return;
        }

        if ($this->travellers_model->update_traveller($id)) {
            $this->session->set_flashdata('status_msg', "Traveller updated successfully.");
            redirect('admin_travellers');
            return;
        }
        $this->session->set_flashdata('status_msg_error', 'Traveller could not be updated');
        redirect('admin_travellers/update_traveller/' . $id);
    }


    public function traveller_profile($id)
    {
        $this->check_data_exists($id, 'id', 'travellers', 'admin_travellers');
        $traveller_details = $this->traveller_read_model->get_traveller_details_by_id($id);
        $page_title = 'Traveller Profile: ' . $traveller_details->fullname;
        $this->admin_header($page_title, $page_title);
        $data['y'] = $traveller_details;
        $data['booking_details'] = $this->traveller_read_model->get_booking_details_by_traveller_id($id);
        $this->load->view('admin/travellers/traveller_profile', $data);
        $this->admin_footer();
    }


    public function get_user_details($id)
    {
        // Ensure this is an AJAX request
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $user = $this->user_read_model->get_user_details_by_id($id);

        if ($user) {
            // Prepare a clean data array for JSON response
            $data = array(
                'fullname'    => $user->firstname . ' ' . $user->lastname,
                'email'       => $user->email,
                'phone'       => $user->number,    // Assuming 'number' is the phone field in 'users' table
                'address'     => $user->address,
                'city'        => $user->state,     // Mapping 'state' to 'city' for the form
                'postal_code' => $user->post_code  // Mapping 'post_code' to 'postal_code'
            );

            // Set content type to JSON and output the data
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
        } else {
            // Send a 404 response if user not found
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'User not found')));
        }
    }

    public function add_traveller_bag_space($id)
    {
        // Check traveller exists
        $this->check_data_exists($id, 'id', 'travellers', 'admin');

        // Validation rules
        $this->form_validation->set_rules('selected_space', 'Bag Space', 'trim|required|numeric');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('status_msg_error', validation_errors());
            redirect('admin_travellers'); // Or your edit page
            return;
        }

        // Pass the ID and the amount to ADD
        $space_to_add = $this->input->post('selected_space', TRUE);

        if ($this->travellers_model->add_traveller_bag_space($id, $space_to_add)) {
            $this->session->set_flashdata('status_msg', "Traveller data updated successfully.");
            redirect('admin_travellers');
            return;
        }

        $this->session->set_flashdata('status_msg_error', 'Traveller data could not be updated');
        redirect('admin_travellers');
    }

    public function remove_traveller_bag_space($id)
    {
        // Check traveller exists
        $this->check_data_exists($id, 'id', 'travellers', 'admin');

        // Validation rules
        $this->form_validation->set_rules('selected_space', 'Bag Space', 'trim|required|numeric');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('status_msg_error', validation_errors());
            redirect('admin_travellers'); // Or your edit page
            return;
        }

        // Pass the ID and the amount to ADD
        $space_to_add = $this->input->post('selected_space', TRUE);

        if ($this->travellers_model->remove_traveller_bag_space($id, $space_to_add)) {
            $this->session->set_flashdata('status_msg', "Traveller data updated successfully.");
            redirect('admin_travellers');
            return;
        }

        $this->session->set_flashdata('status_msg_error', 'Traveller data could not be updated');
        redirect('admin_travellers');
    }

    public function lock_traveller_bag($id)
    {
        $this->travellers_model->lock_traveller_bag($id);
        $this->session->set_flashdata('status_msg', 'Traveller Bag Updated.');
        redirect($this->agent->referrer());
    }

    public function unlock_traveller_bag($id)
    {
        $this->travellers_model->unlock_traveller_bag($id);
        $this->session->set_flashdata('status_msg', 'Traveller Bag Updated.');
        redirect($this->agent->referrer());
    }


    public function add_offline_booking($id)
    {
        $this->form_validation->set_rules('user_id', 'User', 'required');
        $this->form_validation->set_rules('agent_name', 'Agent Full Name', 'trim|required');
        $this->form_validation->set_rules('agent_email', 'Agent Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('agent_phone', 'Agent Phone', 'trim|required');
        $this->form_validation->set_rules('agent_address', 'Agent Address', 'trim|required');
        $this->form_validation->set_rules('agent_locality', 'Agent City', 'trim|required');
        $this->form_validation->set_rules('agent_postcode', 'Agent Postal Code', 'trim|required');
        $this->form_validation->set_rules('receiver_name', 'Receiver Full Name', 'trim|required');
        $this->form_validation->set_rules('receiver_email', 'Receiver Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('receiver_phone', 'Receiver Phone', 'trim|required');
        $this->form_validation->set_rules('receiver_address', 'Receiver Address', 'trim|required');
        $this->form_validation->set_rules('receiver_locality', 'Receiver City', 'trim|required');
        $this->form_validation->set_rules('receiver_postcode', 'Receiver Postal Code', 'trim|required');
        $this->form_validation->set_rules('selected_space', 'Selected Space', 'required');

        // **CRITICAL BUG FIX HERE**
        // You MUST check if validation passed before running the model.
        if ($this->form_validation->run()) {
            // Validation passed
            if ($this->users_model->add_offline_booking_to_db($id)) {
                $this->travellers_model->update_traveller_space($id);
                $this->session->set_flashdata('status_msg', "Offline booking data added successfully.");
            } else {
                $this->session->set_flashdata('error_msg', "Failed to add booking. Please try again.");
            }
        } else {
            // Validation failed
            $this->session->set_flashdata('error_msg', "Failed to add booking: " . validation_errors());
        }

        redirect($this->agent->referrer());
    }


    public function approve_traveller($id)
    {
        $this->travellers_model->approve_traveller($id);
        $this->session->set_flashdata('status_msg', 'Traveller Approved and notified by email.');
        redirect($this->agent->referrer());
    }


    public function unapprove_traveller($id)
    {
        $this->travellers_model->unapprove_traveller($id);
        $this->session->set_flashdata('status_msg', 'Traveller Unapproved.');
        redirect($this->agent->referrer());
    }


    /* DELETE — super_admin only */
    public function delete_traveller($id)
    {
        $this->admin_role_restricted(['super_admin']);
        $this->check_data_exists($id, 'id', 'travellers', 'admin');
        $this->travellers_model->delete_traveller($id);
        $this->session->set_flashdata('status_msg', 'Traveller Deleted.');
        redirect($this->agent->referrer());
    }


    public function recycle_traveller($id, $error = array('error' => ''))
    {
        $this->check_data_exists($id, 'id', 'travellers', 'admin');
        $travellers_details = $this->traveller_read_model->get_traveller_details_by_id($id);
        $page_title = 'Recycle Traveller: ' . $travellers_details->fullname;
        $this->admin_header($page_title, $page_title);
        $data['y'] = $travellers_details;
        $data['upload_error'] = $error;
        $this->load->view('admin/travellers/recycle_traveller', $data);
        $this->admin_footer();
    }


    public function recycle_traveller_ajax($id)
    {
        $this->check_data_exists($id, 'id', 'travellers', 'admin');

        $this->form_validation->set_rules('fullname', 'Name', 'trim|min_length[2]|max_length[500]|required');
        $this->form_validation->set_rules('phone', 'Mobile', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email', array('valid_email' => 'Enter a valid email.'));
        $this->form_validation->set_rules('travel_date', 'Travel Date', 'trim|required');
        $this->form_validation->set_rules('arrival_date', 'Arrival Date', 'trim|required');
        $this->form_validation->set_rules('location', 'Current Location', 'trim|required');
        $this->form_validation->set_rules('current_state', 'State', 'trim');
        $this->form_validation->set_rules('destination', 'Destination', 'trim|required');
        $this->form_validation->set_rules('arrival_airport', 'Arrival Airport', 'trim|required');
        $this->form_validation->set_rules('arrival_state', 'Final Destination', 'trim|required');
        $this->form_validation->set_rules('destination_area', 'Final Destination Area', 'trim|max_length[150]');
        $this->form_validation->set_rules('airline', 'Airline', 'required');
        $this->form_validation->set_rules('address', 'Address', 'trim|min_length[2]|max_length[500]');
        $this->form_validation->set_rules('drop_area1', 'First Drop Off Area', 'trim|max_length[150]');
        $this->form_validation->set_rules('drop_area2', 'Last Drop Off Area', 'trim|max_length[150]');
        $this->form_validation->set_rules('available_space', 'Available Space', 'trim|required');
        $this->form_validation->set_rules('unwanted_items[]', 'Unwanted Items', 'trim|required');

        $config = [
            'upload_path' => 'assets/itinerary',
            'allowed_types' => 'jpg|jpeg|png|pdf',
            'max_size' => 5024,
            'file_ext_tolower' => TRUE,
            'remove_spaces' => TRUE,
            'detect_mime' => TRUE,
        ];
        $this->load->library('upload', $config);

        if (!$this->form_validation->run()) {
            $this->recycle_traveller($id);
            return;
        }

        if (empty($_FILES['itinerary_photo']['name'])) {
            $this->session->set_flashdata('status_msg_error', 'Upload Itinerary');
            redirect('admin_travellers/recycle_traveller/' . $id);
            return;
        }

        $file_ext = pathinfo($_FILES['itinerary_photo']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '.' . $file_ext;
        $temp_name = $_FILES['itinerary_photo']['tmp_name'];

        if (!move_uploaded_file($temp_name, $config['upload_path'] . '/' . $new_name)) {
            $this->session->set_flashdata('status_msg_error', 'Failed to upload file.');
            redirect('admin_travellers/recycle_traveller/' . $id);
            return;
        }

        $itinerary_photo = $new_name;
        $thumbnail = generate_image_thumb($itinerary_photo, '100', '100');

        if ($this->travellers_model->recycle_traveller($id, $itinerary_photo, $thumbnail)) {
            $this->session->set_flashdata('status_msg', "Traveller recycled successfully.");
            redirect('admin_travellers');
            return;
        }
        $this->session->set_flashdata('status_msg_error', 'Traveller could not be recycled');
        redirect('admin_travellers/recycle_traveller/' . $id);
    }


    public function bulk_actions_traveller()
    {
        $this->form_validation->set_rules('check_bulk_action', 'Bulk Select', 'trim');
        $selected_rows = $this->input->post('check_bulk_action', TRUE);

        // Check if selected_rows is an array before counting
        if (is_array($selected_rows)) {
            $selected_rows_count = count($selected_rows);
        } else {
            $selected_rows_count = 0;
        }

        if ($this->form_validation->run()) {
            if ($selected_rows_count > 0) {
                $this->travellers_model->bulk_actions_traveller($selected_rows);
            } else {
                $this->session->set_flashdata('status_msg_error', 'No item selected.');
            }
        } else {
            $this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
        }
        redirect($this->agent->referrer());
    }
}
