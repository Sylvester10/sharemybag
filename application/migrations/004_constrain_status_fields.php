<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Constrain Status Fields
 * Phase 1 — Step 1.9 (DB-002)
 *
 * Normalizes legacy status strings into a fixed vocabulary and then converts
 * the core workflow columns to ENUMs.
 */
class Migration_Constrain_status_fields extends CI_Migration
{
    public function up()
    {
        $this->db->query("
            UPDATE bookings
            SET payment_status = CASE
                WHEN payment_status IS NULL OR TRIM(payment_status) = '' THEN 'pending'
                WHEN LOWER(TRIM(payment_status)) IN ('completed', 'complete', 'success', 'paid') THEN 'completed'
                WHEN LOWER(TRIM(payment_status)) IN ('canceled', 'cancelled', 'failed', 'declined') THEN 'canceled'
                ELSE 'pending'
            END
        ");

        $this->db->query("
            UPDATE bookings
            SET status = CASE
                WHEN status IS NULL OR TRIM(status) = '' THEN 'Pending'
                WHEN LOWER(TRIM(status)) IN ('pending', 'booking pending') THEN 'Pending'
                WHEN LOWER(TRIM(status)) IN ('approved', 'booking approved') THEN 'Approved'
                WHEN LOWER(TRIM(status)) IN ('declined', 'booking declined') THEN 'Declined'
                WHEN LOWER(TRIM(status)) = 'available' THEN 'Available'
                WHEN LOWER(TRIM(status)) = 'unavailable' THEN 'Unavailable'
                ELSE 'Pending'
            END
        ");

        $this->db->query("
            UPDATE bookings
            SET delivery_status = CASE
                WHEN delivery_status IS NULL OR TRIM(delivery_status) = '' THEN 'Pending'
                WHEN LOWER(TRIM(delivery_status)) IN ('pending', 'booking pending') THEN 'Pending'
                WHEN LOWER(TRIM(delivery_status)) = 'shipment created' THEN 'Shipment Created'
                WHEN LOWER(TRIM(delivery_status)) = 'in transit' THEN 'In Transit'
                WHEN LOWER(TRIM(delivery_status)) = 'delivered' THEN 'Delivered'
                ELSE 'Pending'
            END
        ");

        $this->db->query("
            UPDATE travellers
            SET status = CASE
                WHEN status IS NULL OR TRIM(status) = '' THEN 'Pending'
                WHEN LOWER(TRIM(status)) = 'pending' THEN 'Pending'
                WHEN LOWER(TRIM(status)) = 'approved' THEN 'Approved'
                WHEN LOWER(TRIM(status)) = 'unapproved' THEN 'Unapproved'
                ELSE 'Pending'
            END
        ");

        $this->db->query("
            ALTER TABLE bookings
            MODIFY COLUMN payment_status ENUM('pending', 'completed', 'canceled') NOT NULL DEFAULT 'pending',
            MODIFY COLUMN status ENUM('Pending', 'Approved', 'Declined', 'Available', 'Unavailable') NOT NULL DEFAULT 'Pending',
            MODIFY COLUMN delivery_status ENUM('Pending', 'Shipment Created', 'In Transit', 'Delivered') NOT NULL DEFAULT 'Pending'
        ");

        $this->db->query("
            ALTER TABLE travellers
            MODIFY COLUMN status ENUM('Pending', 'Approved', 'Unapproved') NOT NULL DEFAULT 'Pending'
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE bookings
            MODIFY COLUMN payment_status VARCHAR(255) NULL DEFAULT NULL,
            MODIFY COLUMN status VARCHAR(255) NULL DEFAULT NULL,
            MODIFY COLUMN delivery_status VARCHAR(255) NULL DEFAULT NULL
        ");

        $this->db->query("
            ALTER TABLE travellers
            MODIFY COLUMN status VARCHAR(255) NULL DEFAULT NULL
        ");
    }
}
