<?php
defined('BASEPATH') or exit('No direct script access allowed');

function stripe_key_test()
{
    return 'sk_test_51PRzxkE9sO0PVQExtCKFGTwx9UbyA1fzuEUkRJb72WMqnoSo5LrmoLLPo5kadv9G2ngotyzPfb4zk4hpNuJyWeQZ00nfLbPTng';
}

function stripe_key_live()
{
    return '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';
}

function paystack_key_test()
{
    return 'sk_test_30121d635245ff14a9377984b6e7cfd2aa0efb55';
}

function paystack_key_live()
{
    return 'sk_live_3d206640616308c6b859b0c9a75d557ecfa45827';
}

// New consolidated function to get the current Stripe key based on environment
function get_stripe_secret_key()
{
    if (ENVIRONMENT === 'production') {
        return stripe_key_live();
    }
    // Default to test key for 'development' and 'testing' environments
    return stripe_key_test();
}

// New consolidated function to get the current Paystack key based on environment
function get_paystack_secret_key()
{
    if (ENVIRONMENT === 'production') {
        return paystack_key_live();
    }
    // Default to test key for 'development' and 'testing' environments
    return paystack_key_test();
}

// yep yep