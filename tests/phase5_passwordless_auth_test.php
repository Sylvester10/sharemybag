<?php

$root = dirname(__DIR__);

function phase5_contents($root, $path)
{
    $fullPath = $root . '/' . $path;
    if (!file_exists($fullPath)) {
        fwrite(STDERR, "FAIL: Missing {$path}.\n");
        exit(1);
    }

    return file_get_contents($fullPath);
}

function phase5_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$migration = phase5_contents($root, 'application/migrations/018_add_passwordless_authentication.php');
$migrationConfig = phase5_contents($root, 'application/config/migration.php');
$loginController = phase5_contents($root, 'application/controllers/User_login.php');
$profileController = phase5_contents($root, 'application/controllers/Profile.php');
$twilioService = phase5_contents($root, 'application/libraries/Twilio_verify_service.php');
$challengeModel = phase5_contents($root, 'application/models/Auth_challenge_model.php');
$loginView = phase5_contents($root, 'application/views/user_login/login.php');
$profileView = phase5_contents($root, 'application/views/users/profile.php');
$profilePhoneControl = phase5_contents($root, 'application/views/users/partials/phone_verification_control.php');
$adminController = phase5_contents($root, 'application/controllers/Admin.php');
$emailHelper = phase5_contents($root, 'application/helpers/email_helper.php');
$loginJs = phase5_contents($root, 'assets/website/js/home.js');

phase5_contains($migration, "'verified_phone_e164'", 'Migration must add a unique verified phone identity.');
phase5_contains($migration, "'phone_verified_at'", 'Migration must record phone verification time.');
phase5_contains($migration, "'email_verified_at'", 'Migration must record email verification time.');
phase5_contains($migration, 'auth_login_challenges', 'Migration must create passwordless challenge storage.');
phase5_contains($migration, 'auth_settings', 'Migration must create the admin-selectable phone OTP channel setting.');
if (!preg_match('/migration_version\'\]\\s*=\\s*(\\d+);/', $migrationConfig, $migrationMatch)
    || (int) $migrationMatch[1] < 18) {
    fwrite(STDERR, "FAIL: Migration target must include version 18 or a later forward migration.\n");
    exit(1);
}

phase5_contains($loginController, 'passwordless_request_ajax', 'Login must expose a no-reload code request endpoint.');
phase5_contains($loginController, 'passwordless_verify_ajax', 'Login must expose a no-reload code verification endpoint.');
phase5_contains($loginController, 'verified_phone_e164', 'Phone login must only resolve a verified phone identity.');
phase5_contains($loginController, 'submittedLoginPhone()', 'Password and code login must normalize the selected country code and phone number.');
phase5_contains($loginController, "set_rules('email', 'Email'", 'Email and password sign-in must remain available for users without verified phones.');
phase5_contains($loginController, 'destinationMatches', 'Passwordless verification must remain bound to the requested destination.');
phase5_contains($loginController, 'sendUnavailableChallengeResponse', 'Code requests must not reveal whether an account exists or a phone is verified.');
phase5_contains($loginController, "createChallenge(\$user->id, 'login', 'email'", 'Email one-time codes must remain available.');
phase5_contains($profileController, 'request_phone_verification_ajax', 'Profile must request phone verification without navigation.');
phase5_contains($profileController, 'verify_phone_ajax', 'Profile must verify the phone in place.');
phase5_contains($twilioService, '/Verifications', 'Twilio adapter must start Verify verifications.');
phase5_contains($twilioService, '/VerificationCheck', 'Twilio adapter must check Verify codes.');
phase5_contains($challengeModel, 'password_hash', 'Email login codes must be stored as hashes.');
phase5_contains($challengeModel, 'destinationMatches', 'Challenge destinations must be verified before an OTP is accepted.');
phase5_contains($emailHelper, 'return $mail->send()', 'Email delivery must report success or failure to passwordless callers.');

phase5_contains($loginView, "'field_name' => 'phone'", 'Login must use the shared phone input.');
phase5_contains($loginView, 'data-login-email-panel', 'Login must default to the email field.');
phase5_contains($loginView, 'data-login-identifier-toggle', 'Login must switch between email and phone within one form.');
phase5_contains($loginView, "'country_code_name' => 'country_code'", 'Login must collect a selectable country code.');
phase5_contains($loginView, 'Get One-Time Code', 'Passwordless mode must use the approved button label.');
phase5_contains($loginView, 'Use Password Instead', 'Login must offer the password fallback.');
phase5_contains($loginView, 'data-passwordless-code-panel', 'OTP entry must replace the form without a page reload.');
phase5_contains($loginJs, 'slideDown(200', 'Password mode should reveal the password field with the approved slide transition.');
phase5_contains($profileView, 'phone_verification_control', 'Profile must expose phone verification in the input group.');
phase5_contains($profilePhoneControl, 'Verify Number', 'Unverified profile phones must show a verification action.');
phase5_contains($profileView, "'input_id_prefix' => 'phoneVerificationOtp'", 'Profile verification must use the shared six-box OTP control.');
phase5_contains($profileView, 'phoneVerificationModal', 'Profile verification must use the approved modal.');
phase5_contains($adminController, 'authentication_settings', 'Super admin must be able to choose WhatsApp or SMS.');

echo "PASS: Phase 5 passwordless authentication contracts are present.\n";
