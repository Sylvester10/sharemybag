<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Add Performance Indexes
 * Phase 1 — Step 1.4 (DB-006, PERF-002)
 *
 * Adds indexes on frequently queried columns across bookings, travellers,
 * users, and shipping tables to eliminate full table scans.
 *
 * IMPORTANT: Run a full database backup before executing this migration.
 * mysqldump --single-transaction --routines --triggers smb_db > backup_before_indexes.sql
 */
class Migration_Add_performance_indexes extends CI_Migration
{
    public function up()
    {
        // ── bookings table ──────────────────────────────────────────

        // Covers: revenue reports filtered by payment_status + currency + payment_method
        // Query: SELECT SUM(total_amount) FROM bookings WHERE payment_status = 'completed' AND currency = 'pounds'
        $this->db->query("ALTER TABLE bookings ADD INDEX idx_bookings_payment_currency (payment_status, currency, payment_method)");

        // Covers: traveller space calculation — SUM(selected_space) WHERE traveller_id = ? AND payment_status = 'completed'
        $this->db->query("ALTER TABLE bookings ADD INDEX idx_bookings_traveller_payment (traveller_id, payment_status)");

        // Covers: user booking history — WHERE user_id = ? ORDER BY date_added DESC
        $this->db->query("ALTER TABLE bookings ADD INDEX idx_bookings_user_date (user_id, date_added)");

        // Covers: booking lookup by hash (public tracking links)
        $this->db->query("ALTER TABLE bookings ADD INDEX idx_bookings_hash (hash)");

        // Covers: shipping association and delivery status updates — WHERE tracking_id = ?
        $this->db->query("ALTER TABLE bookings ADD INDEX idx_bookings_tracking (tracking_id)");

        // Covers: Paystack payment verification webhook — WHERE paystack_ref = ?
        $this->db->query("ALTER TABLE bookings ADD INDEX idx_bookings_paystack_ref (paystack_ref)");


        // ── travellers table ────────────────────────────────────────

        // Covers: approved traveller listings — WHERE status = 'Approved' ORDER BY travel_date ASC
        $this->db->query("ALTER TABLE travellers ADD INDEX idx_travellers_status_date (status, travel_date)");

        // Covers: traveller lookup by hash (public links)
        $this->db->query("ALTER TABLE travellers ADD INDEX idx_travellers_hash (hash)");

        // Covers: destination search — WHERE destination = ? AND status = 'Approved' ORDER BY travel_date
        $this->db->query("ALTER TABLE travellers ADD INDEX idx_travellers_destination (destination, status, travel_date)");


        // ── users table ─────────────────────────────────────────────

        // Covers: login, password reset — WHERE email = ?
        $this->db->query("ALTER TABLE users ADD INDEX idx_users_email (email)");

        // Covers: referral lookup — WHERE username = ?
        $this->db->query("ALTER TABLE users ADD INDEX idx_users_username (username)");

        // Covers: admin user listing by verification state — WHERE is_verified = ? ORDER BY date_added
        $this->db->query("ALTER TABLE users ADD INDEX idx_users_verified (is_verified, date_added)");


        // ── shipping table ──────────────────────────────────────────

        // Covers: shipping timeline lookup — WHERE tracking_id = ?
        $this->db->query("ALTER TABLE shipping ADD INDEX idx_shipping_tracking (tracking_id)");
    }


    public function down()
    {
        // ── bookings ──
        $this->db->query("ALTER TABLE bookings DROP INDEX idx_bookings_payment_currency");
        $this->db->query("ALTER TABLE bookings DROP INDEX idx_bookings_traveller_payment");
        $this->db->query("ALTER TABLE bookings DROP INDEX idx_bookings_user_date");
        $this->db->query("ALTER TABLE bookings DROP INDEX idx_bookings_hash");
        $this->db->query("ALTER TABLE bookings DROP INDEX idx_bookings_tracking");
        $this->db->query("ALTER TABLE bookings DROP INDEX idx_bookings_paystack_ref");

        // ── travellers ──
        $this->db->query("ALTER TABLE travellers DROP INDEX idx_travellers_status_date");
        $this->db->query("ALTER TABLE travellers DROP INDEX idx_travellers_hash");
        $this->db->query("ALTER TABLE travellers DROP INDEX idx_travellers_destination");

        // ── users ──
        $this->db->query("ALTER TABLE users DROP INDEX idx_users_email");
        $this->db->query("ALTER TABLE users DROP INDEX idx_users_username");
        $this->db->query("ALTER TABLE users DROP INDEX idx_users_verified");

        // ── shipping ──
        $this->db->query("ALTER TABLE shipping DROP INDEX idx_shipping_tracking");
    }
}
