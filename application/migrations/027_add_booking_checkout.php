<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_booking_checkout extends CI_Migration
{
    public function up()
    {
        $sql = file_get_contents(FCPATH . 'database/027_booking_checkout.sql');
        if ($sql === false) { throw new RuntimeException('Checkout SQL file is missing.'); }
        foreach (explode(';', preg_replace('/^--.*$/m', '', $sql)) as $statement) {
            if (trim($statement) !== '' && !$this->db->query($statement)) {
                throw new RuntimeException('Checkout migration failed.');
            }
        }
    }

    public function down()
    {
        throw new RuntimeException('Checkout rollback must preserve payment and reservation history.');
    }
}
