<?php

$root = dirname(__DIR__);

function resend_test_contents($root, $path)
{
    $fullPath = $root . '/' . $path;
    if (!is_file($fullPath)) {
        fwrite(STDERR, "FAIL: Missing {$path}.\n");
        exit(1);
    }

    return file_get_contents($fullPath);
}

function resend_test_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

function resend_test_not_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\n");
        exit(1);
    }
}

$view = resend_test_contents($root, 'application/views/user_login/login.php');
$javascript = resend_test_contents($root, 'assets/website/js/home.js');
$controller = resend_test_contents($root, 'application/controllers/User_login.php');

resend_test_contains($view, 'data-passwordless-resend-button', 'The sign-in code screen must provide a resend control.');
resend_test_contains($view, 'data-passwordless-resend-label', 'The resend control must expose one inline label for every resend state.');
resend_test_contains($view, 'data-passwordless-resend-countdown', 'The sign-in code screen must show the resend cooldown.');
resend_test_contains($view, 'passwordless-resend-spinner', 'The resend action must provide loading feedback.');
resend_test_contains($javascript, 'startPasswordlessResendCooldown', 'The resend control must enforce the server-provided cooldown in the UI.');
resend_test_contains($javascript, "url: base_url + 'user_login/passwordless_request_ajax'", 'Resend must reuse the protected passwordless request endpoint.');
resend_test_contains($javascript, '$codeForm.find(\'[data-challenge-token]\').val(res.challenge_token || \'\')', 'A successful resend must replace the invalidated challenge token.');
resend_test_contains($javascript, 'res.resend_after', 'The countdown must use the cooldown returned by the server.');
resend_test_contains($javascript, 'showPasswordlessResendSent', 'A successful resend must briefly replace the inline action text with Sent.');
resend_test_contains($javascript, '$resendLabel.text(\'Sent\')', 'The resend confirmation must be inline text rather than an alert or badge.');
resend_test_not_contains($javascript, 'renderAuthStatus($status, \'success\'', 'Successful resend and sign-in must not display success alerts before continuing.');
resend_test_contains($controller, "'resend_after' => 30", 'The backend must return an authoritative resend cooldown.');
resend_test_contains($controller, 'auth_throttle_check($key, self::CODE_REQUEST_RATE_LIMIT_MAX', 'Resend requests must remain protected by the backend rate limit.');

fwrite(STDOUT, "PASS: passwordless sign-in supports rate-limited in-place code resend.\n");
