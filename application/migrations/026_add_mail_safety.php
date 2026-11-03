<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Add_mail_safety extends CI_Migration
{
    public function up()
    {
        $sql = file_get_contents(FCPATH . 'database/026_mail_safety.sql');
        if ($sql === false) { throw new RuntimeException('Mail safety SQL file is missing.'); }
        $sql = preg_replace('/^--.*$/m', '', $sql);
        foreach (explode(';', $sql) as $statement) {
            if (trim($statement) !== '' && !$this->db->query($statement)) {
                throw new RuntimeException('Mail safety migration failed.');
            }
        }
    }
    public function down()
    {
        // Outbox and delivery history require explicit operator review before removal.
        throw new RuntimeException('Mail safety rollback must preserve queued messages and delivery history.');
    }
}
