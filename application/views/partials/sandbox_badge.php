<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (strtolower((string) parse_url(base_url(), PHP_URL_HOST)) !== 'sandbox.sharemybag.co.uk') {
    return;
}

$sandbox_badge_fixed = !empty($sandbox_badge_fixed);
?>
<span class="smb-sandbox-badge" style="display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:#fff0e6;border:1px solid #f3b58a;color:#8a3900;font-size:12px;font-weight:700;line-height:1;white-space:nowrap;<?php echo $sandbox_badge_fixed ? 'position:fixed;top:16px;right:16px;z-index:1040;' : ''; ?>">Sandbox</span>
