<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation ===== 
Name: Booking_model
Role: Model
Description: Controls the DB processes of Bookings_model from admin panel
Controller: Bookings, Home
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Shipping_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'bookings';
        $this->traveller_details = $this->common_model->get_traveller_details_by_id($this->session->id);
    }

    

    public function add_shipping_to_db()
    {
        $tracking_id = $this->input->post('tracking_id', TRUE);
        $heading = ucfirst($this->input->post('heading', TRUE));
        $body = ucfirst($this->input->post('body', TRUE));
        $delivery_status = $this->input->post('heading', TRUE);
        $icon_text = ($delivery_status == 'Delivered') ? '<i class="fa-solid fa-circle-check text-success"></i>' :
            (($delivery_status == 'In Transit') ? '<i class="fa-solid fa-earth-americas text-primary"></i>' : '<i class="fa-solid fa-calendar-check text-danger"></i>');

        $data = array(
            'tracking_id' => $tracking_id,
            'heading' => $heading,
            'body' => $body,
            'delivery_status' => $delivery_status,
            'icon_text' => $icon_text,
        );
        $this->db->insert('shipping', $data);

        //Update the Delivery Status in the bookings table
        $data = array(
            'delivery_status' => $delivery_status
        );
        $this->db->where('tracking_id', $tracking_id);
        $insert = $this->db->update('bookings', $data);

        return;
    }


    public function edit_shipping($booking_id)
    {
        $heading = ucfirst($this->input->post('heading', TRUE));
        $body = ucfirst($this->input->post('body', TRUE));
        $delivery_status = $this->input->post('delivery_status', TRUE);
        $icon_text = ($delivery_status == 'Delivered') ? '<i class="fa-solid fa-circle-check text-success"></i>' :
            (($delivery_status == 'In Transit') ? '<i class="fa-solid fa-earth-americas text-primary"></i>' : '<i class="fa-solid fa-calendar-check text-danger"></i>');

        $data = array(
            'heading' => $heading,
            'body' => $body,
            'delivery_status' => $delivery_status,
            'icon_text' => $icon_text,
        );

        // Update the shipping record
        $this->db->where('id', $booking_id);
        $this->db->update('shipping', $data);

        // Update the delivery status in the bookings table
        $tracking_id = $this->db->get_where('shipping', array('id' => $booking_id))->row()->tracking_id;
        $booking_data = array(
            'delivery_status' => $delivery_status
        );
        $this->db->where('tracking_id', $tracking_id);
        $this->db->update('bookings', $booking_data);

        return;
    }


    public function delete_shipping($id)
    {
        $tracking_id = $this->db->get_where('shipping', array('id' => $id))->row()->tracking_id;
        $this->db->delete('shipping', array('id' => $id));

        $this->db->where('tracking_id', $tracking_id);
        $this->db->update('bookings', array('delivery_status' => 'Pending'));

        return;
    }



}