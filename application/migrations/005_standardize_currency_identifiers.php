<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Standardize Currency Identifiers
 * Phase 1 — Step 1.10 (DB-007)
 *
 * Converts legacy currency labels to ISO 4217 codes across exchange_rates
 * and bookings so rate lookup, reporting, and payment flows use one format.
 */
class Migration_Standardize_currency_identifiers extends CI_Migration
{
    public function up()
    {
        $this->db->query("
            UPDATE exchange_rates
            SET currency = CASE
                WHEN currency IS NULL OR TRIM(currency) = '' THEN currency
                WHEN LOWER(TRIM(currency)) IN ('pound', 'pounds', 'gbp') THEN 'GBP'
                WHEN LOWER(TRIM(currency)) IN ('cad', 'dollar', 'dollars') THEN 'CAD'
                WHEN LOWER(TRIM(currency)) IN ('naira', 'ngn') THEN 'NGN'
                ELSE UPPER(TRIM(currency))
            END
        ");

        $this->db->query("
            UPDATE bookings
            SET currency = CASE
                WHEN currency IS NULL OR TRIM(currency) = '' THEN currency
                WHEN LOWER(TRIM(currency)) IN ('pound', 'pounds', 'gbp') THEN 'GBP'
                WHEN LOWER(TRIM(currency)) IN ('cad', 'dollar', 'dollars') THEN 'CAD'
                WHEN LOWER(TRIM(currency)) IN ('naira', 'ngn') THEN 'NGN'
                ELSE UPPER(TRIM(currency))
            END
        ");

        $this->db->query("
            ALTER TABLE exchange_rates
            MODIFY COLUMN currency CHAR(3) NOT NULL
        ");

        $this->db->query("
            ALTER TABLE bookings
            MODIFY COLUMN currency CHAR(3) NULL DEFAULT NULL
        ");
    }

    public function down()
    {
        $this->db->query("
            UPDATE exchange_rates
            SET currency = CASE
                WHEN currency = 'GBP' THEN 'pound'
                WHEN currency = 'CAD' THEN 'cad'
                WHEN currency = 'NGN' THEN 'naira'
                ELSE LOWER(currency)
            END
        ");

        $this->db->query("
            UPDATE bookings
            SET currency = CASE
                WHEN currency = 'GBP' THEN 'pounds'
                WHEN currency = 'CAD' THEN 'dollars'
                WHEN currency = 'NGN' THEN 'naira'
                ELSE LOWER(currency)
            END
        ");

        $this->db->query("
            ALTER TABLE exchange_rates
            MODIFY COLUMN currency VARCHAR(20) NULL DEFAULT NULL
        ");

        $this->db->query("
            ALTER TABLE bookings
            MODIFY COLUMN currency VARCHAR(20) NULL DEFAULT NULL
        ");
    }
}
