<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_booking_action_audit_logs extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists('booking_action_logs')) {
            return;
        }

        // No cascading foreign keys are used here. Audit records must remain
        // available even when a related operational record is later archived.
        $this->db->query("
            CREATE TABLE booking_action_logs (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                booking_id INT(11) NOT NULL,
                booking_reference VARCHAR(50) DEFAULT NULL,
                action VARCHAR(30) NOT NULL,
                from_traveller_id INT(11) DEFAULT NULL,
                to_traveller_id INT(11) DEFAULT NULL,
                admin_id INT(11) NOT NULL,
                reason TEXT NOT NULL,
                refund_status VARCHAR(30) DEFAULT NULL,
                refund_reference VARCHAR(191) DEFAULT NULL,
                refund_amount DECIMAL(12,2) DEFAULT NULL,
                currency CHAR(3) DEFAULT NULL,
                before_snapshot LONGTEXT DEFAULT NULL,
                after_snapshot LONGTEXT DEFAULT NULL,
                date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_booking_action_logs_booking (booking_id),
                KEY idx_booking_action_logs_action (action),
                KEY idx_booking_action_logs_from_traveller (from_traveller_id),
                KEY idx_booking_action_logs_to_traveller (to_traveller_id),
                KEY idx_booking_action_logs_admin (admin_id),
                KEY idx_booking_action_logs_date (date_added)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    public function down()
    {
        if ($this->db->table_exists('booking_action_logs')) {
            $this->db->query('DROP TABLE booking_action_logs');
        }
    }
}
