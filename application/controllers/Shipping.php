<?php
defined('BASEPATH') or die('Direct access not allowed');

class Shipping extends MY_Controller
{
    private function jsonResponse($status, $message = '', array $extra = array())
    {
        echo json_encode(array_merge(array(
            'status' => $status,
            'msg' => $message,
            'csrf_hash' => $this->security->get_csrf_hash(),
        ), $extra));
    }

    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted();
        $this->admin_role_restricted(array('super_admin', 'customer_support'));
        $this->load->model('shipping_model');
        $this->load->model('shipping_read_model');
        $this->load->model('booking_read_model');
        $this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
    }

    public function index()
    {
        $summary = $this->shipping_read_model->get_shipping_dashboard_summary();
        $data['tiles'] = array(
            array('label' => 'Need Help Bookings', 'value' => $summary->need_help_bookings, 'icon' => 'fa fa-life-ring', 'class' => 'custom-bg-blue'),
            array('label' => 'Shipping Records', 'value' => $summary->created_records, 'icon' => 'fa fa-truck', 'class' => 'custom-bg-blue'),
            array('label' => 'In Transit', 'value' => $summary->in_transit_records, 'icon' => 'fa fa-clock-o', 'class' => 'custom-bg-blue'),
            array('label' => 'Completed', 'value' => $summary->completed_records, 'icon' => 'fa fa-check-circle', 'class' => 'custom-bg-blue'),
        );
        $data['staff_options'] = $this->shipping_read_model->get_staff_options();
        $data['courier_options'] = shipping_courier_options();

        $inner_page_title = 'Shipping Records (' . $summary->need_help_bookings . ')';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/shipping/all_shipping', $data);
        $this->admin_footer();
    }

    public function records_ajax()
    {
        $this->load->model('ajax/shipping/shipping_records_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();

        foreach ($list as $row) {
            $shippingExists = !empty($row->shipping_record_id);
            $actions = $this->renderActionMenu($row->booking_id, $row->tracking_id, $shippingExists);
            $staff = $this->renderStaffCell($row->staff_name ?? '', $row->staff_role ?? '');
            $user = $this->renderPersonCard($row->user_fullname, $row->user_phone ?: '', $row->user_email);
            $traveller = $this->renderPersonCard($row->traveller_name, $row->traveller_contact, $row->traveller_email);
            $pickupAddress = $row->pickup_address ?: $this->composeAddress($row->agent_address, $row->agent_locality, $row->agent_postcode);
            $dropoffAddress = $row->dropoff_address ?: $this->composeAddress($row->receiver_address, $row->receiver_locality, $row->receiver_postcode);
            $country = $row->pickup_country ?: $this->inferPickupCountryFromRow($row);
            $courier = $row->courier ? html_escape($row->courier) : '<span class="text-muted">Not Assigned</span>';
            $status = shipping_status_badge($row->status ?: 'In Transit');
            $dateAdded = $row->date_added ? x_datetime_full($row->date_added) : x_datetime_full($row->booking_date_added);

            $data[] = array(
                $actions,
                $staff,
                $user,
                $traveller,
                nl2br(html_escape($pickupAddress)),
                nl2br(html_escape($dropoffAddress)),
                html_escape($country),
                $courier,
                '<code>' . html_escape($row->tracking_id) . '</code>',
                $status,
                $dateAdded,
            );
        }

        echo json_encode(array(
            'draw' => (int) $this->input->post('draw'),
            'recordsTotal' => $this->current_model->count_all_records(),
            'recordsFiltered' => $this->current_model->count_filtered_records(),
            'data' => $data,
            'csrf_hash' => $this->security->get_csrf_hash(),
        ));
    }

    public function search_bookings_ajax()
    {
        $query = trim((string) $this->input->post('query', true));
        $results = $this->shipping_read_model->search_bookings_for_shipping($query);
        $payload = array();

        foreach ($results as $row) {
            $payload[] = array(
                'booking_id' => (int) $row->id,
                'tracking_id' => $row->tracking_id,
                'need_help' => $row->need_help,
                'shipping_exists' => !empty($row->shipping_record_id),
                'user' => trim($row->user_fullname),
                'traveller' => trim($row->traveller_name),
                'pickup_address' => $this->composeAddress($row->agent_address, $row->agent_locality, $row->agent_postcode),
                'dropoff_address' => $this->composeAddress($row->receiver_address, $row->receiver_locality, $row->receiver_postcode),
                'pickup_country' => $this->inferPickupCountryFromRow($row),
                'date_added' => x_datetime_full($row->date_added),
            );
        }

        $this->jsonResponse(true, '', array('results' => $payload));
    }

    public function shipping_context_ajax($bookingId)
    {
        $context = $this->shipping_read_model->get_booking_shipping_context((int) $bookingId);
        if (!$context) {
            $this->jsonResponse(false, 'Booking not found.');
            return;
        }

        $defaults = array(
            'booking_id' => (int) $context->id,
            'tracking_id' => $context->tracking_id,
            'user' => $context->user_fullname ?: '',
            'user_phone' => $context->user_phone ?: '',
            'traveller' => $context->traveller_name ?: '',
            'pickup_address' => $context->pickup_address ?: $this->composeAddress($context->agent_address, $context->agent_locality, $context->agent_postcode),
            'dropoff_address' => $context->dropoff_address ?: $this->composeAddress($context->receiver_address, $context->receiver_locality, $context->receiver_postcode),
            'pickup_country' => $context->pickup_country ?: $this->inferPickupCountryFromRow($context),
            'courier' => $context->courier ?: 'Not Assigned',
            'staff_admin_id' => $context->staff_admin_id ? (int) $context->staff_admin_id : 0,
            'status' => shipping_status_normalize($context->status ?: 'In Transit'),
            'shipping_exists' => !empty($context->shipping_date_added),
        );

        $this->jsonResponse(true, '', array('context' => $defaults));
    }

    public function create_shipping_ajax()
    {
        $bookingId = (int) $this->input->post('booking_id');
        $this->form_validation->set_rules('booking_id', 'Booking', 'trim|required|integer');
        $this->form_validation->set_rules('pickup_address', 'Pickup Address', 'trim|required');
        $this->form_validation->set_rules('dropoff_address', 'Drop-off Address', 'trim|required');
        $this->form_validation->set_rules('pickup_country', 'Pickup Country', 'trim|required');
        $this->form_validation->set_rules('courier', 'Courier', 'trim|required');
        $this->form_validation->set_rules('staff_admin_id', 'Staff', 'trim|required|integer');
        $this->form_validation->set_rules('status', 'Status', 'trim|required');

        if (!$this->form_validation->run()) {
            $this->jsonResponse(false, strip_tags(validation_errors()));
            return;
        }

        $result = $this->shipping_model->create_shipping_record($bookingId, $this->collectShippingPayload());
        $this->jsonResponse(!empty($result['status']), $result['msg'] ?? 'Unable to create shipping record.', $result);
    }

    public function edit_shipping_ajax($bookingId)
    {
        $bookingId = (int) $bookingId;
        $this->form_validation->set_rules('pickup_address', 'Pickup Address', 'trim|required');
        $this->form_validation->set_rules('dropoff_address', 'Drop-off Address', 'trim|required');
        $this->form_validation->set_rules('pickup_country', 'Pickup Country', 'trim|required');
        $this->form_validation->set_rules('courier', 'Courier', 'trim|required');
        $this->form_validation->set_rules('staff_admin_id', 'Staff', 'trim|required|integer');
        $this->form_validation->set_rules('status', 'Status', 'trim|required');

        if (!$this->form_validation->run()) {
            $this->jsonResponse(false, strip_tags(validation_errors()));
            return;
        }

        $result = $this->shipping_model->update_shipping_record($bookingId, $this->collectShippingPayload());
        $this->jsonResponse(!empty($result['status']), $result['msg'] ?? 'Unable to update shipping record.', $result);
    }

    public function update_status_ajax($bookingId)
    {
        $bookingId = (int) $bookingId;
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
        $this->form_validation->set_rules('heading', 'Heading', 'trim');
        $this->form_validation->set_rules('body', 'Update Note', 'trim|required|min_length[2]');

        if (!$this->form_validation->run()) {
            $this->jsonResponse(false, strip_tags(validation_errors()));
            return;
        }

        $result = $this->shipping_model->add_tracking_update(
            $bookingId,
            $this->input->post('status', true),
            $this->input->post('heading', true),
            $this->input->post('body', true)
        );
        $this->jsonResponse(!empty($result['status']), $result['msg'] ?? 'Unable to add shipping update.', $result);
    }

    public function view_shipping($bookingId)
    {
        $record = $this->shipping_read_model->get_shipping_record_view((int) $bookingId);
        if (!$record) {
            $this->session->set_flashdata('status_msg_error', 'Shipping record not found for that booking.');
            redirect('shipping');
        }

        $data['record'] = $record;
        $data['history'] = $this->shipping_read_model->get_shipping_details_by_tracking_id($record->tracking_id);
        $data['staff_options'] = $this->shipping_read_model->get_staff_options();
        $data['courier_options'] = shipping_courier_options();
        $pageTitle = 'Shipping Details: ' . $record->tracking_id;
        $this->admin_header($pageTitle, $pageTitle);
        $this->load->view('admin/shipping/view_shipping', $data);
        $this->admin_footer();
    }

    public function delete_shipping($bookingId)
    {
        $result = $this->shipping_model->delete_shipping_record((int) $bookingId);
        $this->session->set_flashdata(!empty($result['status']) ? 'status_msg' : 'status_msg_error', $result['msg'] ?? 'Unable to delete shipping record.');
        redirect('shipping');
    }

    private function collectShippingPayload()
    {
        return array(
            'pickup_address' => $this->input->post('pickup_address', true),
            'dropoff_address' => $this->input->post('dropoff_address', true),
            'pickup_country' => $this->input->post('pickup_country', true),
            'courier' => $this->input->post('courier', true),
            'staff_admin_id' => $this->input->post('staff_admin_id', true),
            'status' => $this->input->post('status', true),
            'tracking_note' => $this->input->post('tracking_note', true),
        );
    }

    private function renderActionMenu($bookingId, $trackingId, $shippingExists)
    {
        $modalId = 'shippingOptions' . (int) $bookingId;
        $button = '<div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#' . $modalId . '" title="Options"><i class="fa fa-navicon"></i></a></div>';

        $actions = '';
        if ($shippingExists) {
            $actions .= '<p><a type="button" href="' . base_url('shipping/view_shipping/' . $bookingId) . '" class="btn btn-default btn-sm btn-block action-btn clickable"><i class="fa fa-eye" style="color: green"></i> &nbsp; View Shipping</a></p>';
            $actions .= '<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable open-edit-shipping" data-booking-id="' . $bookingId . '" data-dismiss="modal"><i class="fa fa-pencil" style="color: blue"></i> &nbsp; Edit Shipping</a></p>';
            $actions .= '<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable open-status-shipping" data-booking-id="' . $bookingId . '" data-dismiss="modal"><i class="fa fa-refresh" style="color: orange"></i> &nbsp; Update Status</a></p>';
            $actions .= '<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $bookingId . '"><i class="fa fa-trash" style="color: red"></i> &nbsp; Delete</a></p>';
        } else {
            $actions .= '<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable open-create-shipping" data-booking-id="' . $bookingId . '" data-dismiss="modal"><i class="fa fa-plus" style="color: green"></i> &nbsp; Add Shipping</a></p>';
        }

        $modal = '
        <div class="modal fade" id="' . $modalId . '" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content modal-width">
                    <div class="modal-header">
                        <div class="pull-right">
                            <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
                        </div>
                        <h4 class="modal-title">Shipping Actions: ' . html_escape($trackingId) . '</h4>
                    </div>
                    <div class="modal-body">' . $actions . '</div>
                </div>
            </div>
        </div>';

        $deleteModal = $shippingExists ? modal_delete_confirm($bookingId, $trackingId, 'shipping record', 'shipping/delete_shipping') : '';

        return $button . $modal . $deleteModal;
    }

    private function renderPersonCard($name, $phone, $email)
    {
        $lines = array();
        $lines[] = '<strong>' . html_escape($name ?: 'N/A') . '</strong>';

        if ($phone !== '') {
            $lines[] = '<span><i class="fa fa-phone"></i> ' . html_escape($phone) . '</span>';
        }

        if ($email !== '') {
            $lines[] = '<span><i class="fa fa-envelope"></i> ' . html_escape($email) . '</span>';
        }

        return '<div class="admin-shipping-person">' . implode('<br>', $lines) . '</div>';
    }

    private function renderStaffCell($name, $role)
    {
        if (!$name) {
            return '<span class="text-muted">Not Assigned</span>';
        }

        $designation = $role ? ucwords(str_replace('_', ' ', $role)) : 'Staff';

        return '<div class="admin-shipping-person"><strong>' . html_escape($name) . '</strong><br><span class="text-muted">' . html_escape($designation) . '</span></div>';
    }

    private function composeAddress($address, $locality, $postcode)
    {
        $parts = array_filter(array_map('trim', array($address, $locality, $postcode)));
        return implode(', ', $parts);
    }

    private function inferPickupCountryFromRow($row)
    {
        $haystack = strtolower(trim(implode(' ', array_filter(array(
            $row->agent_address ?? '',
            $row->agent_locality ?? '',
            $row->traveller_current_state ?? '',
            $row->traveller_arrival_state ?? '',
        )))));

        if (strpos($haystack, 'canada') !== false || strpos($haystack, 'toronto') !== false || strpos($haystack, 'calgary') !== false || strpos($haystack, 'ottawa') !== false) {
            return 'Canada';
        }

        if (strpos($haystack, 'uk') !== false || strpos($haystack, 'united kingdom') !== false || strpos($haystack, 'england') !== false || strpos($haystack, 'london') !== false || strpos($haystack, 'manchester') !== false) {
            return 'United Kingdom';
        }

        return 'Nigeria';
    }
}
