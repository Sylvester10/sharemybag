<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation =====
Name: Admin_bookings
Role: Controller
Description: Controls access to Booking pages and functions in admin panel
Models: Bookings_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023

UPDATED:
  - add_parcel_ajax()   : Add a parcel to a completed booking. Recalculates totals, commission, traveller space. Sends email to traveller.
  - remove_parcel_ajax(): Remove a parcel from a completed booking. Recalculates totals, commission, traveller space. Sends email to traveller.
  - Role restrictions added to finances-adjacent views (super_admin only).
*/



class Admin_bookings extends MY_Controller
{
    private function parcel_response($status, $message, $extra = array())
    {
        $payload = array_merge(array(
            'status' => $status,
            'msg' => $message,
            'csrf_hash' => $this->security->get_csrf_hash(),
        ), $extra);

        echo json_encode($payload);
    }

    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted(); //allow only logged in users to access this class
        $this->load->model('bookings_model');
        $this->load->model('shipping_model');
        $this->load->model('travellers_model');
        $this->load->model('booking_read_model');
        $this->load->model('traveller_read_model');
        $this->load->model('finance_read_model');
        $this->load->model('shipping_read_model');
        $this->load->library('booking_presenter');
        $this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
    }



    /* ========== All Bookings ========== */
    public function index()
    {
        $inner_page_title = 'All Bookings (' . $this->booking_read_model->count_all_bookings() . ')';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/bookings/all_bookings');
        $this->admin_footer();
    }


    public function all_bookings_ajax()
    {
        $this->load->model('ajax/bookings/bookings_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {
            $traveller_destination = $y->traveller_destination ?: '';

            // traveller details
            $traveller_details = '<i class="las la-user"></i> ' . $y->traveller_name . '<br />
							<i class="las la-phone"></i> ' . $y->traveller_contact . '<br />
							<i class="las la-map-marker-alt"></i> ' . $y->traveller_drop_address1 . '<br />
							<i class="las la-calendar"></i> ' . x_date($y->traveller_drop_date1) . '<br />
							<i class="las la-plane-arrival"></i> ' . $y->traveller_arrival_state . ', ' . $traveller_destination;

            $user_details = $y->payment_method == 'offline'
                ? 'N/A'
                : '<i class="las la-user"></i> ' . $y->user_fullname . '<br />
							<i class="las la-at"></i> ' . $y->user_email;

            $agent_details = '<i class="las la-user"></i> ' . $y->agent_name . '<br />
							<i class="las la-phone"></i> ' . $y->agent_phone . '<br />
							<i class="las la-at"></i> ' . $y->agent_email . '<br />
							<i class="las la-map-marker-alt"></i> ' . $y->agent_address . ', ' . $y->agent_locality . ', ' . $y->agent_postcode . '';

            // receiver details
            $receiver_details = $y->payment_method == 'offline'
                ? 'N/A'
                : '<i class="las la-user"></i> ' . $y->receiver_name . ' <br />
							<i class="las la-phone"></i> ' . $y->receiver_phone . ' <br />
							<i class="las la-at"></i> ' . $y->receiver_email . ' <br />
							<i class="las la-map-marker-alt"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

            list($item_details, $metrics) = $this->booking_presenter->render_item_table($y->items, $y->currency);

            $item_sizes = $this->booking_presenter->format_item_size($metrics, $y->selected_space);
            $traveller_commission = booking_stored_traveller_commission($y);
            $commission = $this->booking_presenter->format_commission($y->currency, $y->payment_status, $traveller_commission);
            $payment_status = $this->booking_presenter->format_payment_status_badge($y->payment_status);
            $payment_method = $this->booking_presenter->format_payment_method($y->payment_method, 'Offline');
            $total_amount = $this->booking_presenter->format_total_amount_summary($y->currency, $y->total_amount, $payment_method);

            $new_tag = ($y->new == 0) ? ' <span class="badge badge-success">NEW</span>' : '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = x_datetime_full($y->date_added) . $new_tag;
            $row[] = $y->need_help;
            $row[] = $traveller_details;
            $row[] = $commission;
            $row[] = $user_details;
            $row[] = $agent_details;
            $row[] = $receiver_details;
            $row[] = $item_details;
            $row[] = $item_sizes;
            $row[] = $total_amount;
            $row[] = $payment_status;
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );
        echo json_encode($output);
    }


    public function completed_bookings()
    {
        $inner_page_title = 'Completed Bookings (' . $this->booking_read_model->count_completed_bookings() . ')';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/bookings/completed_bookings');
        $this->admin_footer();
    }


    public function completed_bookings_ajax()
    {
        $this->load->model('ajax/bookings/completed_bookings_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {
            $traveller_destination = $y->traveller_destination ?: '';

            $traveller_details = '<i class="las la-user"></i> ' . $y->traveller_name . '<br />
							<i class="las la-phone"></i> ' . $y->traveller_contact . '<br />
							<i class="las la-map-marker-alt"></i> ' . $y->traveller_drop_address1 . '<br />
							<i class="las la-calendar"></i> ' . x_date($y->traveller_drop_date1) . '<br />
							<i class="las la-plane-arrival"></i> ' . $y->traveller_arrival_state . ', ' . $traveller_destination;

            $user_details = $y->payment_method == 'offline'
                ? '<i class="las la-user"></i> ' . $y->user_fullname . '<br />
				<i class="las la-at"></i> ' . $y->user_email . ' <br /> <i class="las la-exclamation-circle"></i> This is an offline booking'
                : '<i class="las la-user"></i> ' . $y->user_fullname . '<br />
				<i class="las la-at"></i> ' . $y->user_email;

            $agent_details = '<i class="las la-user"></i> ' . $y->agent_name . '<br />
							<i class="las la-phone"></i> ' . $y->agent_phone . '<br />
							<i class="las la-at"></i> ' . $y->agent_email . '<br />
							<i class="las la-map-marker-alt"></i> ' . $y->agent_address . ', ' . $y->agent_locality . ', ' . $y->agent_postcode . '';

            $receiver_details = '<i class="las la-user"></i> ' . $y->receiver_name . ' <br />
							<i class="las la-phone"></i> ' . $y->receiver_phone . ' <br />
							<i class="las la-at"></i> ' . $y->receiver_email . ' <br />
							<i class="las la-map-marker-alt"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

            // Parcel management buttons (customer_support and super_admin)
            $admin_role = $this->get_admin_role();
            $parcel_actions_html = '';
            if (in_array($admin_role, ['super_admin', 'customer_support'])) {
                $parcel_actions_html .= '<div class="mt-1">'
                    . '<button type="button" class="btn btn-xs btn-success me-1" onclick="openAddParcelModal(' . $y->id . ')">'
                    . '<i class="las la-plus"></i> Add Parcel</button>'
                    . '<button type="button" class="btn btn-xs btn-danger" onclick="openRemoveParcelModal(' . $y->id . ', \'' . htmlspecialchars(addslashes($y->items), ENT_QUOTES, 'UTF-8') . '\')">'
                    . '<i class="las la-minus"></i> Remove Parcel</button>'
                    . '</div>';
            }
            list($item_details, $metrics) = $this->booking_presenter->render_item_table($y->items, $y->currency, $parcel_actions_html);

            $item_sizes = $this->booking_presenter->format_item_size($metrics, $y->selected_space);
            $traveller_commission = booking_stored_traveller_commission($y);
            $commission = $this->booking_presenter->format_commission($y->currency, $y->payment_status, $traveller_commission);
            $payment_status = $this->booking_presenter->format_payment_status_badge($y->payment_status);
            $payment_method = $this->booking_presenter->format_payment_method($y->payment_method, 'Offline');
            $total_amount = $this->booking_presenter->format_total_amount_summary($y->currency, $y->total_amount, $payment_method);

            $new_tag = ($y->new == 0) ? ' <span class="badge badge-success">NEW</span>' : '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = x_datetime_full($y->date_added) . $new_tag;
            $row[] = $y->need_help;
            $row[] = $traveller_details;
            $row[] = $commission;
            $row[] = $user_details;
            $row[] = $agent_details;
            $row[] = $receiver_details;
            $row[] = $item_details;
            $row[] = $item_sizes;
            $row[] = $total_amount;
            $row[] = $payment_status;
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );
        echo json_encode($output);
    }


    public function canceled_bookings()
    {
        $inner_page_title = 'Canceled Bookings (' . $this->booking_read_model->count_canceled_bookings() . ')';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/bookings/canceled_bookings');
        $this->admin_footer();
    }

    public function canceled_bookings_ajax()
    {
        $this->load->model('ajax/bookings/canceled_bookings_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {
            $traveller_destination = $y->traveller_destination ?: '';

            $traveller_details = '<i class="las la-user"></i> ' . $y->traveller_name . '<br />
							<i class="las la-phone"></i> ' . $y->traveller_contact . '<br />
							<i class="las la-map-marker-alt"></i> ' . $y->traveller_drop_address1 . '<br />
							<i class="las la-calendar"></i> ' . x_date($y->traveller_drop_date1) . '<br />
							<i class="las la-plane-arrival"></i> ' . $y->traveller_arrival_state . ', ' . $traveller_destination;

            $user_details = $y->payment_method == 'offline'
                ? 'N/A'
                : '<i class="las la-user"></i> ' . $y->user_fullname . '<br />
							<i class="las la-at"></i> ' . $y->user_email;

            $agent_details = '<i class="las la-user"></i> ' . $y->agent_name . '<br />
							<i class="las la-phone"></i> ' . $y->agent_phone . '<br />
							<i class="las la-at"></i> ' . $y->agent_email . '<br />
							<i class="las la-map-marker-alt"></i> ' . $y->agent_address . ', ' . $y->agent_locality . ', ' . $y->agent_postcode . '';

            $receiver_details = '<i class="las la-user"></i> ' . $y->receiver_name . ' <br />
							<i class="las la-phone"></i> ' . $y->receiver_phone . ' <br />
							<i class="las la-at"></i> ' . $y->receiver_email . ' <br />
							<i class="las la-map-marker-alt"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

            list($item_details, $metrics) = $this->booking_presenter->render_item_table($y->items, $y->currency);
            $item_sizes = $this->booking_presenter->format_item_size($metrics, $y->selected_space);
            $traveller_commission = booking_stored_traveller_commission($y);
            $commission = $this->booking_presenter->format_commission($y->currency, $y->payment_status, $traveller_commission);
            $payment_status = $this->booking_presenter->format_payment_status_badge($y->payment_status);
            $payment_method = $this->booking_presenter->format_payment_method($y->payment_method, 'Offline');
            $total_amount = $this->booking_presenter->format_total_amount_summary($y->currency, $y->total_amount, $payment_method);

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = x_datetime_full($y->date_added);
            $row[] = $traveller_details;
            $row[] = $commission;
            $row[] = $user_details;
            $row[] = $agent_details;
            $row[] = $receiver_details;
            $row[] = $y->need_help;
            $row[] = $item_details;
            $row[] = $item_sizes;
            $row[] = $total_amount;
            $row[] = $payment_status;
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );
        echo json_encode($output);
    }


    /* ADD PARCEL TO EXISTING BOOKING */
    public function add_parcel_ajax()
    {
        $this->admin_role_restricted(['super_admin', 'customer_support']);

        $booking_id  = (int) $this->input->post('booking_id');
        $item_name   = trim($this->input->post('item_name'));
        $category    = trim($this->input->post('category'));
        $item_size   = (float) $this->input->post('item_size');
        $notes       = trim($this->input->post('notes'));

        if (!$booking_id || !$item_name || !$category || $item_size <= 0) {
            $this->parcel_response(false, 'All fields are required and size must be greater than 0.');
            return;
        }

        $result = $this->bookings_model->add_parcel($booking_id, $item_name, $category, $item_size, $notes);
        $this->parcel_response(!empty($result['status']), $result['msg'] ?? 'Unable to add parcel.', $result);
    }


    /* REMOVE PARCEL FROM EXISTING BOOKING */
    public function remove_parcel_ajax()
    {
        $this->admin_role_restricted(['super_admin', 'customer_support']);

        $booking_id = (int) $this->input->post('booking_id');
        $item_index = (int) $this->input->post('item_index'); // 0-based index
        $notes      = trim($this->input->post('notes'));

        if (!$booking_id) {
            $this->parcel_response(false, 'Invalid booking.');
            return;
        }

        $result = $this->bookings_model->remove_parcel($booking_id, $item_index, $notes);
        $this->parcel_response(!empty($result['status']), $result['msg'] ?? 'Unable to remove parcel.', $result);
    }


    /* ========== View Booking ========== */
    public function view_booking($id)
    {
        $this->check_data_exists($id, 'id', 'bookings', 'admin');
        $bookings_details = $this->booking_read_model->get_booking_details_by_id($id);
        $booking_reference = !empty($bookings_details->tracking_id)
            ? $bookings_details->tracking_id
            : '#' . (int) $bookings_details->id;
        $page_title = 'Booking Info: ' . $booking_reference;
        $this->admin_header($page_title, $page_title);
        $data['y'] = $bookings_details;
        $this->load->view('admin/bookings/view_booking', $data);
        $this->admin_footer();
    }


    /* ========== View Shipping ========== */
    public function view_shipping($booking_id)
    {
        $this->check_data_exists($booking_id, 'id', 'bookings', 'admin');

        $booking_details = $this->booking_read_model->get_booking_details_by_id($booking_id);
        $page_title = 'Shipping Info: ' . $booking_details->agent_name;
        $this->admin_header($page_title, $page_title);

        $shipping_details = $this->shipping_read_model->get_shipping_details_by_tracking_id($booking_details->tracking_id);

        $data['shipping_details'] = $shipping_details;
        $this->load->view('admin/bookings/view_booking', $data);
        $this->admin_footer();
    }


    public function edit_shipping($booking_id)
    {
        $this->form_validation->set_rules('heading', 'Heading', 'trim|required');
        $this->form_validation->set_rules('body', 'Body', 'trim|min_length[2]|max_length[500]|required');
        $this->form_validation->set_rules('delivery_status', 'Delivery Status', 'trim|required');

        if ($this->form_validation->run()) {
            $this->shipping_model->edit_shipping($booking_id);
            $this->session->set_flashdata('status_msg', "Shipping updated successfully.");
            redirect($this->agent->referrer());
        } else {
            echo validation_errors();
        }
    }


    public function update_new_status($id)
    {
        $this->bookings_model->update_new_status($id);
        $this->session->set_flashdata('status_msg', 'Booking Marked as Seen.');
        redirect($this->agent->referrer());
    }


    public function confirm_booking($id)
    {
        $this->bookings_model->confirm_booking($id);
        $this->session->set_flashdata('status_msg', 'Booking Confirmed Successfully.');
        redirect($this->agent->referrer());
    }


    public function cancel_booking($id)
    {
        if ($this->bookings_model->cancel_booking($id)) {
            $this->session->set_flashdata('status_msg', 'Booking Cancelled and Bag Space Reverted.');
        }

        redirect($this->agent->referrer());
    }


    public function delete_booking($id)
    {
        $this->check_data_exists($id, 'id', 'bookings', 'admin');
        if ($this->bookings_model->delete_booking($id)) {
            $this->session->set_flashdata('status_msg', 'Booking data deleted successfully.');
        } else {
            $this->session->set_flashdata('status_msg_error', 'Booking deletion failed.');
        }
        redirect($this->agent->referrer());
    }


    public function delete_shipping($id)
    {
        $this->check_data_exists($id, 'id', 'shipping', 'admin');
        $this->shipping_model->delete_shipping($id);
        $this->session->set_flashdata('status_msg', 'Shipping data deleted successfully.');
        redirect($this->agent->referrer());
    }


    public function bulk_actions_booking()
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
                $this->bookings_model->bulk_actions_booking($selected_rows);
            } else {
                $this->session->set_flashdata('status_msg_error', 'No item selected.');
            }
        } else {
            $this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
        }
        redirect($this->agent->referrer());
    }

}
