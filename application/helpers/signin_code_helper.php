<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Canonical order also keeps saved settings and audit labels deterministic. */
function signin_code_method_bits()
{
    return array('email' => 1, 'whatsapp' => 2, 'sms' => 4);
}

function signin_code_methods_mask($methods)
{
    if (!is_array($methods) || count($methods) < 1 || count($methods) > 2) { return null; }
    $bits = signin_code_method_bits();
    $mask = 0;
    foreach ($methods as $method) {
        if (!is_string($method) || !isset($bits[$method]) || ($mask & $bits[$method])) { return null; }
        $mask |= $bits[$method];
    }
    return $mask;
}

function signin_code_enabled_methods($user)
{
    if (isset($user->signin_code_methods)) {
        $mask = (int) $user->signin_code_methods;
        if (!in_array($mask, array(1, 2, 3, 4, 5, 6), true)) { return array(); }
        return array_keys(array_filter(signin_code_method_bits(), static function ($bit) use ($mask) { return ($mask & $bit) !== 0; }));
    }
    // Compatibility while the additive migration is being applied.
    $methods = array('email');
    if (!empty($user->phone_signin_enabled)) {
        $methods[] = ($user->phone_otp_channel ?? 'sms') === 'whatsapp' ? 'whatsapp' : 'sms';
    }
    return $methods;
}

function signin_code_method_enabled($user, $method)
{
    if (!$user || !in_array($method, signin_code_enabled_methods($user), true)) { return false; }
    return $method === 'email' || (!empty($user->phone_signin_enabled)
        && !empty($user->phone_verified_at) && !empty($user->verified_phone_e164));
}
