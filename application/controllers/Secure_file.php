<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Secure_file Controller
 *
 * Serves protected files (ID cards, selfies, utility docs and itineraries) only to
 * authenticated and authorized users. Direct HTTP access to the
 * protected upload directories
 * is blocked by .htaccess rules; this controller is the only access path.
 *
 * SEC-004: PII files must never be served without authentication.
 */
class Secure_file extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Serve a protected file.
     *
     * @param string $type   One of: id_cards, selfie, utility, itinerary
     * @param string $filename  The filename to serve
     */
    public function serve($type = '', $filename = '')
    {
        // ── Authentication check ──
        // Must be logged in as admin OR as the owning user
        $is_admin = $this->session->admin_loggedin;
        $is_user  = $this->session->user_loggedin;

        // An admin session must not fall through to user ownership privileges.
        if ($is_admin && !in_array($this->get_admin_role(), array('super_admin', 'traveller_support'), true)) {
            show_error('This staff role cannot access identity files or itineraries.', 403, 'Access Denied');
            return;
        }
        $impersonator_id = (int) $this->session->userdata('user_impersonator_id');
        if ($impersonator_id > 0) {
            $impersonator = $this->db->where('id', $impersonator_id)->get('admins')->row();
            if (!$impersonator || $impersonator->role === 'customer_support') {
                show_error('This account session cannot access protected files.', 403, 'Access Denied');
                return;
            }
        }

        if (!$is_admin && !$is_user) {
            show_error('You must be logged in to access this file.', 403, 'Access Denied');
            return;
        }

        // ── Validate type ──
        $allowed_types = ['id_cards', 'selfie', 'utility', 'itinerary'];
        if (!in_array($type, $allowed_types, true)) {
            show_404();
            return;
        }

        // ── Sanitize filename (prevent directory traversal) ──
        if ($filename !== basename($filename) || empty($filename) || strpos($filename, '..') !== false) {
            show_404();
            return;
        }

        if (!$is_admin) {
            $this->db->where('email', (string) $this->session->email);
            if ($type === 'itinerary') {
                $this->db->where('itinerary_photo', $filename);
                ci_where_not_deleted($this->db, 'travellers');
                $owner = $this->db->get('travellers')->row();
            } else {
                $column = array('selfie' => 'selfie', 'id_cards' => 'id_card', 'utility' => 'utility')[$type];
                $this->db->where($column, $filename);
                ci_where_not_deleted($this->db, 'users');
                $owner = $this->db->get('users')->row();
            }
            if (!$owner) {
                show_error('You do not have permission to access this file.', 403, 'Access Denied');
                return;
            }
        }

        // ── Build file path ──
        $file_path = FCPATH . 'assets/' . $type . '/' . $filename;

        if (!file_exists($file_path) || !is_file($file_path)) {
            show_404();
            return;
        }

        // ── Serve the file ──
        $mime = mime_content_type($file_path);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file_path));
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Content-Type-Options: nosniff');
        readfile($file_path);
        exit;
    }
}
