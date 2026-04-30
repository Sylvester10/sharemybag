<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin_pricing extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted();
        $this->admin_role_restricted(array('super_admin'));
        $this->load->model('pricing_settings_model');
        $this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
    }

    public function index()
    {
        $inner_page_title = 'Pricing Settings';
        $this->admin_header('Admin', $inner_page_title);
        $data['pricing_routes'] = $this->pricing_settings_model->get_all_routes();
        $data['pricing_table_ready'] = $this->pricing_settings_model->has_pricing_table();
        $this->load->view('admin/pricing/index', $data);
        $this->admin_footer();
    }

    public function update($route_key = '')
    {
        $definitions = booking_route_definition_map();

        if (!isset($definitions[$route_key])) {
            $this->session->set_flashdata('status_msg_error', 'Invalid pricing route selected.');
            redirect(site_url('admin_pricing'));
        }

        $numeric_rule = 'trim|required|numeric|greater_than_equal_to[0]';
        $rules = array(
            'service_charge' => 'Service Charge',
            'normal_rate' => 'Normal Rate',
            'special_rate' => 'Special Rate',
            'duty_free_rate' => 'Duty Free Rate',
            'premium_small_rate' => 'Premium Small Rate',
            'premium_laptop_rate' => 'Premium Laptop Rate',
            'normal_payout_rate' => 'Normal Payout Rate',
            'special_payout_rate' => 'Special Payout Rate',
            'premium_small_payout_rate' => 'Premium Small Payout Rate',
            'premium_laptop_payout_rate' => 'Premium Laptop Payout Rate',
        );

        foreach ($rules as $field => $label) {
            $this->form_validation->set_rules($field, $label, $numeric_rule);
        }

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('status_msg_error', first_validation_error(validation_errors()));
            redirect(site_url('admin_pricing'));
        }

        $payload = array();
        foreach (array_keys($rules) as $field) {
            $payload[$field] = $this->input->post($field, true);
        }

        $result = $this->pricing_settings_model->save_route_pricing($route_key, $payload, $this->admin_details);
        $flash_key = $result['status'] ? 'status_msg_success' : 'status_msg_error';
        $this->session->set_flashdata($flash_key, $result['msg']);
        redirect(site_url('admin_pricing'));
    }
}
