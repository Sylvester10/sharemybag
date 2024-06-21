<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Waitlist
Role: Controller
Description: Controls access to waitlist pages and functions in admin panel
Models: Waitlist_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Waitlist extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted(); //allow only logged in users to access this class
        $this->load->model('waitlist_model');
        $this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
    }



    /* ========== Approved waitlist ========== */
    public function index()
    {
        $inner_page_title = 'Wait list (' . count($this->common_model->get_waitlist()) . ')';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/waitlist/waitlist');
        $this->admin_footer();
    }


    public function waitlist_ajax()
    {
        $this->load->model('ajax/waitlist/waitlist_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {
            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
            $row[] = $y->name;
            $row[] = $y->phone;
            $row[] = $y->email;
            $row[] = x_datetime_full($y->date_added);
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


    public function delete_waitlist($id)
    {
        //check admin exists
        $this->check_data_exists($id, 'id', 'waitlist', 'admin');
        $this->waitlist_model->delete_waitlist($id);
        $this->session->set_flashdata('status_msg', 'Waitlist Deleted.');
        redirect($this->agent->referrer());
    }


    public function bulk_actions_waitlist()
    {
        $this->form_validation->set_rules('check_bulk_action', 'Bulk Select', 'trim');
        $selected_rows = count($this->input->post('check_bulk_action', TRUE));
        if ($this->form_validation->run()) {
            if ($selected_rows > 0) {
                $this->waitlist_model->bulk_actions_waitlist();
            } else {
                $this->session->set_flashdata('status_msg_error', 'No item selected.');
            }
        } else {
            $this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
        }
        redirect($this->agent->referrer());
    }
}
