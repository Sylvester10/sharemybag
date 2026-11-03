<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Run from the project root: php index.php mail_worker run. Never exposed to HTTP callers. */
class Mail_worker extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request()) { show_404(); exit; }
        $this->load->database();
        $this->load->helper(['url','app','email','my','sk']);
        require_once APPPATH . 'core/Constants.php';
        $this->load->model('bulk_mail_model');
    }

    public function run()
    {
        $result = $this->bulk_mail_model->process(5);
        $this->load->library('booking_checkout_worker');
        $result['checkout'] = $this->booking_checkout_worker->run(5);
        echo json_encode($result,JSON_UNESCAPED_SLASHES) . PHP_EOL;
        if ($result['errors'] || !empty($result['checkout']['errors'])) { exit(1); }
    }
}
