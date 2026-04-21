<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Secure_file Controller
 *
 * Serves protected files (ID cards, selfies, utility docs) only to
 * authenticated and authorized users. Direct HTTP access to the
 * assets/id_cards/, assets/selfie/, and assets/utility/ directories
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
     * @param string $type   One of: id_cards, selfie, utility
     * @param string $filename  The filename to serve
     */
    public function serve($type = '', $filename = '')
    {
        // ── Authentication check ──
        // Must be logged in as admin OR as the owning user
        $is_admin = $this->session->admin_loggedin;
        $is_user  = $this->session->user_loggedin;

        if (!$is_admin && !$is_user) {
            show_error('You must be logged in to access this file.', 403, 'Access Denied');
            return;
        }

        // ── Validate type ──
        $allowed_types = ['id_cards', 'selfie', 'utility'];
        if (!in_array($type, $allowed_types, true)) {
            show_404();
            return;
        }

        // ── Sanitize filename (prevent directory traversal) ──
        $filename = basename($filename); // strips any path components
        if (empty($filename) || strpos($filename, '..') !== false) {
            show_404();
            return;
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
        readfile($file_path);
        exit;
    }
}
