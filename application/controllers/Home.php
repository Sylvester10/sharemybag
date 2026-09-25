<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* ===== Documentation =====
Name: Home
Role: Controller
Description: Public website controller for landing, traveller search, booking, and parcel tracking flows
Models: Travellers_model, Traveller_read_model, Bookings_model, Adverts_model
Author: Sylvester Esso Nmakwe
Date Created: 11th April, 2023

UPDATED:
  - price_estimate(): AJAX endpoint for the landing page price checker popup.
    Accepts: destination (Nigeria/United Kingdom/Canada), category, weight.
    Returns: itemised cost breakdown matching the booking summary page.
*/



class Home extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('travellers_model');
        $this->load->model('traveller_read_model');
        $this->load->model('bookings_model');
        $this->load->model('adverts_model');
        $this->load->library('booking_presenter');
        $this->traveller_details = $this->traveller_read_model->get_traveller_details_by_id($this->session->id);
    }


    public function index()
    {
        $data['schema'] = $this->get_schema();
        $this->website_header('Share My Bag', $data);
        $this->load->view('website/home', $data);
        $this->website_footer();
    }


    public function search()
    {
        $destination = $this->input->post('destination');
        $travellers = $this->traveller_read_model->get_travellers_by_destination($destination);
        $csrf_hash = $this->security->get_csrf_hash();

        $selected_traveller = null;

        if (count($travellers) > 0) {
            foreach ($travellers as $t) {
                $t = $this->travellers_model->update_traveller_space($t->id, true);
                if (!$t) {
                    continue;
                }
                if ($t->available_space > 0 && !$t->bag_locked) {
                    $selected_traveller = $t;
                    break;
                }
            }
        }

        if ($selected_traveller) {
            $days = get_date_difference(date('Y-m-d H:i:s'), $selected_traveller->travel_date);
            $days = !$days ? 'Today' : ($days > 1 ? "$days Days" : "$days Day");

            $data = array(
                'travel_date'      => x_date($selected_traveller->travel_date),
                'days_remaining'   => $days,
                'current_state'    => $selected_traveller->current_state,
                'departure_state'  => $selected_traveller->departure_state,
                'arrival_airport'  => $selected_traveller->arrival_airport,
                'arrival_state'    => $selected_traveller->arrival_state,
                'area'             => isset($selected_traveller->area) ? $selected_traveller->area : '',
                'destination_area' => isset($selected_traveller->destination_area) ? $selected_traveller->destination_area : '',
                'available_space'  => $selected_traveller->available_space,
                'id'               => $selected_traveller->id,
                'status'           => true,
                'csrf_hash'        => $csrf_hash
            );
            echo json_encode($data);
        } else {
            $data = array(
                'status'    => false,
                'msg'       => 'No travellers are available for that route right now.',
                'csrf_hash' => $csrf_hash
            );
            echo json_encode($data);
        }
    }


    /* ========================================================
	   PRICE ESTIMATE — Landing Page AJAX Endpoint
	   POST params:
	     - destination:  'Nigeria', 'United Kingdom', or 'Canada'
	     - origin:       'Nigeria', 'United Kingdom', or 'Canada'
	     - category:     'Normal', 'Fish/Medicine', 'Documents/Electronics'
	     - weight:       numeric (KG or PC count)
	   ======================================================== */
    public function price_estimate()
    {
        $destination = trim($this->input->post('destination'));
        $origin      = trim($this->input->post('origin'));
        $category    = trim($this->input->post('category'));
        $weight      = (float) $this->input->post('weight');
        $response = $this->booking_presenter->build_price_estimate($origin, $destination, $category, $weight);
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        echo json_encode($response);
    }


    public function track()
    {
        $this->website_header('Track Parcel');
        $this->load->view('website/track');
        $this->website_footer();
    }


    public function booking($hash = null)
    {
        if (!$hash) redirect('/');
        $this->website_header('Booking');
        $data['hash'] = $hash;
        $this->load->view('website/booking', $data);
        $this->website_footer();
    }


    public function travellers()
    {
        $data['captcha_code'] = mt_rand(111111, 999999);
        $this->website_header('Travellers');
        $this->load->view('website/travellers', $data);
        $this->website_footer();
    }

    public function add_traveller_ajax()
    {
        $rules = [
            ['field' => 'fullname', 'label' => 'Full Name', 'rules' => 'trim|required'],
            ['field' => 'travel_date', 'label' => 'Travel Date', 'rules' => 'trim|required'],
            ['field' => 'email', 'label' => 'Email', 'rules' => 'trim|valid_email|required'],
            ['field' => 'c_code1', 'label' => 'Country Code', 'rules' => 'trim|required'],
            ['field' => 'phone', 'label' => 'Phone Number', 'rules' => 'trim|required'],
            ['field' => 'location', 'label' => 'Location', 'rules' => 'trim|required'],
            ['field' => 'destination', 'label' => 'Destination', 'rules' => 'trim|required'],
            ['field' => 'available_space', 'label' => 'Bag Space', 'rules' => 'trim|required'],
            ['field' => 'c_code2', 'label' => 'Alternate Country Code', 'rules' => 'trim'],
            ['field' => 'alt_phone', 'label' => 'Alternate Phone Number', 'rules' => 'trim'],
            ['field' => 'captcha_code', 'label' => 'Captcha Code', 'rules' => 'trim'],
            ['field' => 'c_captcha_code', 'label' => 'Captcha Code', 'rules' => 'trim|required|matches[captcha_code]']
        ];

        foreach ($rules as $rule) {
            $this->form_validation->set_rules(
                $rule['field'],
                $rule['label'],
                $rule['rules'],
                ['required' => 'Please enter your ' . strtolower($rule['label'])]
            );
        }

        $location = trim((string) $this->input->post('location'));
        $destination = trim((string) $this->input->post('destination'));

        $is_canada_uk_route =
            (strtolower($location) === 'canada' && strtolower($destination) === 'united kingdom') ||
            (strtolower($location) === 'united kingdom' && strtolower($destination) === 'canada');

        if ($is_canada_uk_route) {
            echo json_encode([
                'status' => false,
                'msg' => 'This route is not available right now.',
                'title' => 'Route Unavailable',
                'msg_timeout' => 6000,
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        if (strtolower($location) === strtolower($destination)) {
            echo json_encode([
                'status' => false,
                'msg' => 'Choose different locations for origin and destination.',
                'title' => 'Route Error',
                'msg_timeout' => 6000,
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        if ($this->form_validation->run() == false) {
            echo json_encode([
                'status' => false,
                'msg' => first_validation_error('Please complete the traveller form and try again.'),
                'title' => 'Traveller Form Error',
                'msg_timeout' => 6000,
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        if (empty($_FILES['itinerary_photo']['name'])) {
            echo json_encode([
                'status' => false,
                'msg' => 'Upload your itinerary to continue.',
                'title' => 'Itinerary Required',
                'msg_timeout' => 6000,
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $upload_dir = FCPATH . 'assets/itinerary/';

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0775, true)) {
                echo json_encode([
                    'status' => false,
                    'msg' => 'We could not prepare the upload folder. Please try again later.',
                    'title' => 'Upload Error',
                    'msg_timeout' => 7000,
                    'csrf_hash' => $this->security->get_csrf_hash()
                ]);
                return;
            }
        }

        if (!is_writable($upload_dir)) {
            echo json_encode([
                'status' => false,
                'msg' => 'We could not save your file right now. Please try again later.',
                'title' => 'Upload Error',
                'msg_timeout' => 7000,
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $config = [
            'upload_path'      => $upload_dir,
            'allowed_types'    => 'jpg|jpeg|png|pdf',
            'max_size'         => 5024,
            'file_ext_tolower' => true,
            'remove_spaces'    => true,
            'detect_mime'      => true,
            'encrypt_name'     => true,
        ];

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('itinerary_photo')) {
            echo json_encode([
                'status' => false,
                'msg' => normalize_user_message($this->upload->display_errors('', ''), 'We could not upload your itinerary. Please try again.'),
                'title' => 'Upload Error',
                'msg_timeout' => 7000,
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $upload_data = $this->upload->data();
        $itinerary_photo = $upload_data['file_name'];
        $file_ext = strtolower($upload_data['file_ext']);

        $thumbnail = null;

        if (in_array($file_ext, ['.jpg', '.jpeg', '.png'])) {
            $thumbnail = generate_image_thumb($itinerary_photo, '100', '100');
        }

        $this->travellers_model->add_traveller_to_db($itinerary_photo, $thumbnail);

        echo json_encode([
            'status' => true,
            'msg' => 'Thank you. One of our agents will contact you shortly.',
            'title' => 'Traveller Request Sent',
            'msg_timeout' => 7000,
            'csrf_hash' => $this->security->get_csrf_hash()
        ]);
    }



    public function investors()
    {
        $this->website_header('Investors');
        $this->load->view('website/investors');
        $this->website_footer();
    }


    public function traveller_agreement()
    {
        $this->website_header('Traveller Agreement');
        $this->load->view('website/traveller_agreement');
        $this->website_footer();
    }

    public function waiver()
    {
        $this->website_header('Liability Waiver');
        $this->load->view('website/waiver');
        $this->website_footer();
    }


    public function prohibited()
    {
        $this->website_header('Prohibited Items');
        $this->load->view('website/prohibited');
        $this->website_footer();
    }


    public function terms_of_use()
    {
        $this->website_header('Terms of Use');
        $this->load->view('website/terms_use');
        $this->website_footer();
    }


    public function terms_conditions()
    {
        $this->website_header('Terms & Conditions');
        $this->load->view('website/terms_conditions');
        $this->website_footer();
    }


    public function policy()
    {
        $this->website_header('Privacy Policy');
        $this->load->view('website/policy');
        $this->website_footer();
    }


    public function cookies()
    {
        $this->website_header('Cookies');
        $this->load->view('website/cookies');
        $this->website_footer();
    }


    public function success()
    {
        $this->website_header('Payment Successful');
        $this->load->view('website/home');
        $this->website_footer();
    }
}
