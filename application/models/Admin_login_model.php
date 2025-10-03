<?php
defined('BASEPATH') or exit('Direct access to script not allowed');



class Admin_login_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }



    public function check_admin_email_exists($email)
    {
        $email_exists = $this->db->get_where('admins', array('email' => $email))->row();
        return ($email_exists) ? TRUE : FALSE;
    }


    public function update_pass_reset_code($id, $pass_reset_code)
    {
        $data = array(
            'pass_reset_code' => $pass_reset_code
        );
        $this->db->where('id', $id);
        return $this->db->update('admins', $data);
    }


    public function validate_pass_reset_code($id, $pass_reset_code)
    {
        //validate password reset code
        $code = $this->common_model->get_admin_details_by_id($id)->pass_reset_code;
        return ($code == $pass_reset_code) ? TRUE : FALSE;
    }


    public function change_password($id)
    {
        $password = hash('ripemd128', $this->input->post('password', TRUE));
        $data = array(
            'password' => $password,
            'pass_reset_code' => NULL  //destroy the code
        );
        $this->db->where('id', $id);
        return $this->db->update('admins', $data);
    }
}
