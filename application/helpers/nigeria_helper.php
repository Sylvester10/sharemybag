<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* ===== Documentation ===== 
Name: nigeria_helper
Role: Helper
Description: Custom Nigeria-specific data helper
Author: Sylvester Nmakwe
Date Created: 10th January, 2023
*/ 





function nigerian_banks() {
    $banks = array (
        'Access Bank',
        'Diamond Bank',
        'Dynamic Standard Bank',
        'Ecobank',
        'Enterprise Bank',
        'Fidelity Bank',
        'First Bank',
        'First City Monument Bank (FCMB)',
        'Guaranty Trust Bank (GTB)',
        'Heritage Bank',
        'Jaiz Bank',
        'Keystone Bank',
        'Mainstreet Bank',
        'Providus Bank',
        'Polaris Bank',
        'Stanbic IBTC Bank',
        'Standard Chartered Bank',
        'Sterling Bank',
        'Suntrust Bank',
        'Union Bank',
        'United Bank for Africa (UBA)',
        'Unity Bank',
        'Wema Bank',
        'Zenith Bank',
    );
    return $banks; 
}