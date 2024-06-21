<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation ===== 
Name: Waitlist
Role: Model
Description: Controls the DB processes of Waitlist_model from admin panel
Controller: Waitlist
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Waitlist_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }




    public function add_waitlist_to_db()
    {
        $name = ucwords($this->input->post('name', TRUE));
        $email = $this->input->post('email', TRUE);
        $c_code = $this->input->post('c_code', TRUE);
        $number1 = $this->input->post('phone', TRUE);

        $phone = $c_code . "" . $number1;

        $data = array(
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
        );

        $this->db->insert('waitlist', $data);

        return;
    }


    public function get_waitlist($limit, $offset)
    {
        $this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
        $this->db->order_by("travel_date", "DESC"); //order by date_unix ASC so that the dates appear chronologically
        $query = $this->db->get_where('waitlist');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function count_waitlist()
    { //count all waitlist
        return $this->db->get_where('waitlist')->num_rows();
    }


    public function delete_waitlist($id)
    {
        $y = $this->common_model->get_waitlist_details_by_id($id);

        // Delete the traveler from the bookings table
        $this->db->delete('waitlist', array('id' => $id));
        return;
    }


    public function bulk_actions_waitlist()
    {
        $selected_rows = count($this->input->post('check_bulk_action', TRUE));
        $bulk_action_type = $this->input->post('bulk_action_type', TRUE);
        $row_id = $this->input->post('check_bulk_action', TRUE);
        foreach ($row_id as $id) {
            switch ($bulk_action_type) {
                case 'delete':
                    $this->delete_waitlist($id);
                    $this->session->set_flashdata('status_msg', "{$selected_rows} Waitlist(s) deleted.");
                    break;
            }
        }
    }
}
