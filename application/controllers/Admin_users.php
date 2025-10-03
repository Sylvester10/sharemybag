<?php
defined('BASEPATH') or die('Direct access not allowed');



class Admin_Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted(); //allow only logged in users to access this class
        $this->load->model('admin_user_model');
        $this->load->model('common_model');
        $this->admin_details = $this->common_model->get_admin_details($this->session->email);
    }



    /* ========== All user ========== */
    public function index()
    {
        $inner_page_title = 'Users (' . count($this->common_model->users()) . ')';
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

            $account_status = ($y->is_verified == 0) ? '<span class="text-danger"><b>Unverified</b></span>' : (($y->is_verified == 1) ? '<span class="text-warning">Pending</span>' : '<span class="text-success"><b>Verified</b></span>');

            $platform = ($y->platform == 'facebook') ? '<i class="fa-brands fa-facebook"></i>' : (($y->platform == 'instagram') ? '<i class="fa-brands fa-instagram"></i>' : '<i class="fa-brands fa-twitter"></i>');

            // contact details
            $contact_details = '<i class="fa fa-phone"></i> ' . $y->number . ' <br />
                                <i class="fa fa-envelope"></i> '  . $y->email . ' <br />
                                ' . $platform . ' ' . ' ' . $y->socials . ' <br />
                                <i class="fa fa-location"></i> ' . $y->address . ', ' . $y->state . ', ' . $y->post_code . '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
            $row[] = $selfie;
            $row[] = $id_card;
            $row[] = $utility;
            $row[] = $y->firstname . " " . $y->lastname;
            $row[] = $contact_details;
            $row[] = $y->country;
            $row[] = $account_status;
            $row[] = get_last_login_ago($y->last_login);
            $row[] = x_date($y->date_registered);
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    /* ========== Pending travellers ========== */
    public function pending_users()
    {
        $inner_page_title = 'Pending Users (' . $this->common_model->count_pending_users() . ')';
        $this->admin_header('Admin', $inner_page_title);
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

            $account_status = ($y->is_verified == 0) ? '<span class="text-danger"><b>Unverified</b></span>' : (($y->is_verified == 1) ? '<span class="text-warning">Pending</span>' : '<span class="text-success"><b>Verified</b></span>');

            $platform = ($y->platform == 'facebook') ? '<i class="fa-brands fa-facebook"></i>' : (($y->platform == 'instagram') ? '<i class="fa-brands fa-instagram"></i>' : '<i class="fa-brands fa-twitter"></i>');

            // contact details
            $contact_details = '<i class="fa fa-phone"></i> ' . $y->number . ' <br />
                                <i class="fa fa-envelope"></i> ' . $y->email . ' <br />
                                ' . $platform . ' ' . ' ' . $y->socials . ' <br />
                                <i class="fa fa-location"></i> ' . $y->address . ', ' . $y->state . ', ' . $y->post_code . '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
            $row[] = $selfie;
            $row[] = $id_card;
            $row[] = $utility;
            $row[] = $y->firstname . " " . $y->lastname;
            $row[] = $contact_details;
            $row[] = $y->country;
            $row[] = $account_status;
            $row[] = get_last_login_ago($y->last_login);
            $row[] = x_date($y->date_registered);
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    /* ========== Approved travellers ========== */
    public function approved_users()
    {
        $inner_page_title = 'Approved Users (' . $this->common_model->count_approved_users() . ')';
        $this->admin_header('Admin', $inner_page_title);
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

            $account_status = ($y->is_verified == 0) ? '<span class="text-danger"><b>Unverified</b></span>' : (($y->is_verified == 1) ? '<span class="text-warning">Pending</span>' : '<span class="text-success"><b>Verified</b></span>');

            $platform = ($y->platform == 'facebook') ? '<i class="fa-brands fa-facebook"></i>' : (($y->platform == 'instagram') ? '<i class="fa-brands fa-instagram"></i>' : '<i class="fa-brands fa-twitter"></i>');

            // contact details
            $contact_details = '<i class="fa fa-phone"></i> ' . $y->number . ' <br />
                                <i class="fa fa-envelope"></i> ' . $y->email . ' <br />
                                ' . $platform . ' ' . ' ' . $y->socials . ' <br />
                                <i class="fa fa-location"></i> ' . $y->address . ', ' . $y->state . ', ' . $y->post_code . '';

            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
            $row[] = $selfie;
            $row[] = $id_card;
            $row[] = $utility;
            $row[] = $y->firstname . " " . $y->lastname;
            $row[] = $contact_details;
            $row[] = $y->country;
            $row[] = $account_status;
            $row[] = get_last_login_ago($y->last_login);
            $row[] = x_date($y->date_registered);
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->current_model->count_all_records(),
            "recordsFiltered" => $this->current_model->count_filtered_records(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    public function user_profile($id)
    {
        //check user exists
        $this->check_data_exists($id, 'id', 'users', 'admin');
        $user_details = $this->common_model->get_user_details_by_id($id);
        $page_title = $user_details->firstname . " " . $user_details->lastname . "'s" . " " . 'Profile: ';
        $this->admin_header($page_title, $page_title);
        $data['y'] = $user_details;
        $data['total_bookings'] = count($this->common_model->get_bookings_by_id($id));
        $data['bookings'] = $this->common_model->get_bookings_by_id($id);
        $this->load->view('admin/users/user_profile', $data);
        $this->admin_footer();
    }


    public function message_admin($id)
    {
        //check admin exists
        $this->check_data_exists($id, 'id', 'user', 'admin');
        $this->form_validation->set_rules('message', 'Message', 'trim|required');
        $y = $this->common_model->get_user_details_by_id($id);
        if ($this->form_validation->run()) {
            $this->admin_user_model->message_user($id);
            $this->session->set_flashdata('status_msg', "Message successfully sent to {$y->name}.");
        } else {
            $this->session->set_flashdata('status_msg_error', 'Error sending message to user.');
        }
        redirect($this->agent->referrer());
    }


    public function activate_user($id)
    {
        $this->admin_user_model->activate_user($id);
        $this->session->set_flashdata('status_msg', 'User activated successfully.');
        redirect($this->agent->referrer());
    }


    public function deactivate_user($id)
    {
        $this->admin_user_model->deactivate_user($id);
        $this->session->set_flashdata('status_msg', 'User unverified successfully.');
        redirect($this->agent->referrer());
    }


    public function delete_user($id)
    {
        //check admin exists
        $this->check_data_exists($id, 'id', 'users', 'admin');
        $this->admin_user_model->delete_user($id);
        $this->session->set_flashdata('status_msg', 'User deleted successfully.');
        redirect($this->agent->referrer());
    }


    public function user_login_admin($id)
    {

        $user_details = $this->common_model->get_user_details_by_id($id);
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
