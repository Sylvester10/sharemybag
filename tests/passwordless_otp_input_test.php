<?php

$root = dirname(__DIR__);

function otp_test_contents($root, $path)
{
    $fullPath = $root . '/' . $path;
    if (!is_file($fullPath)) {
        fwrite(STDERR, "FAIL: Missing {$path}.\n");
        exit(1);
    }

    return file_get_contents($fullPath);
}

function otp_test_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

$partial = otp_test_contents($root, 'application/views/user_login/partials/otp_inputs.php');
$signupVerification = otp_test_contents($root, 'application/views/user_login/verify_email.php');
$login = otp_test_contents($root, 'application/views/user_login/login.php');
$loginJs = otp_test_contents($root, 'assets/login/js/login.js');
$loginCss = otp_test_contents($root, 'assets/login/css/custom.css');
$controller = otp_test_contents($root, 'application/controllers/User_login.php');

otp_test_contains($partial, '$index <= 6', 'The shared verification component must render six code fields.');
otp_test_contains($partial, 'data-otp-target', 'The shared verification component must bind its fields to the submitted hidden value.');
otp_test_contains($partial, 'inputmode="numeric"', 'Each verification field must request the numeric keyboard.');
otp_test_contains($partial, 'autocomplete="one-time-code"', 'The first verification field must support one-time-code autofill.');
otp_test_contains($partial, 'aria-label="Digit', 'Each verification field must have an accessible name.');

otp_test_contains($signupVerification, "user_login/partials/otp_inputs", 'Signup email verification must use the shared code fields.');
otp_test_contains($login, "user_login/partials/otp_inputs", 'Passwordless sign-in must reuse the signup code fields.');
otp_test_contains($login, "'hidden_name' => 'code'", 'Passwordless sign-in must submit the combined code under the existing backend field name.');
otp_test_contains($login, 'assets/login/js/login.js', 'Sign-in must load the shared verification-field behaviour.');

otp_test_contains($loginJs, "document.querySelectorAll('.otp-input-container')", 'Verification behaviour must support every shared code group.');
otp_test_contains($loginJs, "group.addEventListener('paste'", 'Pasting a complete code must work across the shared fields.');
otp_test_contains($loginJs, 'syncGroup()', 'Pasted and typed digits must update the submitted hidden value.');
otp_test_contains($loginCss, 'grid-template-columns: repeat(6, minmax(0, 1fr));', 'The shared fields must use a responsive six-column grid.');
otp_test_contains($loginCss, '.otp-input-container.is-invalid', 'The shared fields must expose a visible invalid state.');
otp_test_contains($controller, "exact_length[6]", 'Passwordless verification must validate the six-digit UI contract.');

fwrite(STDOUT, "PASS: signup and sign-in share the accessible six-digit verification input.\n");
