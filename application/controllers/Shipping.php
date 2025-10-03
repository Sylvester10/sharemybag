<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to Shippings pages and functions in admin panel
Models: Shipping_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023
*/



class Shipping extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted(); //allow only logged in users to access this class
        $this->load->model('shipping_model');
        $this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
    }




    public function index()
    {
        $inner_page_title = 'All shippings (' . count($this->common_model->get_all_shippings()) . ')';
        $this->admin_header('Admin', $inner_page_title);
        $this->load->view('admin/shippings/all_shippings');
        $this->admin_footer();
    }


    public function shippings_ajax()
    {
        $this->load->model('ajax/shippings/all_shippings_ajax', 'current_model');
        $list = $this->current_model->get_records();
        $data = array();
        foreach ($list as $y) {
            $image_src = base_url('assets/uploads/' . $y->id_photo);
            $avatar = user_avatar_table($y->id_photo, $image_src, user_avatar);
            $status = '<span class="text-success"><b> ' . $y->status . ' </b></span>';
            $agent_contact = '' . $y->agent_phone . ' <br> <br> ' . $y->agent_email . ' <br> <br> ' . $y->agent_address . '';
            $packer_contact = '' . $y->packer_phone . ' <br> <br> ' . $y->packer_email . ' <br> <br> ' . $y->packer_address . '';
            $items = nl2br($y->item_name);
            $selected_space = '' . $y->selected_space . ' KG';
            $price = '₦' . $y->selected_price . '';
            $delivery_status = ($y->delivery_status == 'delivered') ? '<span class="text-success"><b>Delivered</b></span>' : '<span class="text-primary"><b>In Transit</b></span>';
            $row = array();
            $row[] = checkbox_bulk_action($y->id);
            $row[] = $this->current_model->options($y->id) . $this->current_model->modals($y->id);
            $row[] = $avatar;
            $row[] = $y->traveller_name;
            $row[] = $y->agent_name;
            $row[] = $agent_contact;
            $row[] = $y->packer_name;
            $row[] = $packer_contact;
            $row[] = $y->category;
            $row[] = $items;
            $row[] = $selected_space;
            $row[] = $price;
            $row[] = $y->tracking_id;
            $row[] = $status;
            $row[] = $delivery_status;
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


    /* ========== Update shipping ========== */
    public function update_shipping($id)
    {
        //check travellers exists
        $this->check_data_exists($id, 'id', 'bookings', 'admin');
        $shipping_details = $this->common_model->get_booking_details_by_id($id);
        $page_title = 'Update Shipping Info: ' . $shipping_details->agent_name;
        $this->admin_header($page_title, $page_title);
        $data['y'] = $shipping_details;
        $data['traveller_details'] = $this->common_model->get_traveller_details_by_id($id);
        $this->load->view('admin/shipping/update_shipping', $data);
        $this->admin_footer();
    }


    public function update_shipping_ajax()
    {
        // validation rules
        $this->form_validation->set_rules('tracking_id', 'Tracking ID', 'trim|required');
        $this->form_validation->set_rules('heading', 'Heading', 'trim|required');
        $this->form_validation->set_rules('body', 'Body', 'trim|min_length[2]|max_length[500]|required');

        if ($this->form_validation->run()) {

            $shipping_details = $this->common_model->get_booking_details_by_id($id);
            $booking_id = $shipping_details->id;
            $this->shipping_model->add_shipping_to_db();
            $this->session->set_flashdata('status_msg', "Shipping info updated successfully.");
            $this->update_shipping($booking_id);
        } else {
            echo validation_errors();
        }
    }


    /* ========== shippings Profile ========== */
    public function view_shipping($id)
    {
        //check shippings exists
        $this->check_data_exists($id, 'id', 'shippings', 'admin');
        $shippings_details = $this->common_model->get_shipping_details_by_id($id);
        $page_title = 'shipping Info: ' . $shippings_details->agent_name;
        $this->admin_header($page_title, $page_title);
        $data['y'] = $shippings_details;
        $this->load->view('admin/shippings/view_shipping', $data);
        $this->admin_footer();
    }


    public function activate_shipping($id)
    {
        $this->shipping_model->activate_shipping($id);
        $this->session->set_flashdata('status_msg', 'Shipping data now available.');
        redirect($this->agent->referrer());
    }


    public function deactivate_shipping($id)
    {
        $this->shipping_model->deactivate_shipping($id);
        $this->session->set_flashdata('status_msg', 'Shipping data now unavailable.');
        redirect($this->agent->referrer());
    }


    public function delete_shipping($id)
    {
        //check admin exists
        $this->check_data_exists($id, 'id', 'shippings', 'admin');
        $this->shipping_model->delete_shipping($id);
        $this->session->set_flashdata('status_msg', 'Shipping data deleted successfully.');
        redirect($this->agent->referrer());
    }


    public function bulk_actions_shipping()
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
                $this->bookings_model->bulk_actions_shipping($selected_rows);
            } else {
                $this->session->set_flashdata('status_msg_error', 'No item selected.');
            }
        } else {
            $this->session->set_flashdata('status_msg_error', 'Bulk action failed!');
        }
        redirect($this->agent->referrer());
    }
}
