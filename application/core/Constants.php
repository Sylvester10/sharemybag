<?php
defined('BASEPATH') or exit('No direct script access allowed');

/* ===== Documentation ===== 
Name: Constants::General
Role: Include
Description: Holds all the constants used by the app. Required in the construct of the core controller, MY_Controller, which makes it global to the entire application.
Date Created: 4th May, 2023
*/


$business_name = 'ShareMyBag';
$business = 'ShareMyBag';
$business_initials = 'SMB';
$business_phone_number = '+2348149265396';
$business_phone_number2 = '';
$business_facebook = 'https://facebook.com/sharemybag';
$business_instagram = 'https://instagram.com/sharemybag';
$business_twitter = '#';
$business_address = '70 BIU Road, GRA, Benin, Nigeria';
$business_contact_email = 'info@sharemybag.co.uk';
$sub_tagline = 'Find.Pay.Delivered';
$business_keywords = 'Share my bag, SMB, Sharemybag, Parcel, Airline baggage, International shipping, Extra space, Community-driven, Verified users, On-demand, Flexible, Affordable, Eco-friendly, Sustainable, Trustworthy, Safe, Convenient, Doorstep delivery, Express delivery, Same-day delivery, Travel essentials, Travel companions,
Route optimization, Travel, UK, Packages, Delivery, Shipping, Luggage, Baggage, Transportation, Courier, Door-to-door, Peer-to-peer, Shared economy, Collaborative consumption, User-generated content, Payment gateway, Advertisements, Secure transactions, Reviews, Customer support,
Mobile-responsive';
$business_description = "Sharemybag connects users seeking to send items and parcels abroad with trusted travellers who offer available bag spaces. Seamlessly purchase bag spaces and watch as your parcels are safely delivered to their recipients.";
$info_mail = "Info_2212!";
$admin_mail = "Admin_2212!";


//Software Info
define('business_name', $business_name);
define('business', $business);
define('business_initials', $business_initials);
define('business_phone_number', $business_phone_number);
define('business_phone_number2', $business_phone_number2);
define('business_facebook', $business_facebook);
define('business_instagram', $business_instagram);
define('business_twitter', $business_twitter);
define('business_address', $business_address);
define('business_contact_email', $business_contact_email);
define('sub_tagline', $sub_tagline);
define('business_keywords', $business_keywords);
define('business_description', $business_description);
define('business_website', base_url());
define('business_logo', base_url('assets/general/logo/colored_logo.svg'));
define('business_logo_white', base_url('assets/general/logo/white_logo.svg'));
define('business_logo_black', base_url('assets/general/logo/black_logo.svg'));
define('business_logo_text', base_url('assets/general/logo/name_black_logo.svg'));
define('business_text_logo', base_url('assets/general/logo/name_black.jpg'));
define('business_text_logo_white', base_url('assets/general/logo/name_white_logo.png'));
define('business_favicon', base_url('assets/general/logo/favicon.ico'));


//Developer Info
define('software_vendor', 'DevSylvester');
define('software_vendor_site', 'https://devsylvester.com/');



//MySQL-PHP server time difference. Change to zero if both are on same server
define('mysql_time_difference', 0); //if negative, write as -x, else, x


//login refresh time
define('login_refresh_time', 120); //refresh last login every 120 secs (2 mins) if the use is active


//Email config
define('business_web_mail', business_contact_email);


//defaults
define('default_admin_password', 'SMB2212');


//Others
define('pdf_icon', base_url('assets/general/pdf.png'));
define('id_card', base_url('assets/general/id-card.png'));
define('stripe', base_url('assets/general/stripe.svg'));
define('paystack', base_url('assets/general/paystack.svg'));
define('user_avatar', base_url('assets/users/images/profile/user-1.jpg'));
