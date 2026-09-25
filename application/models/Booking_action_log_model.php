<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Append-only audit access for sensitive booking actions.
 *
 * This model intentionally exposes no update or delete operation.
 */
class Booking_action_log_model extends CI_Model
{
    const ACTION_CANCEL = 'cancel';
    const ACTION_MOVE = 'move';

    private $table = 'booking_action_logs';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function record(array $event)
    {
        $action = strtolower(trim((string) ($event['action'] ?? '')));
        $reason = trim((string) ($event['reason'] ?? ''));
        $bookingId = (int) ($event['booking_id'] ?? 0);
        $adminId = (int) ($event['admin_id'] ?? 0);

        if (!in_array($action, array(self::ACTION_CANCEL, self::ACTION_MOVE), true)) {
            return false;
        }

        if ($bookingId <= 0 || $adminId <= 0 || $reason === '') {
            return false;
        }

        $data = array(
            'booking_id' => $bookingId,
            'booking_reference' => $this->nullableString($event['booking_reference'] ?? null, 50),
            'action' => $action,
            'from_traveller_id' => $this->nullablePositiveInteger($event['from_traveller_id'] ?? null),
            'to_traveller_id' => $this->nullablePositiveInteger($event['to_traveller_id'] ?? null),
            'admin_id' => $adminId,
            'reason' => $reason,
            'refund_status' => $this->nullableString($event['refund_status'] ?? null, 30),
            'refund_reference' => $this->nullableString($event['refund_reference'] ?? null, 191),
            'refund_amount' => $this->nullableMoney($event['refund_amount'] ?? null),
            'currency' => $this->nullableCurrency($event['currency'] ?? null),
            'before_snapshot' => $this->encodeSnapshot($event['before_snapshot'] ?? null),
            'after_snapshot' => $this->encodeSnapshot($event['after_snapshot'] ?? null),
        );

        if (!$this->db->insert($this->table, $data)) {
            return false;
        }

        return (int) $this->db->insert_id();
    }

    public function getByBookingId($bookingId)
    {
        $this->db->where('booking_id', (int) $bookingId);
        $this->db->order_by('date_added', 'DESC');
        $this->db->order_by('id', 'DESC');
        return $this->db->get($this->table)->result();
    }

    private function encodeSnapshot($snapshot)
    {
        if ($snapshot === null || $snapshot === '') {
            return null;
        }

        if (is_string($snapshot)) {
            return $snapshot;
        }

        $encoded = json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $encoded === false ? null : $encoded;
    }

    private function nullablePositiveInteger($value)
    {
        $value = (int) $value;
        return $value > 0 ? $value : null;
    }

    private function nullableMoney($value)
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function nullableCurrency($value)
    {
        $value = strtoupper(trim((string) $value));
        return preg_match('/^[A-Z]{3}$/', $value) ? $value : null;
    }

    private function nullableString($value, $maximumLength)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $maximumLength)
            : substr($value, 0, $maximumLength);
    }
}
