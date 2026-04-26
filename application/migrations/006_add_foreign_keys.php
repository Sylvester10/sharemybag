<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Add Foreign Keys
 * Phase 1 — Step 1.11 (DB-008)
 *
 * Enforces the core parent/child relationships that the application already
 * assumes in code:
 * - bookings.traveller_id -> travellers.id (RESTRICT)
 * - bookings.user_id -> users.id (RESTRICT)
 * - shipping.tracking_id -> bookings.tracking_id (CASCADE)
 *
 * Legacy cleanup is performed before constraints are added so the migration
 * can execute on inconsistent historical data.
 */
class Migration_Add_foreign_keys extends CI_Migration
{
    public function up()
    {
        $this->normalizeBookingUserIds();
        $this->deleteOrphanBookings();
        $this->repairMissingBookingTrackingIds();
        $this->deduplicateBookingTrackingIds();
        $this->deleteOrphanShippingRows();
        $this->ensureUniqueBookingTrackingIndex();
        $this->addForeignKeys();
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('shipping', 'fk_shipping_booking_tracking');
        $this->dropForeignKeyIfExists('bookings', 'fk_bookings_user');
        $this->dropForeignKeyIfExists('bookings', 'fk_bookings_traveller');

        if ($this->hasIndex('bookings', 'uniq_bookings_tracking')) {
            $this->db->query("ALTER TABLE bookings DROP INDEX uniq_bookings_tracking");
        }

        if (!$this->hasIndex('bookings', 'idx_bookings_tracking')) {
            $this->db->query("ALTER TABLE bookings ADD INDEX idx_bookings_tracking (tracking_id)");
        }
    }

    private function normalizeBookingUserIds()
    {
        // Offline/admin-created bookings legitimately have no user account.
        $this->db->query("UPDATE bookings SET user_id = NULL WHERE user_id = 0");

        // Null invalid user references rather than deleting the booking record.
        $this->db->query(
            "UPDATE bookings b
            LEFT JOIN users u ON b.user_id = u.id
            SET b.user_id = NULL
            WHERE b.user_id IS NOT NULL
              AND u.id IS NULL"
        );
    }

    private function deleteOrphanBookings()
    {
        // Bookings without a valid traveller cannot be repaired safely.
        $this->db->query(
            "DELETE b FROM bookings b
            LEFT JOIN travellers t ON b.traveller_id = t.id
            WHERE b.traveller_id IS NOT NULL
              AND t.id IS NULL"
        );
    }

    private function deduplicateBookingTrackingIds()
    {
        $duplicates = $this->db->query(
            "SELECT tracking_id
            FROM bookings
            WHERE tracking_id IS NOT NULL
              AND tracking_id <> ''
            GROUP BY tracking_id
            HAVING COUNT(*) > 1"
        )->result();

        foreach ($duplicates as $duplicate) {
            $rows = $this->db->query(
                "SELECT id, tracking_id
                FROM bookings
                WHERE tracking_id = ?
                ORDER BY id ASC",
                array($duplicate->tracking_id)
            )->result();

            // Keep the oldest booking on the legacy tracking ID.
            for ($i = 1; $i < count($rows); $i++) {
                $replacement = $this->buildUniqueTrackingId($rows[$i]->id);

                $this->db->where('id', $rows[$i]->id);
                $this->db->update('bookings', array('tracking_id' => $replacement));
            }
        }
    }

    private function repairMissingBookingTrackingIds()
    {
        $rows = $this->db->query(
            "SELECT id
            FROM bookings
            WHERE tracking_id IS NULL
               OR tracking_id = ''"
        )->result();

        foreach ($rows as $row) {
            $replacement = $this->buildUniqueTrackingId($row->id);

            $this->db->where('id', $row->id);
            $this->db->update('bookings', array('tracking_id' => $replacement));
        }
    }

    private function deleteOrphanShippingRows()
    {
        $this->db->query(
            "DELETE s FROM shipping s
            LEFT JOIN bookings b ON s.tracking_id = b.tracking_id
            WHERE b.tracking_id IS NULL"
        );
    }

    private function ensureUniqueBookingTrackingIndex()
    {
        if ($this->hasIndex('bookings', 'idx_bookings_tracking')) {
            $this->db->query("ALTER TABLE bookings DROP INDEX idx_bookings_tracking");
        }

        if (!$this->hasIndex('bookings', 'uniq_bookings_tracking')) {
            $this->db->query("ALTER TABLE bookings ADD UNIQUE INDEX uniq_bookings_tracking (tracking_id)");
        }
    }

    private function addForeignKeys()
    {
        if (!$this->hasForeignKey('bookings', 'fk_bookings_traveller')) {
            $this->db->query(
                "ALTER TABLE bookings
                ADD CONSTRAINT fk_bookings_traveller
                FOREIGN KEY (traveller_id) REFERENCES travellers(id)
                ON DELETE RESTRICT
                ON UPDATE CASCADE"
            );
        }

        if (!$this->hasForeignKey('bookings', 'fk_bookings_user')) {
            $this->db->query(
                "ALTER TABLE bookings
                ADD CONSTRAINT fk_bookings_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE RESTRICT
                ON UPDATE CASCADE"
            );
        }

        if (!$this->hasForeignKey('shipping', 'fk_shipping_booking_tracking')) {
            $this->db->query(
                "ALTER TABLE shipping
                ADD CONSTRAINT fk_shipping_booking_tracking
                FOREIGN KEY (tracking_id) REFERENCES bookings(tracking_id)
                ON DELETE CASCADE
                ON UPDATE CASCADE"
            );
        }
    }

    private function buildUniqueTrackingId($bookingId)
    {
        $seed = 0;
        $candidate = $this->formatTrackingCandidate($bookingId, $seed);

        while ($this->trackingIdExists($candidate)) {
            $seed++;
            $candidate = $this->formatTrackingCandidate($bookingId, $seed);
        }

        return $candidate;
    }

    private function formatTrackingCandidate($bookingId, $seed)
    {
        return 'SMB' . strtoupper(substr(md5('booking:' . $bookingId . ':' . $seed), 0, 7));
    }

    private function trackingIdExists($trackingId)
    {
        return $this->db
            ->where('tracking_id', $trackingId)
            ->count_all_results('bookings') > 0;
    }

    private function hasIndex($table, $indexName)
    {
        $query = $this->db->query(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            array($indexName)
        );

        return $query->num_rows() > 0;
    }

    private function hasForeignKey($table, $constraintName)
    {
        $query = $this->db->query(
            "SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME = ?",
            array($table, $constraintName)
        );

        return $query->num_rows() > 0;
    }

    private function dropForeignKeyIfExists($table, $constraintName)
    {
        if ($this->hasForeignKey($table, $constraintName)) {
            $this->db->query("ALTER TABLE {$table} DROP FOREIGN KEY {$constraintName}");
        }
    }
}
