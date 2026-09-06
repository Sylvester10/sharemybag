<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation =====
Name: Admin_Users
Role: Controller
UPDATED:
  - customer_support: can view users (index, user_ajax, user_profile)
                      can email users, can approve/block users
                      CANNOT delete users (super_admin only)
  - traveller_support: can only view travellers section — blocked from this controller
  - super_admin: full access
*/



class Admin_Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted();

        // traveller_support has no business in the users section
        $this->admin_role_restricted(['super_admin', 'customer_support']);

        $this->load->model('admin_user_model');
        $this->load->model('user_read_model');
        $this->load->model('booking_read_model');
        $this->admin_details = $this->common_model->get_admin_details($this->session->email);
    }



    /* ========== All users ========== */
    public function index()
    {
        $inner_page_title = 'Users (' . $this->user_read_model->count_users() . ')';
        $this->admin_header('Users', $inner_page_title);
        $this->load->view('admin/users/users');
        $this->admin_footer();
    }


    public function user_ajax()
    {
        $this->load->model('ajax/admins/admin_users_model_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {
            $selfie_src = base_url('assets/selfie/' . $y->selfie);
            $id_src = base_url('assets/id_cards/' . $y->id_card);
            $utility_src = base_url('assets/utility/' . $y->utility);
            $selfie = user_avatar_table($y->selfie, $selfie_src, user_avatar);
            $id_card = user_avatar_table($y->id_card, $id_src, id_card);
            $utility = user_avatar_table($y->utility, $utility_src, pdf_icon);

            $is_verified = user_verification_badge($y->is_verified);
            $account_status = account_status_badge($y->account_status);

            $platform = ($y->platform == 'facebook') ? '<i class="lab la-facebook-f"></i>' : (($y->platform == 'instagram') ? '<i class="lab la-instagram"></i>' : '<i class="lab la-twitter"></i>');

            $contact_details = '<i class="las la-phone"></i> ' . $y->number . ' <br />
                                <i class="las la-envelope"></i> '  . $y->email . ' <br />
                                ' . $platform . ' ' . ' ' . $y->socials . ' <br />
                                <i class="las la-map-marker-alt"></i> ' . $y->address . ', ' . $y->state . ', ' . $y->post_code . '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = $selfie;
            $row[] = $id_card;
            $row[] = $utility;
            $row[] = $y->firstname . " " . $y->lastname;
            $row[] = $contact_details;
            $row[] = $y->country;
            $row[] = $is_verified;
            $row[] = $account_status;
            $row[] = get_last_login_ago($y->last_login);
            $row[] = x_date($y->date_registered);
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


    public function pending_users()
    {
        $inner_page_title = 'Pending Users (' . $this->user_read_model->count_pending_users() . ')';
        $this->admin_header('Users', $inner_page_title);
        $this->load->view('admin/users/pending_users');
        $this->admin_footer();
    }


    public function pending_users_ajax()
    {
        $this->load->model('ajax/admins/pending_users_model_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {
            $selfie_src = base_url('assets/selfie/' . $y->selfie);
            $id_src = base_url('assets/id_cards/' . $y->id_card);
            $utility_src = base_url('assets/utility/' . $y->utility);
            $selfie = user_avatar_table($y->selfie, $selfie_src, user_avatar);
            $id_card = user_avatar_table($y->id_card, $id_src, id_card);
            $utility = user_avatar_table($y->utility, $utility_src, pdf_icon);

            $is_verified = user_verification_badge($y->is_verified);
            $account_status = account_status_badge($y->account_status);
            $platform = ($y->platform == 'facebook') ? '<i class="lab la-facebook-f"></i>' : (($y->platform == 'instagram') ? '<i class="lab la-instagram"></i>' : '<i class="lab la-twitter"></i>');
            $contact_details = '<i class="las la-phone"></i> ' . $y->number . ' <br />
                                <i class="las la-envelope"></i> '  . $y->email . ' <br />
                                ' . $platform . ' ' . ' ' . $y->socials . ' <br />
                                <i class="las la-map-marker-alt"></i> ' . $y->address . ', ' . $y->state . ', ' . $y->post_code . '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = $selfie;
            $row[] = $id_card;
            $row[] = $utility;
            $row[] = $y->firstname . " " . $y->lastname;
            $row[] = $contact_details;
            $row[] = $y->country;
            $row[] = $is_verified;
            $row[] = $account_status;
            $row[] = get_last_login_ago($y->last_login);
            $row[] = x_date($y->date_registered);
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


    public function approved_users()
    {
        $inner_page_title = 'Approved Users (' . $this->user_read_model->count_approved_users() . ')';
        $this->admin_header('Users', $inner_page_title);
        $this->load->view('admin/users/approved_users');
        $this->admin_footer();
    }


    public function approved_users_ajax()
    {
        $this->load->model('ajax/admins/approved_users_model_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {
            $selfie_src = base_url('assets/selfie/' . $y->selfie);
            $id_src = base_url('assets/id_cards/' . $y->id_card);
            $utility_src = base_url('assets/utility/' . $y->utility);
            $selfie = user_avatar_table($y->selfie, $selfie_src, user_avatar);
            $id_card = user_avatar_table($y->id_card, $id_src, id_card);
            $utility = user_avatar_table($y->utility, $utility_src, pdf_icon);

            $is_verified = user_verification_badge($y->is_verified);
            $account_status = account_status_badge($y->account_status);
            $platform = ($y->platform == 'facebook') ? '<i class="lab la-facebook-f"></i>' : (($y->platform == 'instagram') ? '<i class="lab la-instagram"></i>' : '<i class="lab la-twitter"></i>');
            $contact_details = '<i class="las la-phone"></i> ' . $y->number . ' <br />
                                <i class="las la-envelope"></i> '  . $y->email . ' <br />
                                ' . $platform . ' ' . ' ' . $y->socials . ' <br />
                                <i class="las la-map-marker-alt"></i> ' . $y->address . ', ' . $y->state . ', ' . $y->post_code . '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y);
            $row[] = $selfie;
            $row[] = $id_card;
            $row[] = $utility;
            $row[] = $y->firstname . " " . $y->lastname;
            $row[] = $contact_details;
            $row[] = $y->country;
            $row[] = $is_verified;
            $row[] = $account_status;
            $row[] = get_last_login_ago($y->last_login);
            $row[] = x_date($y->date_registered);
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


    public function user_profile($id)
    {
        $this->check_data_exists($id, 'id', 'users', 'admin_users');
        $user_details = $this->user_read_model->get_user_details_by_id($id);
        $page_title = 'User Profile: ' . $user_details->firstname . ' ' . $user_details->lastname;
        $this->admin_header($page_title, $page_title);
        $data['y'] = $user_details;
        $data['bookings'] = $this->booking_read_model->get_bookings_by_user_id($id);
        $data['total_bookings'] = count($data['bookings']);
        $this->load->view('admin/users/user_profile', $data);
        $this->admin_footer();
    }


    public function update_user_ajax($id)
    {
        //check user exists
        $this->check_data_exists($id, 'id', 'users', 'admin');
        // validation rules
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|min_length[2]|max_length[500]|required');
        $this->form_validation->set_rules('lastname', 'Last Name', 'trim|min_length[2]|max_length[500]|required');
        $this->form_validation->set_rules('country_code', 'Country code', 'trim|required');
        $this->form_validation->set_rules('number', 'Mobile', 'trim|required');
        $this->form_validation->set_rules(
            'email',
            'Email',
            'trim|required|valid_email',
            array('valid_email' => 'Enter a valid email.')
        );
        $this->form_validation->set_rules('country', 'Country', 'trim|required');
        $this->form_validation->set_rules('address', 'Address', 'trim|required');
        $this->form_validation->set_rules('state', 'State', 'trim|required');
        $this->form_validation->set_rules('post_code', 'Post code', 'trim|required');

        if (!$this->form_validation->run()) {
            $this->user_profile($id);
            return;
        }

        if ($this->admin_user_model->update_user($id)) {
            $this->session->set_flashdata('status_msg', "User data updated successfully.");
            redirect('admin_users/user_profile/' . $id);
            return;
        }
        $this->session->set_flashdata('status_msg_error', 'User data could not be updated');
        redirect('admin_users/user_profile/' . $id);
    }


    public function verify_user($id)
    {
        $this->admin_user_model->verify_user($id);
        $this->session->set_flashdata('status_msg', 'User verified successfully.');
        redirect($this->agent->referrer());
    }


    public function unverify_user($id)
    {
        $reason = $this->input->post('rejection_reason', TRUE);
        $note = $this->input->post('rejection_note', TRUE);

        if (!$this->input->post()) {
            $this->session->set_flashdata('status_msg_error', 'Select a reason before un-verifying this account.');
            redirect($this->agent->referrer());
            return;
        }

        $this->form_validation->set_rules('rejection_reason', 'Reason', 'trim|required');
        $this->form_validation->set_rules('rejection_note', 'Additional note', 'trim|max_length[500]');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('status_msg_error', first_validation_error('Select a reason before un-verifying this account.'));
            redirect($this->agent->referrer());
            return;
        }

        $this->admin_user_model->unverify_user($id, $reason, $note);
        $this->session->set_flashdata('status_msg', 'User unverified successfully.');
        redirect($this->agent->referrer());
    }


    public function block_user($id)
    {
        $this->admin_user_model->block_user($id);
        $this->session->set_flashdata('status_msg', 'User blocked successfully.');
        redirect($this->agent->referrer());
    }


    public function unblock_user($id)
    {
        $this->admin_user_model->unblock_user($id);
        $this->session->set_flashdata('status_msg', 'User unblocked successfully.');
        redirect($this->agent->referrer());
    }


    public function message_admin($id)
    {
        //check admin exists
        $this->check_data_exists($id, 'id', 'user', 'admin');
        $this->form_validation->set_rules('message', 'Message', 'trim|required');
        $y = $this->user_read_model->get_user_details_by_id($id);
        if ($this->form_validation->run()) {
            $this->admin_user_model->message_user($id);
            $this->session->set_flashdata('status_msg', "Message successfully sent to {$y->name}.");
        } else {
            $this->session->set_flashdata('status_msg_error', 'Error sending message to user.');
        }
        redirect($this->agent->referrer());
    }


    public function delete_user($id)
    {
        $this->admin_role_restricted(['super_admin']);
        $this->check_data_exists($id, 'id', 'users', 'admin_users');
        $this->admin_user_model->delete_user($id);
        $this->session->set_flashdata('status_msg', 'User deleted successfully.');
        redirect($this->agent->referrer());
    }


    public function user_login_admin($id)
    {

        $user_details = $this->user_read_model->get_user_details_by_id($id);
        $email = $user_details->email;
        if ($email != NULL || $email != '') {
            $login_data = array(
                'email' => $email,
                'user_loggedin' => TRUE,
            );
            $this->session->set_userdata($login_data);
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('status_msg_error', 'Email not found!');
            redirect($this->agent->referrer());
        }
    }


    public function bulk_actions_user()
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
                $this->admin_user_model->bulk_actions_user($selected_rows);
            } else {
                $this->session->set_flashdata('status_msg_error', 'No item selected.');
            }
        } else {
            $this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
        }
        redirect($this->agent->referrer());
    }

}
