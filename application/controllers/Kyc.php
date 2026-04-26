<?php
defined('BASEPATH') or die('Direct access not allowed');


class Kyc extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->user_restricted(); //allow only logged in users to access this class
        $this->load->model('users_model');
        $this->load->model('user_read_model');
        $this->user_details = $this->user_read_model->get_user_details($this->session->email);
    }



    /* ====== KYC ====== */
    public function index()
    { //user dashboard, routed as dashboard
        // $this->return_to_user_dashboard(); //return user to dashboard if still loggedin
        $this->dashboard_header('Identity Verification');
        $data['user_id'] = $this->user_details->id;
        $data['user_details'] = $this->user_details;
        $this->load->view('users/kyc', $data);
        $this->dashboard_footer();
    }


    public function verify_ajax()
    {
        $csrf_hash = $this->security->get_csrf_hash();
        // Validate file size
        $validate_image = $this->validate_file_upload('id_photo', 'Image Upload ERROR');
        $validate_selfie = $this->validate_file_upload('selfie', 'Selfie Image - ERROR');
        $validate_document = $this->validate_file_upload('utility', 'Utility Bill - ERROR');

        // Check for validation errors
        if ($validate_image || $validate_selfie || $validate_document) {
            $error_list = trim(($validate_image ? $validate_image : '') . ($validate_selfie ? $validate_selfie : '') . ($validate_document ? $validate_document : ''));
            $res = ['status' => false, 'msg' => normalize_user_message($error_list, 'Please check your uploaded files and try again.'), 'title' => 'Upload Error', 'msg_timeout' => 7000, 'csrf_hash' => $csrf_hash];
            echo json_encode($res); // Show validation errors
            return;
        }

        // User details validation
        $this->form_validation->set_rules('id_type', 'ID Type', 'trim|required');
        $this->form_validation->set_rules('platform', 'Social media platform', 'trim|required');

        if ($this->form_validation->run()) {
            $this->load->library('upload');
            $upload_error = "";
            $id_photo = '';
            $selfie = NULL;
            $utility = '';

            // Upload ID photo
            if (!empty($_FILES['id_photo']['name'])) {
                $upload_error .= $this->upload_file('id_photo', './assets/id_cards', 'jpg|jpeg|png');
                if ($upload_error === '') {
                    $upload_data = $this->upload->data();
                    $id_photo = $upload_data['file_name'];
                }
            }

            if (!empty($_FILES['selfie']['name'])) {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
                $file_type = $_FILES['selfie']['type'];

                // Check if the MIME type is actually recognized
                if (!in_array($file_type, $allowed_types)) {
                    $res = [
                        'status' => false,
                        'msg' => 'Upload the selfie as a JPG or PNG image.',
                        'title' => 'Unsupported File',
                        'msg_timeout' => 7000,
                        'csrf_hash' => $csrf_hash
                    ];
                    echo json_encode($res);
                    return;
                }

                $upload_error .= $this->upload_file('selfie', './assets/selfie', 'jpg|jpeg|png');
                if ($upload_error === '') {
                    $upload_data = $this->upload->data();
                    $selfie = $upload_data['file_name'];
                }
            }

            // Upload utility
            if (!empty($_FILES['utility']['name'])) {
                $upload_error .= $this->upload_file('utility', './assets/utility', 'jpg|jpeg|png|pdf');
                if ($upload_error === '') {
                    $upload_data = $this->upload->data();
                    $utility = $upload_data['file_name'];
                }
            }

            // Check for any upload errors
            if (!empty($upload_error)) {
                $res = ['status' => false, 'msg' => normalize_user_message($upload_error, 'We could not upload your documents. Please try again.'), 'title' => 'Upload Error', 'msg_timeout' => 7000, 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
                return;
            }

            // Get user details
            $user_id = $this->user_details->id;
            $email = $this->user_details->email;

            // Verify document in the database
            if ($this->users_model->verify_document($user_id, $id_photo, $selfie, $utility)) {

                // Get user data for sending the email
                $data = [
                    'firstname' => $this->user_details->firstname,
                    'id_type' => $this->input->post('id_type') // Retrieve the posted ID type
                ];

                // Send email to User
                send_email_notification($this, $email, 'Documents Under Review', $data, 'user_document_verification_email');

                $res = ['status' => true, 'msg' => 'You will be notified via email on your verification status.', 'title' => 'Documents submitted successfully.', 'msg_timeout' =>  10000, 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
            } else {
                $res = ['status' => false, 'msg' => 'We could not submit your documents right now. Please try again.', 'title' => 'Submission Failed', 'msg_timeout' => 7000, 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
            }
        } else {
            $res = ['status' => false, 'msg' => first_validation_error('Please complete the KYC form and try again.'), 'title' => 'Verification Error', 'msg_timeout' => 7000, 'csrf_hash' => $csrf_hash];
            echo json_encode($res); // Show validation errors
        }
    }
}
