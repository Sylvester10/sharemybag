<?php
// Isolated view rendering: no database, SMTP connection, or real recipients.
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('APPPATH', dirname(__DIR__, 2) . '/application/');
define('business', 'ShareMyBag');
define('business_name', 'ShareMyBag');
define('business_contact_email', 'customers@sharemybag.co.uk');
define('business_phone_number', '+44 20 0000 0000');
define('business_address', '');

function production_url($path = '') { return 'https://sharemybag.co.uk/' . $path; }
function base_url($path = '') { return production_url($path); }
function html_escape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function booking_category_unit($category) { return 'kg'; }
function booking_support_whatsapp_url($data) { return 'https://wa.me/2348149265396?text=' . rawurlencode($data['tracking_id']); }

require_once APPPATH . 'helpers/email_helper.php';

class EmailPreviewRenderer
{
    public $load;
    private $directory;
    public function __construct($directory) { $this->load = $this; $this->directory = $directory; }
    public function view($view, $data = array(), $return = false)
    {
        extract($data);
        ob_start();
        include $this->directory . '/' . basename($view) . '.php';
        $html = ob_get_clean();
        if ($return) { return $html; }
        echo $html;
    }
}

function email_preview_data()
{
    return array(
        'firstname' => 'Amara', 'lastname' => 'Okafor', 'fullname' => 'Amara Okafor',
        'agent_name' => 'Amara Okafor', 'user_fullname' => 'Amara Okafor',
        'message' => nl2br("Hello everyone,\n\nThis is the original test notification wording.\nPlease keep this line together.\n\nThank you."),
        'verification_code' => '482915', 'login_code' => '482915', 'pass_reset_code' => '482915',
        'reset_link' => email_call2action_blue(base_url('recover_password/change_password/42/482915'), 'Reset Password'),
        'id_type' => 'International passport', 'rejection_reason' => 'The document is not clear.',
        'rejection_note' => "Please include all four corners.\nMake sure the text is readable.",
        'traveller_name' => 'Daniel Okafor', 'action_word' => 'added to', 'tracking_id' => 'SMB-DEMO-1042',
        'item_name' => 'Clothing', 'item_category' => 'Clothing', 'item_size' => '2kg',
        'new_total' => '£120.00', 'new_commission' => '£90.00', 'currency' => '£', 'total_amount' => 120,
        'items' => json_encode(array((object) array('category'=>'Clothing','item_name'=>'Clothing','size'=>6,'price'=>120))),
        'traveller_departure_state' => 'Lagos', 'traveller_arrival_airport' => 'Heathrow',
        'traveller_departure_date' => '24 October 2026', 'traveller_arrival_date' => '25 October 2026',
        'traveller_current_state' => 'Lagos', 'traveller_arrival_state' => 'London',
        'traveller_drop_address1' => 'Sample Lagos address', 'traveller_drop_date1' => '20 October 2026',
        'traveller_drop_address2' => 'Sample Lagos address', 'traveller_drop_date2' => '22 October 2026',
    );
}

function email_document($html)
{
    $document = new DOMDocument();
    $prior = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($prior);
    return $document;
}

function email_copy_normalize($text)
{
    return preg_replace('/\s+/u', '', $text);
}
