<?php
require __DIR__ . '/support/email_render.php';
function recovery_email_assert($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
$renderer = new EmailPreviewRenderer(APPPATH . 'views/mail');
$link = production_url('continue-registration/' . str_repeat('a',64));
$html = $renderer->view('user_registration_continuation_email', ['firstname'=>'<script>unsafe</script>', 'resume_link'=>$link], true);
$xpath = new DOMXPath(email_document($html));
recovery_email_assert(strpos($html,'<script>unsafe</script>') === false && strpos($html,'&lt;script&gt;') !== false, 'Names are escaped');
recovery_email_assert($xpath->query('//a[@href="' . $link . '"]')->length === 1, 'Continuation email has the correct action link');
recovery_email_assert(strpos($html,'Get verification code') !== false && strpos($html,'24 hours') !== false, 'Continuation explains fresh-code request and link expiry');
recovery_email_assert($xpath->query('//div[@class="code-box"]')->length === 0, 'Admin continuation email contains no premature code');
recovery_email_assert($xpath->query('//a[@class="email-footer-link"]')->length === 3, 'Continuation retains shared email policy links');
$link = production_url('verify-email/' . str_repeat('a',64));
$html = $renderer->view('user_code_verification_email', ['firstname'=>'Fixture', 'verification_code'=>'482915', 'resume_link'=>$link], true);
$xpath = new DOMXPath(email_document($html));
recovery_email_assert($xpath->query('//a[@href="' . $link . '"]')->length === 1 && strpos($html,'482915') !== false, 'Verification email includes both the code and return link');
recovery_email_assert(strpos($html,'15 minutes') !== false, 'Verification email explains code expiry');
echo "PASS: continuation and verification emails render safe names, correct links, code timing, and shared email shell.\n";
