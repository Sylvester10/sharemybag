<?php
defined('BASEPATH') or die('Direct access not allowed');


/* ===== Documentation =====
Name: Errors
Role: Controller
Description: Errors Class controls error pages such as error404 and no_js
Model: (None)
Author: Sylvester
Date Created: 17th April, 2018
*/


class Errors extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->user_loggedin) {
            $this->load->model('user_read_model');
            $this->user_details = $this->user_read_model->get_user_details($this->session->email);
        }
    }



    public function error404()
    { //set as 404_override method in config/routes, routed as error404
        if ($this->session->user_loggedin) {
            $this->dashboard_header('Page Not Found');
            $this->load->view('users/error404');
            $this->dashboard_footer();
            return;
        }

        $this->website_header('404');
        $this->load->view('errors/html/error404');
        $this->website_footer();
    }


    /*
	Note: This method is loaded when the <noscript></noscript> tag is detected (as set in the <head></head> tag)
	Action: checks if javascript is enabled and redirects user to this page, requesting JS to be enabled before continuing
	*/
    public function no_js()
    { //routed as no_js
        $this->website_header('No JavaScript');
        $this->load->view('errors/html/no_js');
        $this->website_footer();
    }
}
