<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = 'errors/error404';
$route['error404'] = 'errors/error404';
$route['translate_uri_dashes'] = FALSE;

// Seo
$route['sitemap.xml'] = 'seo/sitemap';
$route['robots.txt'] = 'seo/robots';
$route['schema.json'] = 'seo/schema';

// Custom routes
$route['booking/(:any)'] = 'home/booking/$1';
$route['track'] = 'home/track';
$route['investors'] = 'home/investors';
$route['travellers'] = 'home/travellers';
$route['traveller-agreement'] = 'home/traveller_agreement';
$route['prohibited-items'] = 'home/prohibited';
$route['terms-of-use'] = 'home/terms_of_use';
$route['terms-and-conditions'] = 'home/terms_conditions';
$route['privacy-policy'] = 'home/policy';
$route['cookies'] = 'home/cookies';
$route['test'] = 'home/test';
$route['success'] = 'home/success';
$route['coming-soon'] = 'home/coming_soon';
//$route['success/(:any)'] = 'home/success/$1';


// User routes
$route['signin'] = 'user_login';
$route['logout'] = 'user_login/logout';
$route['verify-email'] = 'registration/verify_email';
$route['forgot-password'] = 'recover_password';
$route['change-password/(:any)'] = 'recover_password/change_password/$1';
$route['buy-bag-space/(:any)'] = 'user_bookings/buy_bag_space/$1';

$route['checkout'] = 'user_bookings/checkout';
$route['create-payment-intent'] = 'user_bookings/create_payment_intent';
$route['booking-success'] = 'user_bookings/booking_success';

// Admin routes
$route['admin_adverts'] = 'admin_adverts/index';
$route['restricted_access'] = 'admin/restricted_access';
$route['admin_adverts/(:num)'] = 'admin_adverts/index/$1';
$route['admin_logout'] = 'admin_login/logout';
