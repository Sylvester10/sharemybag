
<?php
defined('BASEPATH') or exit('Direct access to script not allowed');


class Users_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->primary_cols = array('id');
    }




    public function add_new_user()
    {
        //generate and update tracking ID
        $verification_code = generate_verification_code();

        $data = extractKeys($this->input->post(), $this->getColumns());
        $data['verification_code'] = $verification_code;


        $this->db->insert('users', $data);

        // //Send email to Admin
        // try {

        //     require_once 'application/third_party/mail.php';
        //     $mail->addAddress('admin@sharemybag.co.uk');
        //     $mail->AddEmbeddedImage('application/third_party/image/smblogo.png', 'logo', 'smblogo.png');
        //     $mail->isHTML(true); // Set email format to HTML
        //     $mail->Subject = "Email Address Verification";
        //     $body = $this->load->view('mail/user_code_verification_email', $data, true);
        //     $mail->Body = $body;
        //     $mail->AltBody = $body;
        //     $mail->send();
        // } catch (Exception $e) {
        // }

        return true;
    }


    function update_traveller_space($id)
    {
        $traveller = $this->common_model->get_traveller_details_by_id($id);
        $this->db->select_sum('selected_space');
        $query = $this->db->get_where('bookings', array('traveller_id' => $id));
        if ($query->num_rows()) {
            $selected_space = $query->row()->selected_space;
            $data['used_space'] = $selected_space;
            $data['available_space'] = $traveller->original_bag_space - $selected_space;
            $this->db->where('id', $traveller->id);
            return $this->db->update('travellers', $data);
        }
        return false;
    }


    public function get_approved_travellers($limit, $offset)
    {
        $this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
        $this->db->order_by("travel_date", "asc"); //order by date_unix ASC so that the dates appear chronologically
        $query = $this->db->where('status', 'Approved');
        $query = $this->db->get('travellers');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function get_traveller($limit, $offset)
    {
        $this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
        $this->db->order_by("travel_date", "DESC"); //order by date_unix ASC so that the dates appear chronologically
        $query = $this->db->get_where('travellers');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function count_approved_travellers()
    { //count all approved travellers
        $query = $this->db->where('status', 'Approved');
        return $this->db->get_where('travellers')->num_rows();
    }


    public function count_pending_travellers()
    { //count all pending traveller
        $query = $this->db->where('status', 'Pending');
        return $this->db->get_where('travellers')->num_rows();
    }


    public function count_unapproved_travellers()
    { //count all unapproved traveller
        $query = $this->db->where('status', 'Unapproved');
        return $this->db->get_where('travellers')->num_rows();
    }


    public function count_travellers()
    { //count all traveller
        return $this->db->get_where('travellers')->num_rows();
    }


    public function approve_traveller($id)
    {
        $data = array(
            'status' => 'Approved',
        );
        $this->db->where('id', $id);
        return $this->db->update('travellers', $data);
    }


    public function unapprove_traveller($id)
    {
        $data = array(
            'status' => 'Unapproved',
        );
        $this->db->where('id', $id);
        return $this->db->update('travellers', $data);
    }


    public function delete_booking_id_photo($id)
    {
        $y = $this->common_model->get_booking_details_by_traveller_id($id);
        $file_path = './assets/uploads/' . $y->id_photo;
        if (!empty($y->id_photo) && file_exists($file_path)) { // Delete the id photo file
            unlink($file_path);
        }
    }

    public function delete_booking_selfie($id)
    {
        $y = $this->common_model->get_booking_details_by_traveller_id($id);
        $file_path = './assets/selfie/' . $y->selfie;
        if (!empty($y->selfie) && file_exists($file_path)) { // Delete the id photo file
            unlink($file_path);
        }
    }


    public function delete_traveller($id)
    {
        $y = $this->common_model->get_traveller_details_by_id($id);

        // Delete the traveller from the bookings table
        $this->db->delete('travellers', array('id' => $id));

        // Delete the all bookings that shares the same traveller id from the bookings table
        $this->db->delete('bookings', array('traveller_id' => $id));

        // remove image files from server
        $this->delete_booking_id_photo($id);
        $this->delete_booking_selfie($id);

        // Delete the all shipping that shares the same traveller id from the bookings table
        $this->db->delete('shipping', array('tracking_id' => $id));
        return;
    }


    public function bulk_actions_traveller()
    {
        $selected_rows = count($this->input->post('check_bulk_action', TRUE));
        $bulk_action_type = $this->input->post('bulk_action_type', TRUE);
        $row_id = $this->input->post('check_bulk_action', TRUE);
        foreach ($row_id as $id) {
            switch ($bulk_action_type) {
                case 'approve':
                    $this->approve_traveller($id);
                    $this->session->set_flashdata('status_msg', "{$selected_rows} Traveller(s) Approved.");
                    break;
                case 'unapprove':
                    $this->unapprove_traveller($id);
                    $this->session->set_flashdata('status_msg', "{$selected_rows} Traveller(s) Unapproved.");
                    break;
                case 'delete':
                    $this->delete_traveller($id);
                    $this->session->set_flashdata('status_msg', "{$selected_rows} Traveller(s) deleted.");
                    break;
            }
        }
    }
}
