<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

class Shipping_model extends \MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'shipping_records';
        $this->primary_cols = array('id', 'booking_id', 'tracking_id');
        $this->load->model('booking_read_model');
        $this->load->model('shipping_read_model');
    }

    public function create_shipping_record($bookingId, array $payload)
    {
        $booking = $this->booking_read_model->get_booking_details_by_id($bookingId);
        if (!$booking) {
            return array('status' => false, 'msg' => 'Invalid booking selected.');
        }
        if (payment_status_normalize($booking->payment_status) !== 'completed') {
            return array('status' => false, 'msg' => 'Shipping can only be created for completed bookings.');
        }

        if ($this->shipping_read_model->get_shipping_record_by_booking_id($bookingId)) {
            return array('status' => false, 'msg' => 'A shipping record already exists for this booking.');
        }

        $data = $this->prepareShippingRecordData($booking, $payload);
        if (!$data['staff_name']) {
            return array('status' => false, 'msg' => 'Please select a staff member.');
        }
        if ($data['carrier_tracking_id'] === '') {
            return array('status' => false, 'msg' => 'Please enter the carrier tracking ID.');
        }
        if (!in_array($data['status'], shipping_status_creation_options(), true)) {
            return array('status' => false, 'msg' => 'Select a valid shipping status.');
        }
        if ($this->carrierTrackingExists($data['courier'], $data['carrier_tracking_id'])) {
            return array('status' => false, 'msg' => 'That carrier tracking ID is already in use for the selected courier.');
        }

        $this->db->trans_start();
        $this->db->insert('shipping_records', $data);
        $this->syncBookingDeliveryStatus($booking->tracking_id, $data['status']);
        $this->insertShippingHistory($booking->tracking_id, $data['status'], $payload['tracking_note'] ?? '', true);
        $this->db->trans_complete();

        $this->clearShippingCaches();

        if (!$this->db->trans_status()) {
            return array('status' => false, 'msg' => 'Unable to create the shipping record right now.');
        }

        return array(
            'status' => true,
            'msg' => 'Shipping record created successfully.',
            'record' => $this->shipping_read_model->get_shipping_record_by_booking_id($bookingId),
        );
    }

    public function update_shipping_record($bookingId, array $payload)
    {
        $booking = $this->booking_read_model->get_booking_details_by_id($bookingId);
        if (!$booking) {
            return array('status' => false, 'msg' => 'Invalid booking selected.');
        }
        if (payment_status_normalize($booking->payment_status) !== 'completed') {
            return array('status' => false, 'msg' => 'Shipping can only be updated for completed bookings.');
        }

        $existing = $this->shipping_read_model->get_shipping_record_by_booking_id($bookingId);
        if (!$existing) {
            return array('status' => false, 'msg' => 'No shipping record exists for this booking yet.');
        }

        $data = $this->prepareShippingRecordData($booking, $payload, $existing);
        if (!$data['staff_name']) {
            return array('status' => false, 'msg' => 'Please select a staff member.');
        }
        if ($data['carrier_tracking_id'] === '') {
            return array('status' => false, 'msg' => 'Please enter the carrier tracking ID.');
        }
        if (!shipping_status_transition_allowed($existing->status, $data['status'], true)) {
            return array('status' => false, 'msg' => 'Shipping status cannot move backwards.');
        }
        if ($this->carrierTrackingExists($data['courier'], $data['carrier_tracking_id'], $bookingId)) {
            return array('status' => false, 'msg' => 'That carrier tracking ID is already in use for the selected courier.');
        }

        $statusChanged = shipping_status_normalize($existing->status) !== $data['status'];
        $note = trim((string) ($payload['tracking_note'] ?? ''));

        $this->db->trans_start();
        $this->db->where('booking_id', $bookingId);
        $this->db->update('shipping_records', $data);
        $this->syncBookingDeliveryStatus($booking->tracking_id, $data['status']);
        if ($statusChanged || $note !== '') {
            $this->insertShippingHistory($booking->tracking_id, $data['status'], $note, false);
        }
        $this->db->trans_complete();

        $this->clearShippingCaches();

        if (!$this->db->trans_status()) {
            return array('status' => false, 'msg' => 'Unable to update the shipping record right now.');
        }

        return array(
            'status' => true,
            'msg' => 'Shipping record updated successfully.',
            'record' => $this->shipping_read_model->get_shipping_record_by_booking_id($bookingId),
        );
    }

    public function add_tracking_update($bookingId, $status, $heading = '', $body = '')
    {
        $booking = $this->booking_read_model->get_booking_details_by_id($bookingId);
        if (!$booking) {
            return array('status' => false, 'msg' => 'Invalid booking selected.');
        }
        if (payment_status_normalize($booking->payment_status) !== 'completed') {
            return array('status' => false, 'msg' => 'Shipping updates are only allowed for completed bookings.');
        }

        $record = $this->shipping_read_model->get_shipping_record_by_booking_id($bookingId);
        if (!$record) {
            return array('status' => false, 'msg' => 'Create the shipping record before adding updates.');
        }

        $normalizedStatus = shipping_status_normalize($status);
        if (!shipping_status_transition_allowed($record->status, $normalizedStatus)) {
            return array('status' => false, 'msg' => 'Select a valid next shipping status.');
        }
        $updateHeading = trim((string) $heading);
        $updateBody = trim((string) $body);

        if ($updateHeading === '') {
            $updateHeading = $normalizedStatus;
        }

        if ($updateBody === '') {
            $updateBody = 'Shipping status updated to ' . $normalizedStatus . '.';
        }

        $this->db->trans_start();
        $this->db->where('booking_id', $bookingId);
        $this->db->update('shipping_records', array(
            'status' => $normalizedStatus,
            'date_updated' => date('Y-m-d H:i:s'),
        ));
        $this->syncBookingDeliveryStatus($booking->tracking_id, $normalizedStatus);
        $this->insertShippingHistory($booking->tracking_id, $normalizedStatus, $updateBody, false, $updateHeading);
        $this->db->trans_complete();

        $this->clearShippingCaches();

        if (!$this->db->trans_status()) {
            return array('status' => false, 'msg' => 'Unable to add the tracking update right now.');
        }

        return array('status' => true, 'msg' => 'Tracking update added successfully.');
    }

    public function delete_shipping_record($bookingId)
    {
        $booking = $this->booking_read_model->get_booking_details_by_id($bookingId);
        if (!$booking) {
            return array('status' => false, 'msg' => 'Invalid booking selected.');
        }
        if (payment_status_normalize($booking->payment_status) !== 'completed') {
            return array('status' => false, 'msg' => 'Shipping records are only available for completed bookings.');
        }

        $record = $this->shipping_read_model->get_shipping_record_by_booking_id($bookingId);
        if (!$record) {
            return array('status' => false, 'msg' => 'Shipping record not found.');
        }

        $this->db->trans_start();
        $this->db->delete('shipping_records', array('booking_id' => $bookingId));
        $this->syncBookingDeliveryStatus($booking->tracking_id, 'In Transit');
        $this->db->delete('shipping', array('tracking_id' => $booking->tracking_id));
        $this->db->trans_complete();

        $this->clearShippingCaches();

        if (!$this->db->trans_status()) {
            return array('status' => false, 'msg' => 'Unable to delete the shipping record right now.');
        }

        return array('status' => true, 'msg' => 'Shipping record deleted successfully.');
    }

    public function add_shipping_to_db()
    {
        $trackingId = $this->input->post('tracking_id', true);
        $heading = ucfirst(trim((string) $this->input->post('heading', true)));
        $body = ucfirst(trim((string) $this->input->post('body', true)));
        $deliveryStatus = shipping_status_normalize($this->input->post('heading', true));
        $this->insertShippingHistory($trackingId, $deliveryStatus, $body, false, $heading);
        $this->syncBookingDeliveryStatus($trackingId, $deliveryStatus);
        $this->clearShippingCaches();
    }

    public function get_tracking_payload($trackingId)
    {
        $shippings = $this->shipping_read_model->get_shipping_by_tracking_id($trackingId);

        if (empty($shippings)) {
            return array(
                'status' => false,
                'msg' => 'No Shipping Info, please check again later.'
            );
        }

        $data = array(
            'status' => true,
            'data' => array()
        );

        foreach ($shippings as $shipping) {
            $data['data'][] = array(
                'tracking_id' => $shipping->tracking_id,
                'icon_text' => shipping_icon($shipping->icon_text),
                'heading' => $shipping->heading,
                'description' => $shipping->body,
                'delivery_status' => shipping_status_normalize($shipping->delivery_status),
                'date_added' => x_datetime_full($shipping->date_added)
            );
        }

        return $data;
    }

    public function edit_shipping($historyId)
    {
        $heading = ucfirst(trim((string) $this->input->post('heading', true)));
        $body = ucfirst(trim((string) $this->input->post('body', true)));
        $deliveryStatus = shipping_status_normalize($this->input->post('delivery_status', true));
        $iconText = $this->resolveShippingIconText($deliveryStatus);

        $data = array(
            'heading' => $heading,
            'body' => $body,
            'delivery_status' => $deliveryStatus,
            'icon_text' => $iconText,
        );

        $this->db->where('id', $historyId);
        $this->db->update('shipping', $data);

        $historyRow = $this->db->where('id', $historyId)->get('shipping')->row();
        if ($historyRow) {
            $this->syncBookingDeliveryStatus($historyRow->tracking_id, $deliveryStatus);
        }

        $this->clearShippingCaches();
    }

    public function delete_shipping($historyId)
    {
        $historyRow = $this->db->where(array('id' => $historyId))->get('shipping')->row();
        if (!$historyRow) {
            return;
        }

        $this->db->delete('shipping', array('id' => $historyId));
        $latest = $this->shipping_read_model->get_shipping_by_tracking_id_row($historyRow->tracking_id);
        $status = $latest ? shipping_status_normalize($latest->delivery_status) : 'In Transit';
        $this->syncBookingDeliveryStatus($historyRow->tracking_id, $status);
        $this->clearShippingCaches();
    }

    private function prepareShippingRecordData($booking, array $payload, $existing = null)
    {
        $defaults = $this->buildShippingDefaults($booking);
        $selectedStaffId = (int) ($payload['staff_admin_id'] ?? 0);
        $staff = $selectedStaffId ? $this->getStaffMeta($selectedStaffId) : null;

        return array(
            'booking_id' => (int) $booking->id,
            'tracking_id' => $booking->tracking_id,
            'carrier_tracking_id' => trim((string) ($payload['carrier_tracking_id'] ?? $existing->carrier_tracking_id ?? '')),
            'pickup_address' => trim((string) ($payload['pickup_address'] ?? $existing->pickup_address ?? $defaults['pickup_address'])),
            'dropoff_address' => trim((string) ($payload['dropoff_address'] ?? $existing->dropoff_address ?? $defaults['dropoff_address'])),
            'pickup_country' => trim((string) ($payload['pickup_country'] ?? $existing->pickup_country ?? $defaults['pickup_country'])),
            'courier' => $this->normalizeCourier($payload['courier'] ?? $existing->courier ?? null),
            'staff_admin_id' => $staff ? (int) $staff->id : null,
            'staff_name' => $staff ? $staff->name : trim((string) ($existing->staff_name ?? '')),
            'status' => shipping_status_normalize($payload['status'] ?? $existing->status ?? 'Awaiting Collection'),
        );
    }

    private function buildShippingDefaults($booking)
    {
        return array(
            'pickup_address' => $this->composeAddress(
                $booking->agent_address ?? '',
                $booking->agent_locality ?? '',
                $booking->agent_postcode ?? ''
            ),
            'dropoff_address' => $this->composeAddress(
                $booking->receiver_address ?? '',
                $booking->receiver_locality ?? '',
                $booking->receiver_postcode ?? ''
            ),
            'pickup_country' => $this->inferPickupCountry($booking),
        );
    }

    private function composeAddress($address, $locality, $postcode)
    {
        $parts = array_filter(array_map('trim', array($address, $locality, $postcode)));
        return implode(', ', $parts);
    }

    private function inferPickupCountry($booking)
    {
        $haystack = strtolower(trim(implode(' ', array_filter(array(
            $booking->agent_address ?? '',
            $booking->agent_locality ?? '',
            $booking->traveller_current_state ?? '',
            $booking->traveller_arrival_state ?? '',
        )))));

        if ($haystack === '') {
            return 'Nigeria';
        }

        $countryMap = array(
            'united kingdom' => 'United Kingdom',
            'uk' => 'United Kingdom',
            'england' => 'United Kingdom',
            'london' => 'United Kingdom',
            'manchester' => 'United Kingdom',
            'canada' => 'Canada',
            'toronto' => 'Canada',
            'calgary' => 'Canada',
            'edmonton' => 'Canada',
            'ottawa' => 'Canada',
            'nigeria' => 'Nigeria',
            'lagos' => 'Nigeria',
            'abuja' => 'Nigeria',
            'port harcourt' => 'Nigeria',
            'ibadan' => 'Nigeria',
        );

        foreach ($countryMap as $needle => $country) {
            if (strpos($haystack, $needle) !== false) {
                return $country;
            }
        }

        return ucfirst(trim((string) ($booking->traveller_current_state ?? 'Nigeria')));
    }

    private function normalizeCourier($courier)
    {
        $courier = trim((string) $courier);
        if ($courier === '') {
            return 'Not Assigned';
        }

        return $courier;
    }

    private function getStaffMeta($adminId)
    {
        $fields = 'id, name, email, role';
        if ($this->db->field_exists('can_manage_shipping', 'admins')) {
            $fields .= ', can_manage_shipping';
        }

        $staff = $this->db
            ->select($fields)
            ->where('id', (int) $adminId)
            ->get('admins')
            ->row();

        return admin_shipping_access_allowed($staff) ? $staff : null;
    }

    private function syncBookingDeliveryStatus($trackingId, $status)
    {
        $normalized = shipping_status_to_booking_delivery($status);
        $this->db->where('tracking_id', $trackingId);
        $this->db->update('bookings', array('delivery_status' => $normalized));
    }

    private function carrierTrackingExists($courier, $carrierTrackingId, $excludeBookingId = 0)
    {
        $this->db->from('shipping_records');
        $this->db->where('courier', trim((string) $courier));
        $this->db->where('carrier_tracking_id', trim((string) $carrierTrackingId));
        if ((int) $excludeBookingId > 0) {
            $this->db->where('booking_id !=', (int) $excludeBookingId);
        }
        return $this->db->count_all_results() > 0;
    }

    private function insertShippingHistory($trackingId, $status, $body = '', $isFirstRecord = false, $headingOverride = '')
    {
        $normalizedStatus = shipping_status_normalize($status);
        $heading = trim((string) $headingOverride);
        if ($heading === '') {
            $heading = $isFirstRecord ? 'Shipping record created' : $normalizedStatus;
        }

        $body = trim((string) $body);
        if ($body === '') {
            $body = $isFirstRecord
                ? 'Shipping support has been created for this booking.'
                : 'Shipping status updated to ' . $normalizedStatus . '.';
        }

        $this->db->insert('shipping', array(
            'tracking_id' => $trackingId,
            'heading' => ucfirst($heading),
            'body' => ucfirst($body),
            'delivery_status' => $normalizedStatus,
            'icon_text' => $this->resolveShippingIconText($normalizedStatus),
        ));
    }

    private function resolveShippingIconText($status)
    {
        $status = shipping_status_normalize($status);

        if ($status === 'Completed') {
            return 'delivered';
        }

        if ($status === 'In Transit') {
            return 'in_transit';
        }

        return 'pending';
    }

    private function clearShippingCaches()
    {
        $this->shipping_read_model->clearShippingCountCaches();
        $this->booking_read_model->clearBookingCountCaches();
    }
}
