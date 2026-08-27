<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation =====
Name: Admin_finances
Role: Controller
Description: Controls access to Booking pages and functions in admin panel
Models: Bookings_model
Author: Sylvester Esso Nmakwe
Date Created: 10th May, 2023

UPDATED: Added role restriction — finances pages are super_admin only.
*/



class Admin_finances extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->admin_restricted();
        // Finances are sensitive — only super_admin can access them
        $this->admin_role_restricted(['super_admin']);
        $this->load->model('bookings_model');
        $this->load->model('shipping_model');
        $this->load->model('travellers_model');
        $this->load->model('finance_read_model');
        $this->load->library('booking_presenter');
        $this->admin_details = $this->common_model->get_admin_details($this->session->admin_email);
    }



    /* ========== All Finances (Pounds/GBP) ========== */
    public function index()
    {
        $inner_page_title = 'All GBP Finances';
        $this->admin_header('Admin', $inner_page_title);

        // --- GBP/Pounds Data ---
        $data['total_pounds_tax'] = $this->finance_read_model->get_total_pounds_tax();
        $data['total_pounds_amount'] = $this->finance_read_model->get_total_pounds_amount();
        $data['total_pounds_selected_items'] = $this->finance_read_model->get_total_pounds_selected_price();

        // Assuming these methods are intended to calculate the sum of commission directly from the DB
        $data['total_pounds_commission'] = $this->finance_read_model->get_total_pounds_commission();

        $this->load->view('admin/finances/pound_finances', $data);
        $this->admin_footer();
    }


    public function all_finances_ajax()
    {
        $this->load->model('ajax/finances/finances_ajax', 'current_model');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $route = $this->input->post('route');
        $draw = (int) $this->input->post('draw');

        $list = $this->current_model->get_records($month, $year, $route);

        $data = array();
        $rowNumber = 1;
        foreach ($list as $y) {
            $sign = '&pound;';
            $payment_status = $this->booking_presenter->format_payment_status_text($y->payment_status);
            $metrics = $this->booking_presenter->collect_item_metrics($y->items);
            $traveller_commission = booking_stored_traveller_commission($y);
            $revenue = (float) $y->total_amount - $traveller_commission - (float) $y->vat;
            $commission = payment_status_normalize($y->payment_status) == 'completed'
                ? $this->booking_presenter->format_money_with_sign($sign, $traveller_commission)
                : 'N/A';
            $payment_method = $this->booking_presenter->format_payment_method($y->payment_method, 'Bank');
            $exchange_rate = strtolower(trim((string) $y->payment_method)) === 'paystack'
                ? $this->format_exchange_rate($y->paystack_exchange_rate, $sign)
                : 'N/A';

            $row = array();
            $row[] = $rowNumber++;
            // Traveller's Date (Using Drop Date 1 as Travel Date)
            $row[] = x_date_month_time_full($y->traveller_departure_date);
            $row[] = $y->traveller_name;
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $y->total_amount);
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $y->selected_price);
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $y->service_charge);
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $metrics['special_fee']);

            // Special and Premium Columns (Yes/No)
            $row[] = $metrics['is_special'] ? 'Yes' : 'No';
            $row[] = $metrics['is_premium'] ? 'Yes' : 'No';

            $row[] = $y->selected_space . 'KG';

            $row[] = $this->booking_presenter->format_money_with_sign($sign, $y->insurance);
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $revenue);
            $row[] = $exchange_rate;
            $row[] = $commission;
            $row[] = $payment_method;
            $data[] = $row;
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->current_model->count_all_records($month, $year, $route),
            "recordsFiltered" => $this->current_model->count_filtered_records($month, $year, $route),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );
        echo json_encode($output);
    }


    /* ========== CAD Finances ========== */
    public function cad_finances()
    {
        $inner_page_title = 'All CAD Finances'; // Updated title
        $this->admin_header('Admin', $inner_page_title);

        // --- CAD/Dollars Data ---
        $data['total_cad_tax'] = $this->finance_read_model->get_total_cad_tax();
        $data['total_cad_amount'] = $this->finance_read_model->get_total_cad_amount();
        $data['total_cad_selected_items'] = $this->finance_read_model->get_total_cad_selected_price();

        // Assuming these methods calculate the sum of commission directly from the DB
        $data['total_cad_commission'] = $this->finance_read_model->get_total_cad_commission();

        $this->load->view('admin/finances/dollar_finances', $data);
        $this->admin_footer();
    }


    public function all_cad_finances_ajax()
    {
        $this->load->model('ajax/finances/finances_cad_ajax', 'current_model');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $route = $this->input->post('route');
        $draw = (int) $this->input->post('draw');

        $list = $this->current_model->get_records($month, $year, $route);

        $data = array();
        $rowNumber = 1;
        foreach ($list as $y) {
            $sign = '$';
            $payment_status = $this->booking_presenter->format_payment_status_text($y->payment_status);
            $metrics = $this->booking_presenter->collect_item_metrics($y->items);
            $traveller_commission = booking_stored_traveller_commission($y);
            $revenue = (float) $y->total_amount - $traveller_commission - (float) $y->vat;
            $commission = payment_status_normalize($y->payment_status) == 'completed'
                ? $this->booking_presenter->format_money_with_sign($sign, $traveller_commission)
                : 'N/A';
            $payment_method = $this->booking_presenter->format_payment_method($y->payment_method, 'Bank');
            $exchange_rate = strtolower(trim((string) $y->payment_method)) === 'paystack'
                ? $this->format_exchange_rate($y->paystack_exchange_rate, $sign)
                : 'N/A';

            $row = array();
            $row[] = $rowNumber++;
            // Traveller's Date
            $row[] = x_date_month_time_full($y->traveller_departure_date);
            $row[] = $y->traveller_name;
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $y->total_amount);
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $y->selected_price);
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $y->service_charge);
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $metrics['special_fee']);

            // Special and Premium Columns (Yes/No)
            $row[] = $metrics['is_special'] ? 'Yes' : 'No';
            $row[] = $metrics['is_premium'] ? 'Yes' : 'No';

            $row[] = $y->selected_space . 'KG';

            $row[] = $this->booking_presenter->format_money_with_sign($sign, $y->insurance);
            $row[] = $this->booking_presenter->format_money_with_sign($sign, $revenue);
            $row[] = $exchange_rate;
            $row[] = $commission;
            $row[] = $payment_method;
            $data[] = $row;
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->current_model->count_all_records($month, $year, $route),
            "recordsFiltered" => $this->current_model->count_filtered_records($month, $year, $route),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash(),
        );

        echo json_encode($output);
    }


    private function format_exchange_rate($rate, $currency_sign)
    {
        if ($rate === null || !is_numeric($rate) || (float) $rate <= 0) {
            return 'N/A';
        }

        return '&#8358;' . number_format((float) $rate, 2) . ' = ' . $currency_sign . '1';
    }


    /* ========== View Finance ========== */
    public function view_finance($id)
    {
        //check bookings exists
        $this->check_data_exists($id, 'id', 'bookings', 'admin');
        $this->load->model('booking_read_model');
        $bookings_details = $this->booking_read_model->get_booking_details_by_id($id);
        $page_title = 'Booking Info: ' . $bookings_details->agent_name;
        $this->admin_header($page_title, $page_title);
        $data['y'] = $bookings_details;
        $this->load->view('admin/bookings/view_booking', $data);
        $this->admin_footer();
    } 
}
