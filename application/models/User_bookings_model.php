<?php
defined('BASEPATH') or exit('Direct access to script not allowed');


class User_bookings_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'bookings';
        $this->primary_cols = array('id');
        $this->load->model('booking_read_model');
        $this->load->model('traveller_read_model');
        $this->load->model('travellers_model');
        $this->load->model('finance_read_model');
        $this->load->model('shipping_read_model');
        $this->traveller_details = $this->traveller_read_model->get_traveller_details_by_id($this->session->id);
    }



    public function add_booking_to_db($id_photo, $selfie)
    {
        //generate and update tracking ID
        $tracking_id = generate_unique_tracking_id('bookings', 'tracking_id');

        $calculations = json_decode($this->input->post('price_calculations'));

        // ── EXPLICIT FIELD ALLOWLIST (SEC-007) ──
        $data = array(
            // Server-controlled
            'status'            => booking_status_normalize('Pending'),
            'delivery_status'   => delivery_status_normalize('Pending'),
            'tracking_id'       => $tracking_id,
            'id_photo'          => $id_photo,
            'selfie'            => $selfie,

            // Financial (from server-validated calculations)
            'insurance'       => isset($calculations->insurance)     ? (float) $calculations->insurance     : 0,
            'total_amount'    => isset($calculations->totalAmount)   ? round((float) $calculations->totalAmount, 2)   : 0,
            'sub_total'       => isset($calculations->subTotal)      ? round((float) $calculations->subTotal, 2)      : 0,
            'vat'             => isset($calculations->vat)           ? round((float) $calculations->vat, 2)           : 0,
            'service_charge'  => isset($calculations->serviceCharge) ? round((float) $calculations->serviceCharge, 2) : 0,
            'selected_space'  => isset($calculations->selectedSpace) ? (float) $calculations->selectedSpace : 0,
            'selected_price'  => isset($calculations->selectedPrice) ? round((float) $calculations->selectedPrice, 2) : 0,

            // Traveller details
            'traveller_id'              => (int) $this->input->post('traveller_id', TRUE),
            'traveller_name'            => $this->input->post('traveller_name', TRUE),
            'traveller_email'           => $this->input->post('traveller_email', TRUE),
            'traveller_contact'         => $this->input->post('traveller_contact', TRUE),
            'traveller_travel_date'     => $this->input->post('traveller_travel_date', TRUE),
            'traveller_departure_date'  => $this->input->post('traveller_departure_date', TRUE),
            'traveller_arrival_date'    => $this->input->post('traveller_arrival_date', TRUE),
            'traveller_drop_address1'   => $this->input->post('traveller_drop_address1', TRUE),
            'traveller_drop_date1'      => $this->input->post('traveller_drop_date1', TRUE),
            'traveller_drop_address2'   => $this->input->post('traveller_drop_address2', TRUE),
            'traveller_drop_date2'      => $this->input->post('traveller_drop_date2', TRUE),
            'traveller_departure_state' => $this->input->post('traveller_departure_state', TRUE),
            'traveller_current_state'   => $this->input->post('traveller_current_state', TRUE),
            'traveller_arrival_airport' => $this->input->post('traveller_arrival_airport', TRUE),
            'traveller_arrival_state'   => $this->input->post('traveller_arrival_state', TRUE),

            // Agent details
            'agent_name'      => $this->input->post('agent_name', TRUE),
	            'agent_phone'     => normalize_phone_number(
	                $this->input->post('agent_country_code', TRUE),
	                $this->input->post('agent_phone', TRUE)
	            ),
            'agent_email'     => $this->input->post('agent_email', TRUE),
            'agent_address'   => $this->input->post('agent_address', TRUE),
            'agent_locality'  => $this->input->post('agent_locality', TRUE),
            'agent_postcode'  => $this->input->post('agent_postcode', TRUE),

            // Receiver details
            'receiver_name'      => $this->input->post('receiver_name', TRUE),
	            'receiver_phone'     => normalize_phone_number(
	                $this->input->post('receiver_country_code', TRUE),
	                $this->input->post('receiver_phone', TRUE)
	            ),
            'receiver_email'     => $this->input->post('receiver_email', TRUE),
            'receiver_address'   => $this->input->post('receiver_address', TRUE),
            'receiver_locality'  => $this->input->post('receiver_locality', TRUE),
            'receiver_postcode'  => $this->input->post('receiver_postcode', TRUE),

            // Booking extras
            'payment_method'  => $this->input->post('payment_method', TRUE),
            'items'           => $this->input->post('items', TRUE),
        );

        $this->db->trans_start();

        $this->db->insert('bookings', $data);

        //Update the tracking ID, used space and available space in the traveller table
        $this->travellers_model->update_traveller_space($data['traveller_id']);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction failed in ' . __METHOD__);
            return false;
        }

        $this->finance_read_model->clearFinanceSummaryCaches();
        $this->booking_read_model->clearBookingCountCaches();

        //Send email to Admin
        try {

            require_once 'application/third_party/mail.php';
            $mail->addAddress('onyekaesso10@gmail.com');
            $mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = "New Booking";
            $body = $this->load->view('mail/admin_booking_notify_email', $data, true);
            $mail->Body = $body;
            $mail->AltBody = $body;
            $mail->send();
        } catch (Exception $e) {
        }

        return true;
    }


    public function get_available_bookings($limit, $offset)
    {
        $this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
        $this->db->order_by("date_added", "DESC"); //order by date_unix ASC so that the dates appear chronologically
        $query = $this->db->where('status', 'Available');
        $this->applyNotDeleted();
        $query = $this->db->get('bookings');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function get_booking($limit, $offset)
    {
        $this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
        $this->db->order_by("date_added", "DESC"); //order by date_unix ASC so that the dates appear chronologically
        $this->applyNotDeleted();
        $query = $this->db->get_where('bookings');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function count_available_bookings()
    { //get all available booking
        $query = $this->db->where('status', 'Available');
        $this->applyNotDeleted();
        return $this->db->get_where('bookings')->num_rows();
    }


    public function count_pending_bookings()
    { //get all available booking
        $query = $this->db->where('status', 'Pending');
        $this->applyNotDeleted();
        return $this->db->get_where('bookings')->num_rows();
    }


    public function count_unavailable_bookings()
    { //get all available booking
        $query = $this->db->where('status', 'Unavailable');
        $this->applyNotDeleted();
        return $this->db->get_where('bookings')->num_rows();
    }


    public function count_bookings()
    { //get all booking
        $this->applyNotDeleted();
        return $this->db->get_where('bookings')->num_rows();
    }


    public function approve_booking($id)
    {
        $data = array(
            'status' => booking_status_normalize('Approved'),
        );

        $this->update($data, $id);

        $y = $this->booking_read_model->get_booking_details_by_id($id);
        $data['agent_name'] = $y->agent_name;
        $data['agent_phone'] = $y->agent_phone;
        $data['agent_email'] = $y->agent_email;
        $data['receiver_name'] = $y->receiver_name;
        $data['receiver_phone'] = $y->receiver_phone;
        $data['receiver_email'] = $y->receiver_email;
        $data['total_amount'] = $y->total_amount;

        //get 75% of selected items
        $data['selected_amount'] = (75 / 100) * $y->selected_price;

        $data['tracking_id'] = $y->tracking_id;
        $data['date_added'] = x_datetime_full($y->date_added);
        $data['traveller_name'] = $y->traveller_name;
        $data['traveller_email'] = $y->traveller_email;
        //$data['traveller_available_space'] = $y->traveller_available_space;
        $data['traveller_drop_address1'] = ($y->traveller_drop_address1 == '') ? 'N/A' : $y->traveller_drop_address1;
        $data['traveller_drop_date1'] = ($y->traveller_drop_date1 == '') ? 'N/A' : x_date($y->traveller_drop_date1);
        $data['traveller_drop_address2'] = ($y->traveller_drop_address2 == '') ? 'N/A' : $y->traveller_drop_address2;
        $data['traveller_drop_date2'] = ($y->traveller_drop_date2 == '') ? 'N/A' : x_date($y->traveller_drop_date2);
        $data['traveller_contact'] = $y->traveller_contact;
        $data['traveller_departure_date'] = x_date($y->traveller_departure_date);
        $data['traveller_arrival_date'] = x_date($y->traveller_arrival_date);
        $data['traveller_departure_state'] = ($y->traveller_departure_state == '') ? 'N/A' : $y->traveller_departure_state;
        $data['currency_symbol'] = currency_symbol($y->currency);
        $data['items'] = $y->items;
        $data['insurance'] = ($y->insurance == '0') ? 'N/A' : $y->insurance;

        //Send email to agent
        try {

            require_once 'application/third_party/mail.php';
            $mail->addAddress($y->agent_email);
            //$mail->AddEmbeddedImage('application/third_party/image/check.png', 'check', 'check.png');
            $mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = "Booking Successful";
            $body = $this->load->view('mail/agent_booking_notify_email', $data, true);
            $mail->Body = $body;
            $mail->AltBody = $body;


            $mail->send();
        } catch (Exception $e) {
        }

        //Send email to Traveller
        try {

            require_once 'application/third_party/mail.php';
            $mail->addAddress($y->traveller_email);
            //$mail->AddEmbeddedImage('application/third_party/image/check.png', 'check', 'check.png');
            $mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = "Bag Space Bought";
            $body = $this->load->view('mail/traveller_booking_notify_email', $data, true);
            $mail->Body = $body;
            $mail->AltBody = $body;


            $mail->send();
        } catch (Exception $e) {
        }

        return;
    }


    public function decline_booking($id)
    {
        $data = array(
            'status' => booking_status_normalize('Declined'),
        );
        $this->db->where('id', $id);
        $this->db->update('bookings', $data);

        return;
    }


    public function delete_booking($id)
    {
        // Retrieve the selected_space value from the bookings table
        $y = $this->booking_read_model->get_booking_details_by_id($id);
        if (!$y) {
            return false;
        }
        $selected_space = $y->selected_space;

        // Retrieve the image file name from the bookings table
        $id_photo = $y->id_photo;
        $file_path = 'assets/uploads/' . $id_photo;
        // Delete the id photo file
        if (!empty($id_photo) && file_exists($file_path)) {
            unlink($file_path);
        }

        // Retrieve the image file name from the bookings table
        $selfie = $y->selfie;
        $file_path = 'assets/selfie/' . $selfie;
        // Delete the selfie file
        if (!empty($selfie) && file_exists($file_path)) {
            unlink($file_path);
        }

        $this->db->trans_start();

        // Soft delete the booking from the bookings table
        $this->softDelete($id);


        // Retrieve the traveller_id from the booking
        $traveller_id = $y->traveller_id;

        // Retrieve the current available space for the traveller from the travellers table
        $this->db->select('available_space');
        $this->db->where('id', $traveller_id);
        ci_where_not_deleted($this->db, 'travellers');
        $query = $this->db->get('travellers');
        $current_row = $query->row();
        $current_space = $current_row ? $current_row->available_space : NULL;

        if ($current_space !== NULL) {
            // Calculate the new available space
            $new_space = $current_space + $selected_space;

            // Update the available_space in the travellers table
            $data = array(
                'available_space' => $new_space,
            );
            $this->db->where('id', $traveller_id);
            ci_where_not_deleted($this->db, 'travellers');
            $this->db->update('travellers', $data);
        }

        // Delete rows from the shipping table with the same Tracking ID
        $this->db->delete('shipping', array('tracking_id' => $y->tracking_id));

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction failed in ' . __METHOD__);
            return false;
        }

        $this->finance_read_model->clearFinanceSummaryCaches();
        $this->booking_read_model->clearBookingCountCaches();
        $this->shipping_read_model->clearShippingCountCaches();

        return;
    }

    public function bulk_actions_booking()
    {
        $selected_rows = count($this->input->post('check_bulk_action', TRUE));
        $bulk_action_type = $this->input->post('bulk_action_type', TRUE);
        $row_id = $this->input->post('check_bulk_action', TRUE);
        foreach ($row_id as $id) {
            switch ($bulk_action_type) {
                case 'delete':
                    $this->delete_booking($id);
                    $this->session->set_flashdata('status_msg', "{$selected_rows} Bookings deleted.");
                    break;
            }
        }
    }
}
