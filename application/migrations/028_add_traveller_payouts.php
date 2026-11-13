<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_traveller_payouts extends CI_Migration
{
    public function up()
    {
        $sql = file_get_contents(FCPATH . 'database/028_traveller_payouts.sql');
        if ($sql === false) {
            throw new RuntimeException('Traveller payout SQL file is missing.');
        }
        foreach (explode(';', preg_replace('/^--.*$/m', '', $sql)) as $statement) {
            if (trim($statement) !== '' && !$this->db->query($statement)) {
                throw new RuntimeException('Traveller payout migration failed.');
            }
        }
    }

    public function down()
    {
        throw new RuntimeException('Traveller payout history must be preserved. Use a reviewed forward migration.');
    }
}
