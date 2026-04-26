<?php
defined('BASEPATH') or exit('No direct script access allowed');

function stripe_key_test()
{
    return $_ENV['STRIPE_TEST_KEY'] ?? '';
}

function stripe_key_live()
{
    return $_ENV['STRIPE_SECRET_KEY'] ?? '';
}

function paystack_key_test()
{
    return $_ENV['PAYSTACK_TEST_KEY'] ?? '';
}

function paystack_key_live()
{
    return $_ENV['PAYSTACK_SECRET_KEY'] ?? '';
}

// Returns the correct Stripe secret key based on the current environment
function get_stripe_secret_key()
{
    if (ENVIRONMENT === 'production') {
        return stripe_key_live();
    }
    return stripe_key_test();
}

// Returns the correct Paystack secret key based on the current environment
function get_paystack_secret_key()
{
    if (ENVIRONMENT === 'production') {
        return paystack_key_live();
    }
    return paystack_key_test();
}
