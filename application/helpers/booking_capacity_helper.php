<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Call under the traveller row lock when deciding or changing an allocation. */
function booking_capacity_snapshot($db, $traveller, $exclude_attempt_id = 0)
{
    if (!$traveller || !empty($traveller->deleted_at)) { return false; }
    $confirmed = $db->query("SELECT COALESCE(SUM(selected_space), 0) AS units FROM bookings
        WHERE traveller_id = ? AND deleted_at IS NULL
        AND LOWER(TRIM(COALESCE(payment_status, ''))) IN ('completed','complete','success','paid')", [(int) $traveller->id]);
    if (!$confirmed) { return false; }
    $used = (float) $confirmed->row()->units;
    $held = 0.0;
    if ($db->table_exists('booking_payment_attempts')) {
        $query = $db->query("SELECT COALESCE(SUM(p.hold_space), 0) AS units
            FROM booking_payment_attempts p INNER JOIN bookings b ON b.id = p.booking_id
            WHERE p.traveller_id = ? AND p.id <> ? AND p.status IN ('initializing','open','pending')
            AND p.hold_until > ? AND b.deleted_at IS NULL
            AND LOWER(TRIM(COALESCE(b.payment_status, ''))) NOT IN ('completed','complete','success','paid')",
            [(int) $traveller->id, (int) $exclude_attempt_id, gmdate('Y-m-d H:i:s')]);
        if (!$query) { return false; }
        $held = (float) $query->row()->units;
    }
    $remaining = round((float) $traveller->original_bag_space - $used - $held, 3);
    return ['used' => $used, 'held' => $held, 'remaining' => $remaining,
        'available' => (int) ($traveller->bag_locked ?? 0) === 1 ? 0.0 : max(0.0, $remaining),
        'overcommitted' => $remaining < -0.0005];
}

function booking_lock_traveller($db, $id)
{
    $lock = $db->dbdriver === 'sqlite3' ? '' : ' FOR UPDATE';
    $query = $db->query('SELECT * FROM travellers WHERE id = ?' . $lock, [(int) $id]);
    return $query ? $query->row() : null;
}
