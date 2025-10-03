<?php
defined('BASEPATH') or die('Direct access not allowed');


class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->user_restricted(); //allow only logged in users to access this class
        $this->load->model('users_model');
        $this->load->model('common_model');
        $this->user_details = $this->common_model->get_user_details($this->session->email);
    }



    public function index()
    { //user dashboard, routed as dashboard
        // $this->return_to_user_dashboard(); //return user to dashboard if still loggedin
        $this->dashboard_header('Dashboard');
        $id = $this->user_details->id;
        $data['firstname'] = $this->user_details->firstname;
        $data['account_status'] = $this->user_details->account_status;
        $data['is_verified'] = $this->user_details->is_verified;
        $data['referral_link'] = $this->user_details->referral_link;
        $data['user_details'] = $this->users_model->is_profile_complete($id);
        $data['approved_travellers'] = count($this->common_model->get_active_approved_travellers());
        $data['total_bookings'] = count($this->common_model->get_bookings_by_id($id));
        $this->load->view('users/dashboard', $data);
        $this->dashboard_footer();
    }


    public function kyc()
    {
        //	$user_details = $this->common_model->get_user_details($this->session->email);
        //	$data['y'] = $user_details;
        $this->dashboard_header('KYC Verification');
        $user_id = $this->user_details->id;
        $data['firstname'] = $this->user_details->firstname;
        $data['account_status'] = $this->user_details->account_status;
        $data['has_uploaded'] = $this->user_details->has_uploaded;
        $this->load->view('users/kyc', $data);
        $this->dashboard_footer();
    }


    // Track parcel
    public function track_parcel()
    {
        $tracking_id = $this->input->post('parcel');
        $shippings = $this->common_model->get_shipping_by_tracking_id($tracking_id);

        if (!empty($shippings)) {
            $data = array(
                'status' => true,
                'data' => array()
            );

            foreach ($shippings as $shipping) {
                $data['data'][] = array(
                    'tracking_id' => $shipping->tracking_id,
                    'icon_text' => $shipping->icon_text,
                    'heading' => $shipping->heading,
                    'description' => $shipping->body,
                    'delivery_status' => $shipping->delivery_status,
                    'date_added' => x_datetime_full($shipping->date_added)
                );
            }

            echo json_encode($data);
        } else {
            $data = array(
                'status' => false,
                'msg' => 'No Shipping Info, please check again later.'
            );
            echo json_encode($data);
        }
    }


    public function change_password($id)
    {
        //check user exists
        $this->check_data_exists($id, 'id', 'users', 'profile');
        // validation rules		
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
        $this->form_validation->set_rules(
            'confirm_password',
            'Confirm Password',
            'trim|required|matches[password]',
            array('matches' => 'Passwords do not match')
        );

        if ($this->form_validation->run()) {
            $this->users_model->change_password($id); //insert the data into db
            $this->session->set_flashdata('status_msg', "Password Updated");
            redirect('profile');
        } else {
            $this->session->set_flashdata('status_msg_error', validation_errors());
            redirect('profile');
        }
    }


    public function error_page()
    {
        $this->dashboard_header('Error 404');
        $this->load->view('dashboard/dashboard');
        $this->dashboard_footer();
    }


    public function check($user_id)
    {
        $available_balance = $this->withdraw_model->available_balance($user_id);
        echo "total deposite " . $total_deposits = $this->deposit_model->total_deposits($user_id) . "<br>";
        echo "total invested " . $total_invested = $this->investment_model->total_investments($user_id) . "<br>";
        echo "total returns " . $total_returns = $this->investment_model->total_investments_returns($user_id) . "<br>";
        echo "total request withdrawal " . $total_requested_withdrawals = $this->withdraw_model->total_withdrawals_requested($user_id) . "<br>";
        echo "total approved withdrawal " . $total_approved_withdrawals = $this->withdraw_model->total_withdrawals_approved($user_id) . "<br>";
        echo "total rejected withdrawal " . $total_rejected_withdrawals = $this->withdraw_model->total_withdrawals_rejected($user_id) . "<br>";
        echo "<br>";
        var_dump($available_balance);
    }
}
