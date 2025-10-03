<?php
defined('BASEPATH') or exit('Direct access to script not allowed');


class Users_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->table = 'bookings';
        $this->load->model('common_model');
        $this->primary_cols = array('id');
        $this->traveller_details = $this->common_model->get_traveller_details_by_id($this->session->id);
    }



    public function add_new_user()
    {
        // Generate and update verification code
        $verification_code = generate_verification_code();
        $pass_reset_code = generate_verification_code();
        $ref_num = mt_rand(11111, 99999);
        $username = strtolower($this->input->post('firstname', TRUE)) . $ref_num;

        $data = extractKeys($this->input->post(), $this->getColumns());
        $referral_link = base_url('travellers' . '/' . '?refer=' . $username);
        $email = $this->input->post('email', TRUE);

        $data = array(
            'verification_code' => $verification_code,
            'pass_reset_code' => $pass_reset_code,
            'account_status' => 0,
            'is_verified' => 0,
            'username' => $username,
            'firstname' => $this->input->post('firstname', TRUE),
            'lastname' => $this->input->post('lastname', TRUE),
            'email' => $email,
            'country' => $this->input->post('country', TRUE),
            'referral_link' => $referral_link,
        );


        $this->db->insert('users', $data);

        // Get the last inserted user ID
        $user_id = $this->db->insert_id();

        // Send email to User
        send_email_notification($this, $email, 'Email Address Verification', $data, 'user_code_verification_email');

        return $user_id; // Return user ID
    }


    public function resend_verification_code($user_id)
    {
        // Get user data for sending the email
        $user = $this->common_model->get_user_details_by_id($user_id);
        if (!$user) {
            return false; // Handle case where user is not found
        }

        $verification_code = $user->verification_code; // Fetch the existing verification code
        $email = $user->email;

        // Prepare email data
        $data = [
            'verification_code' => $verification_code,
            'firstname' => $user->firstname,
        ];

        // Send email to User
        send_email_notification($this, $email, 'Email Address Verification', $data, 'user_code_verification_email');

        return true;
    }


    public function get_user_by_verification_code($verification_code)
    {
        $query = $this->db->get_where('users', ['verification_code' => $verification_code]);
        return $query->row();
    }


    public function update_user_verification($user_id, $password)
    {
        $this->db->where('id', $user_id);
        $this->db->update('users', [
            'password' => $password,
            'verification_code' => NULL // Clear old code after verification
        ]);
    }


    public function update_user_verification_code($user_id, $new_code)
    {
        $this->db->where('id', $user_id);
        $this->db->update('users', ['verification_code' => $new_code]);
    }


    public function verify_document($user_id, $id_photo, $selfie, $utility)
    {
        // Prepare data for insertion
        $data = extractKeys($this->input->post(), $this->getColumns());
        $data['id_type'] = $this->input->post('id_type');
        $data['platform'] = $this->input->post('platform');
        $data['socials'] = $this->input->post('socials');
        $data['id_card'] = $id_photo;
        $data['selfie'] = $selfie;
        $data['utility'] = $utility;
        $data['is_verified'] = 1;

        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
        // Insert data into the database
        if ($this->db->affected_rows() > 0) {
            return $user_id; // Return user ID
        }
        return false; // Insertion failed
    }


    // add booking to the database
    // public function add_booking_to_db($user_id, $fullname, $email)
    // {
    //     //generate and update tracking ID
    //     $tracking_id = generate_tracking_id(7);

    //     $calculations = json_decode($this->input->post('price_calculations'));

    //     $data = extractKeys($this->input->post(), $this->getColumns());
    //     $data['insurance'] = $calculations->insurance;
    //     $data['status'] = 'Booking Pending';
    //     $data['delivery_status'] = 'Booking Pending';
    //     $data['total_amount'] = $calculations->totalAmount;
    //     $data['sub_total'] = $calculations->subTotal;
    //     $data['vat'] = $calculations->vat;
    //     $data['service_charge'] = $calculations->serviceCharge;
    //     $data['selected_space'] = $calculations->selectedSpace;
    //     $data['selected_price'] = $calculations->selectedPrice;
    //     $data['tracking_id'] = $tracking_id;
    //     $data['user_id'] = $user_id;
    //     $data['user_fullname'] = $fullname;
    //     $data['user_email'] = $email;
    //     $data['hash'] = $this->generate_hash(200);

    //     $this->db->insert('bookings', $data);

    //     return $this->db->get_where($this->table, array('hash' => $data['hash']))->row();
    // }

    public function add_booking_to_db($user_id, $fullname, $email)
    {
        //generate and update tracking ID
        $tracking_id = generate_tracking_id(7);

        $calculations = json_decode($this->input->post('price_calculations'));

        $data = extractKeys($this->input->post(), $this->getColumns());
        $data['insurance'] = $calculations->insurance;
        $data['payment_status'] = 'canceled';
        $data['status'] = 'Booking Pending';
        $data['delivery_status'] = 'Booking Pending';
        $data['total_amount'] = $calculations->totalAmount;
        $data['sub_total'] = $calculations->subTotal;
        // $data['vat'] = $calculations->vat;
        $data['service_charge'] = $calculations->serviceCharge;
        $data['selected_space'] = $calculations->selectedSpace;
        $data['selected_price'] = $calculations->selectedPrice;
        $data['tracking_id'] = $tracking_id;
        $data['user_id'] = $user_id;
        $data['user_fullname'] = $fullname;
        $data['user_email'] = $email;
        $data['hash'] = $this->generate_hash(200);

        $this->db->insert('bookings', $data);

        return $this->db->get_where($this->table, array('hash' => $data['hash']))->row();
    }


    public function add_offline_booking_to_db($id)
    {
        $y = $this->common_model->get_traveller_details_by_id($id);

        $data = extractKeys($this->input->post(), $this->getColumns());
        $data['traveller_id'] = $y->id;
        $data['traveller_name'] = $y->fullname;
        $data['traveller_contact'] = $y->phone;
        $data['traveller_departure_date'] = $y->travel_date;
        $data['traveller_arrival_date'] = $y->arrival_date;
        $data['traveller_departure_state'] = $y->departure_state;
        $data['traveller_current_state'] = $y->current_state;
        $data['traveller_arrival_state'] = $y->arrival_state;
        $data['traveller_arrival_airport'] = $y->arrival_airport;
        $data['traveller_drop_address1'] = $y->drop_address1;
        $data['traveller_drop_date1'] = $y->drop_date1;
        $data['	traveller_drop_address2'] = $y->drop_address2;
        $data['traveller_drop_date2'] = $y->drop_date2;
        $data['selected_space'] = $this->input->post('selected_space');
        $data['payment_method'] = 'offline';
        $data['payment_status'] = 'completed';
        $data['hash'] = $this->generate_hash(200);

        return $this->db->insert('bookings', $data);
    }


    // add booking to the database
    public function update_profile_to_db($id)
    {
        // Extract and validate data
        $data = extractKeys($this->input->post(), $this->getColumns());

        $data = array(
            'state' => $this->input->post('state', TRUE),
            'post_code' => $this->input->post('post_code', TRUE),
            'address' => $this->input->post('address', TRUE),
            'number' => $this->input->post('number', TRUE),
        );

        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }


    public function is_profile_complete($id)
    {
        $user_details = $this->common_model->get_user_details_by_id($id);

        // Check if any of the fields are null or empty
        $is_user_complete = (
            $user_details->number == '' ||
            $user_details->address == '' ||
            $user_details->state == '' ||
            $user_details->post_code == ''
        );

        return $is_user_complete;
    }


    public function change_password($id)
    {
        $y = $this->common_model->get_user_details_by_id($id);
        $password = password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT);
        $data = array(
            'password' => $password,
        );
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
}
