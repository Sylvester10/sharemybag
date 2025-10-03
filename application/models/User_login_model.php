<?php
defined('BASEPATH') or exit('Direct access to script not allowed');


class User_login_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }




    public function check_user_email_exists($email)
    {
        $email_exists = $this->db->get_where('users', array('email' => $email))->row();
        return ($email_exists) ? TRUE : FALSE;
    }


    public function update_pass_code($id, $pass_reset_code)
    {
        $data = array(
            'pass_reset_code' => $pass_reset_code
        );
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }


    public function validate_pass_reset_code($id, $pass_reset_code)
    {
        //validate password reset code
        $code = $this->common_model->get_user_details_by_id($id)->pass_reset_code;
        return ($code == $pass_reset_code) ? TRUE : FALSE;
    }


    public function change_pass($id)
    {
        $password = password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT);
        $pass_reset_code = generate_verification_code();
        $data = array(
            'password' => $password,
            'pass_reset_code' => $pass_reset_code,
        );
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
}
