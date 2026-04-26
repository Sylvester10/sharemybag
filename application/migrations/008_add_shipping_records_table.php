<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_shipping_records_table extends CI_Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS shipping_records (
                id INT(11) NOT NULL AUTO_INCREMENT,
                booking_id INT(11) NOT NULL,
                tracking_id VARCHAR(20) NOT NULL,
                pickup_address VARCHAR(300) DEFAULT NULL,
                dropoff_address VARCHAR(300) DEFAULT NULL,
                pickup_country VARCHAR(100) DEFAULT NULL,
                courier VARCHAR(100) DEFAULT NULL,
                staff_admin_id INT(11) DEFAULT NULL,
                staff_name VARCHAR(100) DEFAULT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'In Transit',
                date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                date_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_shipping_records_booking (booking_id),
                UNIQUE KEY uniq_shipping_records_tracking (tracking_id),
                KEY idx_shipping_records_status (status),
                KEY idx_shipping_records_staff (staff_admin_id),
                CONSTRAINT fk_shipping_records_booking
                    FOREIGN KEY (booking_id) REFERENCES bookings(id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_shipping_records_tracking
                    FOREIGN KEY (tracking_id) REFERENCES bookings(tracking_id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_shipping_records_admin
                    FOREIGN KEY (staff_admin_id) REFERENCES admins(id)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS shipping_records");
    }
}
