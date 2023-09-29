<?php
defined('BASEPATH') or exit('No direct script access allowed');

/* ===== Documentation ===== 
Name: Constants::General
Role: Include
Description: Holds all the constants used by the app. Required in the construct of the core controller, MY_Controller, which makes it global to the entire application.
Date Created: 4th May, 2023
*/


$business_name = 'Share My Bag';
$business_initials = 'SMB';
$business_phone_number = '+2348149265396';
//$business_phone_number2 = '';
$business_phone_number3 = '';
$business_address = '70 BIU Road, GRA, Benin, Nigeria';
$business_contact_email = 'info@sharemybag.uk';
$sub_tagline = 'Find.Pay.Delivered';
$business_keywords = 'Share my bag, SMB, Sharemybag, Parcel, Airline baggage, International shipping, Extra space, Community-driven, Verified users, On-demand, Flexible, Affordable, Eco-friendly, Sustainable, Trustworthy, Safe, Convenient, Doorstep delivery, Express delivery, Same-day delivery, Travel essentials, Travel companions,
Route optimization, Travel, UK, Packages, Delivery, Shipping, Luggage, Baggage, Transportation, Courier, Door-to-door, Peer-to-peer, Shared economy, Collaborative consumption, User-generated content, Payment gateway, Advertisements, Secure transactions, Reviews, Customer support,
Mobile-responsive';
$business_description = "Sharemybag connects users seeking to send items and parcels abroad with trusted travelers who offer available bag spaces. Seamlessly purchase bag spaces and watch as your parcels are safely delivered to their recepients ";
$info_mail = "Info_2212!";
$admin_mail = "Admin_2212!";


//Software Info
define('business_name', $business_name);
define('business_initials', $business_initials);
define('business_phone_number', $business_phone_number);
define('business_phone_number2', $business_phone_number2);
define('business_phone_number3', $business_phone_number3);
define('business_address', $business_address);
define('business_contact_email', $business_contact_email);
define('sub_tagline', $sub_tagline);
define('business_keywords', $business_keywords);
define('business_description', $business_description);
define('business_website', base_url());
define('business_logo', base_url('assets/user/img/logo/svg/colored_logo.svg'));
define('business_logo_white', base_url('assets/user/img/logo/svg/white_logo.svg'));
define('business_logo_black', base_url('assets/user/img/logo/svg/black_logo.svg'));
define('business_favicon', base_url('assets/user/img/favicon.png'));


//Developer Info
define('software_vendor', 'DevOnyeka');
define('software_vendor_site', 'https://devonyeka.com/');



//MySQL-PHP server time difference. Change to zero if both are on same server
define('mysql_time_difference', 0); //if negative, write as -x, else, x


//Email config
define('business_web_mail', business_contact_email);


//defaults
define('default_admin_password', 'SMB2212');


//Others
//define('pdf_icon', base_url('assets/user/img/pdf_icon.png'));
define('user_avatar', base_url('assets/user/img/user.png'));
