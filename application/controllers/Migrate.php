<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller
{
    public function index()
    {
        if (!$this->input->is_cli_request()) {
            show_404();
        }

        $this->config->set_item('migration_enabled', TRUE);
        $this->load->library('migration');

        if ($this->migration->latest() === FALSE) {
            echo $this->migration->error_string() . PHP_EOL;
            exit(1);
        }

        echo "Migrations ran successfully." . PHP_EOL;
    }
}
