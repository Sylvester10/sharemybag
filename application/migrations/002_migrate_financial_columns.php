<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Migrate Financial Columns to DECIMAL
 * Phase 1 — Step 1.5 (DB-009)
 *
 * Eliminates IEEE 754 floating-point/varchar precision errors in financial
 * amounts by migrating them to exact DECIMAL types. Also converts space
 * columns to DECIMAL(8,2) to properly handle fractional weight (e.g., 2.5 KG).
 *
 * IMPORTANT: Run a full database backup before executing this migration.
 */
class Migration_Migrate_financial_columns extends CI_Migration
{
    public function up()
    {
        // ------------- PRE-CLEANUP -------------
        // Replace NULLs with 0 or '0' to safely apply NOT NULL constraint
        
        // Bookings
        $this->db->query("UPDATE bookings SET traveller_commission = '0' WHERE traveller_commission IS NULL OR traveller_commission = ''");
        $this->db->query("UPDATE bookings SET selected_space = '0' WHERE selected_space IS NULL OR selected_space = ''");
        $this->db->query("UPDATE bookings SET selected_price = '0' WHERE selected_price IS NULL OR selected_price = ''");
        $this->db->query("UPDATE bookings SET total_amount = 0.00 WHERE total_amount IS NULL");
        $this->db->query("UPDATE bookings SET sub_total = 0.00 WHERE sub_total IS NULL");
        $this->db->query("UPDATE bookings SET vat = 0.00 WHERE vat IS NULL");
        $this->db->query("UPDATE bookings SET service_charge = 0.00 WHERE service_charge IS NULL");

        // Travellers
        $this->db->query("UPDATE travellers SET original_bag_space = 0 WHERE original_bag_space IS NULL");
        $this->db->query("UPDATE travellers SET used_space = 0 WHERE used_space IS NULL");
        $this->db->query("UPDATE travellers SET available_space = 0 WHERE available_space IS NULL");

        // Exchange Rates
        $this->db->query("UPDATE exchange_rates SET rate = '0' WHERE rate IS NULL OR rate = ''");

        // ------------- SCHEMA MIGRATION -------------

        // 1. Bookings table
        $this->db->query("
            ALTER TABLE bookings
            MODIFY COLUMN total_amount         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            MODIFY COLUMN sub_total            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            MODIFY COLUMN vat                  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            MODIFY COLUMN service_charge       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            MODIFY COLUMN selected_price       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            MODIFY COLUMN traveller_commission DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            MODIFY COLUMN selected_space       DECIMAL(8,2)  NOT NULL DEFAULT 0.00
        ");

        // 2. Travellers table
        $this->db->query("
            ALTER TABLE travellers
            MODIFY COLUMN available_space      DECIMAL(8,2) NOT NULL DEFAULT 0.00,
            MODIFY COLUMN original_bag_space   DECIMAL(8,2) NOT NULL DEFAULT 0.00,
            MODIFY COLUMN used_space           DECIMAL(8,2) NOT NULL DEFAULT 0.00
        ");

        // 3. Exchange Rates table
        $this->db->query("
            ALTER TABLE exchange_rates
            MODIFY COLUMN rate DECIMAL(12,4) NOT NULL DEFAULT 0.0000
        ");
    }

    public function down()
    {
        // Reverting schema changes (lossy down-migration back to original types)
        
        // 1. Bookings table
        $this->db->query("
            ALTER TABLE bookings
            MODIFY COLUMN total_amount         DECIMAL(10,2) NULL DEFAULT NULL,
            MODIFY COLUMN sub_total            DECIMAL(10,2) NULL DEFAULT NULL,
            MODIFY COLUMN vat                  DECIMAL(10,2) NULL DEFAULT NULL,
            MODIFY COLUMN service_charge       DECIMAL(10,2) NULL DEFAULT NULL,
            MODIFY COLUMN selected_price       VARCHAR(20) NULL DEFAULT NULL,
            MODIFY COLUMN traveller_commission VARCHAR(100) NULL DEFAULT NULL,
            MODIFY COLUMN selected_space       VARCHAR(20) NULL DEFAULT NULL
        ");

        // 2. Travellers table
        $this->db->query("
            ALTER TABLE travellers
            MODIFY COLUMN available_space      INT(20) NULL DEFAULT NULL,
            MODIFY COLUMN original_bag_space   INT(20) NULL DEFAULT NULL,
            MODIFY COLUMN used_space           INT(20) NULL DEFAULT NULL
        ");

        // 3. Exchange Rates table
        $this->db->query("
            ALTER TABLE exchange_rates
            MODIFY COLUMN rate VARCHAR(10) NULL DEFAULT NULL
        ");
    }
}
