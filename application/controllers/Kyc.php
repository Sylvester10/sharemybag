<?php
defined('BASEPATH') or die('Direct access not allowed');


class Kyc extends MY_Controller
{
    const KYC_MAX_FILE_SIZE = 5242880;

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
        $this->enforce_complete_profile();
        // $this->return_to_user_dashboard(); //return user to dashboard if still loggedin
        $this->dashboard_header('Identity Verification');
        $data['user_id'] = $this->user_details->id;
        $data['user_details'] = $this->user_details;
        $this->load->view('users/kyc', $data);
        $this->dashboard_footer();
    }


    public function verify_ajax()
    {
        if ($this->users_model->is_profile_incomplete($this->user_details->id)) {
            echo json_encode([
                'status' => false,
                'msg' => 'Complete your profile before verifying your identity.',
                'title' => 'Complete Your Profile',
                'msg_timeout' => 7000,
                'redirect' => base_url('profile'),
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $csrf_hash = $this->security->get_csrf_hash();
        $upload_validation_error = $this->validate_kyc_uploads();
        if ($upload_validation_error) {
            echo json_encode(array_merge($upload_validation_error, ['csrf_hash' => $csrf_hash]));
            return;
        }

        // User details validation
        $this->form_validation->set_rules('id_type', 'ID Type', 'trim|required');
        $this->form_validation->set_rules('platform', 'Social media platform', 'trim|required');

        if ($this->form_validation->run()) {
            $this->load->library('upload');
            $id_photo = '';
            $selfie = NULL;
            $utility = '';

            // Upload ID photo
            if (!empty($_FILES['id_photo']['name'])) {
                $upload_error = $this->upload_file('id_photo', './assets/id_cards', 'jpg|jpeg|png');
                if ($upload_error !== '') {
                    echo json_encode(array_merge($this->build_kyc_upload_error('id_photo', 'We could not upload your ID card. Upload it as a JPG or PNG image and try again.'), ['csrf_hash' => $csrf_hash]));
                    return;
                }

                $upload_data = $this->upload->data();
                $id_photo = $upload_data['file_name'];
            }

            if (!empty($_FILES['selfie']['name'])) {
                $upload_error = $this->upload_file('selfie', './assets/selfie', 'jpg|jpeg|png');
                if ($upload_error !== '') {
                    echo json_encode(array_merge($this->build_kyc_upload_error('selfie', 'We could not upload your selfie. Upload it as a JPG or PNG image and try again.'), ['csrf_hash' => $csrf_hash]));
                    return;
                }

                $upload_data = $this->upload->data();
                $selfie = $upload_data['file_name'];
            }

            // Upload utility
            if (!empty($_FILES['utility']['name'])) {
                $upload_error = $this->upload_file('utility', './assets/utility', 'jpg|jpeg|png|pdf');
                if ($upload_error !== '') {
                    echo json_encode(array_merge($this->build_kyc_upload_error('utility', 'We could not upload your proof of address. Upload it as a JPG, PNG, or PDF file and try again.'), ['csrf_hash' => $csrf_hash]));
                    return;
                }

                $upload_data = $this->upload->data();
                $utility = $upload_data['file_name'];
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
            $res = array_merge(
                [
                    'status' => false,
                    'msg' => first_validation_error('Please complete the KYC form and try again.'),
                    'title' => 'Verification Error',
                    'msg_timeout' => 7000,
                    'csrf_hash' => $csrf_hash
                ],
                $this->get_kyc_validation_step_error()
            );
            echo json_encode($res); // Show validation errors
        }
    }

    private function validate_kyc_uploads()
    {
        foreach ($this->get_kyc_upload_rules() as $field => $rule) {
            if (empty($_FILES[$field]['name'])) {
                continue;
            }

            $file_size = isset($_FILES[$field]['size']) ? (int) $_FILES[$field]['size'] : 0;
            if ($file_size > self::KYC_MAX_FILE_SIZE) {
                return $this->build_kyc_upload_error($field, $rule['size_message']);
            }

            $extension = strtolower((string) pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            if ($extension === '' || !in_array($extension, $rule['extensions'], true)) {
                return $this->build_kyc_upload_error($field, $rule['type_message']);
            }

            $mime_type = strtolower((string) ($_FILES[$field]['type'] ?? ''));
            if ($mime_type !== '' && !in_array($mime_type, $rule['mime_types'], true)) {
                return $this->build_kyc_upload_error($field, $rule['type_message']);
            }
        }

        return null;
    }

    private function get_kyc_upload_rules()
    {
        $is_nigeria = $this->is_nigeria_kyc();

        return [
            'id_photo' => [
                'title' => 'ID Upload Error',
                'step' => 1,
                'extensions' => ['jpg', 'jpeg', 'png'],
                'mime_types' => ['image/jpeg', 'image/jpg', 'image/png'],
                'type_message' => 'Upload your ID card as a JPG or PNG image.',
                'size_message' => 'Your ID card file must be 5MB or less.',
            ],
            'utility' => [
                'title' => 'Proof of Address Error',
                'step' => $is_nigeria ? 2 : 1,
                'extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                'mime_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
                'type_message' => 'Upload your proof of address as a JPG, PNG, or PDF file.',
                'size_message' => 'Your proof of address file must be 5MB or less.',
            ],
            'selfie' => [
                'title' => 'Selfie Upload Error',
                'step' => $is_nigeria ? 4 : 3,
                'extensions' => ['jpg', 'jpeg', 'png'],
                'mime_types' => ['image/jpeg', 'image/jpg', 'image/png'],
                'type_message' => 'Upload your selfie as a JPG or PNG image.',
                'size_message' => 'Your selfie file must be 5MB or less.',
            ],
        ];
    }

    private function build_kyc_upload_error($field, $message)
    {
        $rules = $this->get_kyc_upload_rules();
        $rule = isset($rules[$field]) ? $rules[$field] : ['title' => 'Upload Error', 'step' => 0];

        return [
            'status' => false,
            'title' => $rule['title'],
            'msg' => $message,
            'msg_timeout' => 7000,
            'error_fields' => [$field],
            'error_steps' => [$rule['step']],
        ];
    }

    private function get_kyc_validation_step_error()
    {
        $is_nigeria = $this->is_nigeria_kyc();

        if (form_error('id_type')) {
            return ['error_fields' => ['id_type'], 'error_steps' => [1]];
        }

        if (form_error('platform')) {
            return ['error_fields' => ['platform'], 'error_steps' => [$is_nigeria ? 3 : 2]];
        }

        return [];
    }

    private function is_nigeria_kyc()
    {
        return strtolower(trim((string) $this->user_details->country)) === 'nigeria';
    }

    private function enforce_complete_profile()
    {
        if ($this->users_model->is_profile_incomplete($this->user_details->id)) {
            $this->session->set_flashdata('status_msg_error', 'Complete your profile before verifying your identity.');
            redirect(base_url('profile'));
        }
    }
}
