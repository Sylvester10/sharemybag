<?php
defined('BASEPATH') or exit('No direct script access allowed');


function check_javascript_enabled()
{
	$CI = &get_instance(); //get instance of code igniter super object so helpers can be accessed outside of object context
	?>
	<!-- No JavaScript -->
	<noscript>
		<?php
		//check current page to ensure redirect doesn't apply to the no_js page
		$current_method = $CI->router->fetch_method();
		if ($current_method != 'no_js') { ?>
			<meta http-equiv="refresh" content="0; url=<?php echo base_url('no_js'); ?>" />
		<?php } ?>
	</noscript>
	<?php
}


function countries()
{
	$countries = array(
		/* 'Afghanistan',
							 'Albania',
							 'Algeria',
							 'Andorra',
							 'Angola',
							 'Antigua and Barbuda',
							 'Argentina',
							 'Armenia',
							 'Aruba',
							 'Australia',
							 'Austria',
							 'Azerbaijan',
							 'Bahamas', 
							 'The Bahrain',
							 'Bangladesh',
							 'Barbados',
							 'Belarus',
							 'Belgium',
							 'Belize',
							 'Benin',
							 'Bhutan',
							 'Bolivia',
							 'Bosnia and Herzegovina',
							 'Botswana',
							 'Brazil',
							 'Brunei',
							 'Bulgaria',
							 'Burkina Faso',
							 'Burma',
							 'Burundi',
							 'Cambodia',
							 'Cameroon',
							 'Canada',
							 'Cabo Verde',
							 'Central African Republic',
							 'Chad',
							 'Chile',
							 'China',
							 'Colombia',
							 'Comoros',
							 'Congo', 
							 'Democratic Republic of the Congo', 
							 'Costa Rica',
							 'Cote d Ivoire',
							 'Croatia',
							 'Cuba',
							 'Curacao',
							 'Cyprus',
							 'Czechia',
							 'Denmark',
							 'Djibouti',
							 'Dominica',
							 'Dominican Republic',
							 'East Timor (see Timor-Leste)',
							 'Ecuador',
							 'Egypt',
							 'El Salvador',
							 'Equatorial Guinea',
							 'Eritrea',
							 'Estonia',
							 'Ethiopia',
							 'Fiji',
							 'Finland',
							 'France',
							 'Gabon',
							 'Gambia', 
							 'Georgia',
							 'Germany',
							 'Ghana',
							 'Greece',
							 'Grenada',
							 'Guatemala',
							 'Guinea',
							 'Guinea-Bissau',
							 'Guyana',
							 'Haiti',
							 'Holy See',
							 'Honduras',
							 'Hong Kong',
							 'Hungary',
							 'Iceland',
							 'India',
							 'Indonesia',
							 'Iran',
							 'Iraq',
							 'Ireland',
							 'Israel',
							 'Italy',
							 'Jamaica',
							 'Japan',
							 'Jordan',
							 'Kazakhstan',
							 'Kenya',
							 'Kiribati',
							 'Korea, North',
							 'Korea, South',
							 'Kosovo',
							 'Kuwait',
							 'Kyrgyzstan',
							 'Laos',
							 'Latvia',
							 'Lebanon',
							 'Lesotho',
							 'Liberia',
							 'Libya',
							 'Liechtenstein',
							 'Lithuania',
							 'Luxembourg',
							 'Macau',
							 'Macedonia',
							 'Madagascar',
							 'Malawi',
							 'Malaysia',
							 'Maldives',
							 'Mali',
							 'Malta',
							 'Marshall Islands',
							 'Mauritania',
							 'Mauritius',
							 'Mexico',
							 'Micronesia',
							 'Moldova',
							 'Monaco',
							 'Mongolia',
							 'Montenegro',
							 'Morocco',
							 'Mozambique',
							 'Namibia',
							 'Nauru',
							 'Nepal',
							 'Netherlands',
							 'New Zealand',
							 'Nicaragua',
							 'Niger',*/
		'Nigeria',
		/*'North Korea',
							 'Norway',
							 'Oman',
							 'Pakistan',
							 'Palau',
							 'Palestinian Territories',
							 'Panama',
							 'Papua New Guinea',
							 'Paraguay',
							 'Peru',
							 'Philippines',
							 'Poland',
							 'Portugal',
							 'Qatar',
							 'Romania',
							 'Russia',
							 'Rwanda',
							 'Saint Kitts and Nevis',
							 'Saint Lucia',
							 'Saint Vincent and the Grenadines',
							 'Samoa',
							 'San Marino',
							 'Sao Tome and Principe',
							 'Saudi Arabia',
							 'Senegal',
							 'Serbia',
							 'Seychelles',
							 'Sierra Leone',
							 'Singapore',
							 'Sint Maarten',
							 'Slovakia',
							 'Slovenia',
							 'Solomon Islands',
							 'Somalia',
							 'South Africa',
							 'South Korea',
							 'South Sudan',
							 'Spain',
							 'Sri Lanka',
							 'Sudan',
							 'Suriname',
							 'Swaziland',
							 'Sweden',
							 'Switzerland',
							 'Syria',
							 'Taiwan',
							 'Tajikistan',
							 'Tanzania',
							 'Thailand',
							 'Timor-Leste',
							 'Togo',
							 'Tonga',
							 'Trinidad and Tobago',
							 'Tunisia',
							 'Turkey',
							 'Turkmenistan',
							 'Tuvalu',
							 'Uganda',
							 'Ukraine',
							 'United Arab Emirates',*/
		'United Kingdom (UK)',
		/*'United States of America (USA)',
							 'Uruguay',
							 'Uzbekistan',
							 'Vanuatu',
							 'Venezuela',
							 'Vietnam',
							 'Yemen',
							 'Zambia',
							 'Zimbabwe',*/

	);
	return $countries;
}

function airlines()
{
	$airlines = array(
		'Qatar Airways',
		'KLM Royal Dutch Airline',
		'Air France',
		'British Airways',
		'Virgin Atlantic',
		'Turkish Airlines',

	);
	return $airlines;
}


function currency_codes()
{
	$currencies = array(
		'Albania Lek' => '76',
		'Afghanistan Afghani' => '1547',
		'Argentina Peso' => '36',
		'Aruba Guilder' => '402',
		'Australia Dollar' => '36',
		'Azerbaijan New Manat' => '1084',
		'Bahamas Dollar' => '36',
		'Barbados Dollar' => '36',
		'Belarus Ruble' => '66',
		'Belize Dollar' => '66',
		'Bermuda Dollar' => '36',
		'Bolivia Bolíviano' => '36',
		'Bosnia & Herzegovina Convertible Marka' => '75',
		'Botswana Pula' => '80',
		'Bulgaria Lev' => '1083',
		'Brazil Real' => '82',
		'Brunei Darussalam Dollar' => '36',
		'Cambodia Riel' => '6107',
		'Canada Dollar' => '36',
		'Cayman Islands Dollar' => '36',
		'Chile Peso' => '36',
		'China Yuan Renminbi' => '165',
		'Colombia Peso' => '36',
		'Costa Rica Colon' => '8353',
		'Croatia Kuna' => '107',
		'Cuba Peso' => '8369',
		'Czech Republic Koruna' => '75',
		'Denmark Krone' => '107',
		'Dominican Republic Peso' => '82',
		'East Caribbean Dollar' => '36',
		'Egypt Pound' => '163',
		'El Salvador Colon' => '36',
		'Euro Member Countries' => '8364',
		'Falkland Islands (Malvinas) Pound' => '163',
		'Fiji Dollar' => '36',
		'Ghana Cedi' => '162',
		'Gibraltar Pound' => '163',
		'Guatemala Quetzal' => '81',
		'Guernsey Pound' => '163',
		'Guyana Dollar' => '36',
		'Honduras Lempira' => '76',
		'Hong Kong Dollar' => '36',
		'Hungary Forint' => '70',
		'Iceland Krona' => '107',
		'India Rupee' => '8377',
		'Indonesia Rupiah' => '82',
		'Iran Rial' => '65020',
		'Isle of Man Pound' => '163',
		'Israel Shekel' => '8362',
		'Jamaica Dollar' => '74',
		'Japan Yen' => '165',
		'Jersey Pound' => '163',
		'Kazakhstan Tenge' => '1083',
		'Korea (North) Won' => '8361',
		'Korea (South) Won' => '8361',
		'Kyrgyzstan Som' => '1083',
		'Laos Kip' => '8365',
		'Lebanon Pound' => '163',
		'Liberia Dollar' => '36',
		'Macedonia Denar' => '1076',
		'Malaysia Ringgit' => '82',
		'Mauritius Rupee' => '8360',
		'Mexico Peso' => '36',
		'Mongolia Tughrik' => '8366',
		'Mozambique Metical' => '77',
		'Namibia Dollar' => '36',
		'Nepal Rupee' => '8360',
		'Netherlands Antilles Guilder' => '402',
		'New Zealand Dollar' => '36',
		'Nicaragua Cordoba' => '67',
		'Nigeria Naira' => '8358',
		'Korea (North) Won' => '8361',
		'Norway Krone' => '107',
		'Oman Rial' => '65020',
		'Pakistan Rupee' => '8360',
		'Panama Balboa' => '66',
		'Paraguay Guarani' => '71',
		'Peru Sol' => '83',
		'Philippines Peso' => '8369',
		'Poland Zloty' => '122',
		'Qatar Riyal' => '65020',
		'Romania New Leu' => '108',
		'Russia Ruble' => '1088',
		'Saint Helena Pound' => '163',
		'Saudi Arabia Riyal' => '65020',
		'Serbia Dinar' => '1044',
		'Seychelles Rupee' => '8360',
		'Singapore Dollar' => '36',
		'Solomon Islands Dollar' => '36',
		'Somalia Shilling' => '83',
		'South Africa Rand' => '82',
		'Korea (South) Won' => '8361',
		'Sri Lanka Rupee' => '8360',
		'Sweden Krona' => '107',
		'Switzerland Franc' => '67',
		'Suriname Dollar' => '36',
		'Syria Pound' => '163',
		'Taiwan New Dollar' => '78',
		'Thailand Baht' => '3647',
		'Trinidad and Tobago Dollar' => '84',
		'Turkey Lira' => '8378',
		'Tuvalu Dollar' => '36',
		'Ukraine Hryvnia' => '8372',
		'United Kingdom Pound' => '163',
		'United States Dollar' => '36',
		'Uruguay Peso' => '36',
		'Uzbekistan Som' => '1083',
		'Venezuela Bolivar' => '66',
		'Viet Nam Dong' => '8363',
		'Yemen Rial' => '65020',
		'Zimbabwe Dollar' => '90',
	);
	return $currencies;
}


function get_currency_symbol($currency_code)
{
	$currency_symbol = '&#' . $currency_code . ';';
	return $currency_symbol;
}


function nigerian_states()
{
	$states = array(
		'Abuja',
		'Abia',
		'Adamawa',
		'Akwa Ibom',
		'Anambra',
		'Bauchi',
		'Bayelsa',
		'Benue',
		'Borno',
		'Cross River',
		'Delta',
		'Ebonyi',
		'Edo',
		'Ekiti',
		'Enugu',
		'Gombe',
		'Imo',
		'Jigawa',
		'Kaduna',
		'Kano',
		'Katsina',
		'Kebbi',
		'Kogi',
		'Kwara',
		'Lagos',
		'Nasarawa',
		'Niger',
		'Ogun',
		'Ondo',
		'Osun',
		'Oyo',
		'Plateau',
		'Rivers',
		'Sokoto',
		'Taraba',
		'Yobe',
		'Zamfara',
	);
	return $states;
}


function airport_states()
{
	$airports = array(
		'Abuja',
		'Lagos',
	);
	return $airports;
}



function admin_roles()
{
	$roles = array(
		'All Roles',
		'Announcement Manager',
		'News Manager',
		'Newsletter Manager',
		'Events Manager',
		'Gallery Manager',
		'Message Manager',
		'User Manager',
	);
	return $roles;
}


function staff_designations()
{
	$staff_designations = array(
		'MD/CEO',
		'ED Admin and Finance',
		'Head of Sales/Marketing',
		'Head of Media/ICT',
		'Head of Client Relations',
		'Personal Assistant',
		'ICT Personnel',
		'Sales',
	);
	return $staff_designations;
}


function educational_qualifications()
{
	$qualifications = [
		'FSLC',
		'SSCE',
		'NCE',
		'OND',
		'Degree',
		'Masters',
		'PhD',
		'Other',
		'None',
	];
	return $qualifications;
}


function get_firstname($full_name)
{ //get firstname from a string of names
	$full_name = trim($full_name);
	$names = explode(" ", $full_name); //break name string into an array of individual words
	if (count($names) > 1) { //name contains at least 2 words
		$first_name = $names[1]; //array index 1, likely firstname
	} else {
		$first_name = $names[0]; //array index 0
	}
	return $first_name;
}


function custom_validation_errors()
{
	if (validation_errors()) {
		return '<div class="alert alert-danger alert-dismissable text-center">
				<a href="#" class="close" data-dismiss="alert" aria-label="Close" title="Close"> &times; </a>'
			. validation_errors() .
			'</div>';
	}
}


function flash_message_success($message)
{
	$CI = &get_instance(); //get instance of code igniter super object
	//success flash messages
	if (!$CI->session->flashdata($message)) {
		return;
	}
	if ($CI->session->flashdata($message)) {
		$response = '<div class="alert alert-success alert-dismissable text-center">
						<a href="#" class="close" data-dismiss="alert" aria-label="Close" title="Close"> &times; </a>'
			. $CI->session->flashdata($message) .
			'</div>';

		$CI->session->set_flashdata($message, false);
		return $response;
	}
}



function flash_message_danger($message)
{
	$CI = &get_instance(); //get instance of code igniter super object
	//danger flash messages
	if (!$CI->session->flashdata($message)) {
		return;
	}
	if ($CI->session->flashdata($message)) {
		$response = '<div class="alert alert-danger alert-dismissable text-center">
						<a href="#" class="close" data-dismiss="alert" aria-label="Close" title="Close"> &times; </a>'
			. $CI->session->flashdata($message) .
			'</div>';

		$CI->session->set_flashdata($message, false);
		return $response;

	}
}


function flash_message_warning($message)
{
	$CI =& get_instance(); //get instance of code igniter super object
	//warning flash messages
	if (!$CI->session->flashdata($message)) {
		return;
	}
	if ($CI->session->flashdata($message)) {
		$response = '<div class="alert alert-warning alert-dismissable text-center">
						<a href="#" class="close" data-dismiss="alert" aria-label="Close" title="Close"> &times; </a>'
			. $CI->session->flashdata($message) .
			'</div>';

		$CI->session->set_flashdata($message, false);
		return $response;
	}
}


function flash_message_info($message)
{
	$CI =& get_instance(); //get instance of code igniter super object
	//info flash messages
	if (!$CI->session->flashdata($message)) {
		return;
	}
	if ($CI->session->flashdata($message)) {
		$response = '<div class="alert alert-info alert-dismissable text-center">
						<a href="#" class="close" data-dismiss="alert" aria-label="Close" title="Close"> &times; </a>'
			. $CI->session->flashdata($message) .
			'</div>';

		$CI->session->set_flashdata($message, false);
		return $response;
	}
}


function generate_snippet($string, $max_characters)
{
	$snippet = mb_strimwidth(strip_tags($string), 0, $max_characters, "...");
	return $snippet;
}

// Get Date difference
function get_date_difference($date1, $date2)
{
	$datetime1 = date_create($date1);
	$datetime2 = date_create($date2);
	$interval = date_diff($datetime1, $datetime2);
	return $interval->format('%a');
}


function x_day_number($date)
{ //eg 23
	return date("d", strtotime($date));
}


function x_day_ordinal($date)
{ //eg 23rd
	return date("jS", strtotime($date));
}


function x_month_short($date)
{ //eg Aug
	return date("M", strtotime($date));
}


function x_month_long($date)
{ //eg August
	return date("F", strtotime($date));
}


function x_year_short($date)
{ //eg 18
	return date("y", strtotime($date));
}


function x_year_long($date)
{ //eg 2018
	return date("Y", strtotime($date));
}


function x_date($date)
{ //format date in the form eg 23rd Aug, 2018 from timestamp in db
	return date("jS M, Y", strtotime($date));
}

function x_dates($date)
{ //format date in the form eg 23rd Aug, 2018 from timestamp in db
	return date("jS M, Y", $date);
}


function x_date_full($date)
{ //format date in the form eg 23rd August, 2018 from timestamp in db
	return date("jS F, Y", strtotime($date));
}


function x_datetime_full($date)
{ //format date in the form eg 23rd August, 2018 21:49 PM from timestamp in db
	return date("jS F, Y h:i A", strtotime($date));
}


function check_date($date)
{
	return ($date != NULL) ? x_date($date) : '';
}


function x_time_12hour($date)
{ //eg 05:20 PM
	return date("h:i A", strtotime($date));
}

function x_time_period($date)
{ //eg 05:20 PM
	return date("A", strtotime($date));
}

function x_time_12hours($date)
{ //eg 05:20
	return date("h:i", strtotime($date));
}


function x_time_24hour($date)
{ //eg 17:20
	return date("h:i A", strtotime($date));
}


function default_calendar_date()
{ //default date for bootstrap calendar box
	$current_day = date('Y/m/d'); //in the format yyyy/mm/dd
	return $current_day;
}


function default_html_date()
{ //default date for html calendar box
	$current_day = date('m/d/Y'); //in the format mm/dd/yyyy
	return $current_day;
}


function get_month_value_short($value)
{
	switch ($value) {
		case '01':
			return 'Jan';
			break;
		case '02':
			return 'Feb';
			break;
		case '03':
			return 'Mar';
			break;
		case '04':
			return 'Apr';
			break;
		case '05':
			return 'May';
			break;
		case '06':
			return 'Jun';
			break;
		case '07':
			return 'Jul';
			break;
		case '08':
			return 'Aug';
			break;
		case '09':
			return 'Sep';
			break;
		case '10':
			return 'Oct';
			break;
		case '11':
			return 'Nov';
			break;
		case '12':
			return 'Dec';
			break;
	}
}


function get_month_value_long($value)
{
	switch ($value) {
		case '01':
			return 'January';
			break;
		case '02':
			return 'February';
			break;
		case '03':
			return 'March';
			break;
		case '04':
			return 'April';
			break;
		case '05':
			return 'May';
			break;
		case '06':
			return 'June';
			break;
		case '07':
			return 'July';
			break;
		case '08':
			return 'August';
			break;
		case '09':
			return 'September';
			break;
		case '10':
			return 'October';
			break;
		case '11':
			return 'November';
			break;
		case '12':
			return 'December';
			break;
	}
}


function get_month_value_short_array()
{
	$data = array(
		'01' => 'Jan',
		'02' => 'Feb',
		'03' => 'Mar',
		'04' => 'Apr',
		'05' => 'May',
		'06' => 'Jun',
		'07' => 'Jul',
		'08' => 'Aug',
		'09' => 'Sep',
		'10' => 'Oct',
		'11' => 'Nov',
		'12' => 'Dec'
	);
	return $data;
}


function get_month_value_long_array()
{
	$data = array(
		'01' => 'January',
		'02' => 'February',
		'03' => 'March',
		'04' => 'April',
		'05' => 'May',
		'06' => 'June',
		'07' => 'July',
		'08' => 'August',
		'09' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December'
	);
	return $data;
}


function time_ago($time)
{ //return time in ago
	//add mysql-server time difference to time;
	$time_diff = mysql_time_difference;
	$time = strtotime("+$time_diff hours", strtotime($time));
	$now = time(); //current time
	$units = 1; //units to show... eg. 9 hours ago, 3 weeks ago. 
	return strtolower(timespan($time, $now, $units)) . ' ago';
}


function get_ordinal_number($number)
{
	//NOTE: There is a CI4 helper function for this purpose using the inflector helper  
	$ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
	if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
		$ordninal = $number . 'th';
	} else {
		$ordninal = $number . $ends[$number % 10];
	}
	return $ordninal;
}


function get_ordinal_string($number)
{
	//NOTE: There is a CI4 helper function for this purpose using the inflector helper  
	$ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
	if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
		$ordninal = 'th';
	} else {
		$ordninal = $ends[$number % 10];
	}
	return $ordninal;
}


function get_slug($title)
{ //get slug from titles and captions for use in URL
	$title = str_replace(' ', '-', $title); //replace space in title with hyphen
	$slug = preg_replace('/[^A-Za-z0-9\-]/', '', $title); //allowed xters. Otherwise, remove
	return strtolower($slug);
}


function radio_value($current_value, $option_value)
{
	//check the selected radio value and set it as default (helpful when editing an entity that has a radio field)
	//this makes use of CI's set_radio() 3rd argument, which sets a radio field as default by setting its value to TRUE
	return ($current_value == $option_value) ? TRUE : NULL;
}


function generate_image_thumb($file_name, $width, $height)
{ //function to resize image while maintaining aspect ratio
	$CI =& get_instance(); //get instance of code igniter super object
	//config for image library
	$config['image_library'] = 'gd2';
	$config['source_image'] = $CI->upload->upload_path . $file_name;
	$config['create_thumb'] = TRUE;
	$config['maintain_ratio'] = TRUE;
	$config['width'] = $width;
	$config['height'] = $height;

	//load image library
	$CI->load->library('image_lib', $config);

	//resize image
	$CI->image_lib->resize();

	// handle if there is any problem
	if (!$CI->image_lib->resize()) {
		return $file_name; //if resize fails, return original image
	} else {
		$suffix = '_thumb.'; //eg cat.jpg becomes cat_thumb.jpg
		$image_thumb = str_ireplace('.', $suffix, $file_name); //add suffix
		return $image_thumb;
	}
}


function generate_ref_id($id)
{
	switch ($id) {
		case in_array($id, range(1, 9)): //1 to 9, prepend 6 zeros to id
			$ref_id = 'SMB202034QWR000000' . $id;
			break;
		case in_array($id, range(10, 99)): //10 to 99, prepend 5 zeros to id
			$ref_id = 'SMB202034QWR00000' . $id;
			break;
		case in_array($id, range(100, 999)): //100 to 999, prepend 4 zeros to id
			$ref_id = 'SMB202034QWR0000' . $id;
			break;
		case in_array($id, range(1000, 9999)): //1000 to 9999, prepend 3 zeros to id
			$ref_id = 'SMB202034QWR000' . $id;
			break;
		case in_array($id, range(10000, 99999)): //10000 to 99999, prepend 2 zeros to id
			$ref_id = 'SMB202034QWR00' . $id;
			break;
		case in_array($id, range(100000, 999999)): //100000 to 999999, prepend 1 zero to id
			$ref_id = 'SMB202034QWR0' . $id;
			break;
		default: //1000000+, return id as is
			$ref_id = $id;
			break;
	}
	return $ref_id;
}


function generate_tracking_id()
{
	$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$code = 'SMB';
	$max = strlen($characters) - 1;

	for ($i = 0; $i < 6; $i++) {
		$rand = mt_rand(0, $max);
		$code .= $characters[$rand];
	}

	return $code;
}


function getRandomName($length = 10)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}



function hash_password($password)
{
	return password_hash($password, PASSWORD_DEFAULT);
}


function generate_image_preview()
{ //generate image preview
	require "application/helpers/templates/image_preview.php";
}


function pagination_links($links, $ul_class)
{
	$link_list = "";
	foreach ($links as $link) {
		$link_list .= '<li>' . $link . '</li>';
	}
	$pagination_links = '<ul class="' . $ul_class . '">'
		. $link_list .
		'</ul>';
	return $pagination_links;
}


function user_avatar_table($user_photo, $image_src, $default_avatar)
{
	if ($user_photo != NULL) {
		$avatar = '<a target="_blank" href="' . $image_src . '"><img class="avatar" src="' . $image_src . '" /></a>';
	} else {
		$avatar = '<img class="avatar" src="' . $default_avatar . '" />';
	}
	return $avatar;
}


function checkbox_bulk_action($id)
{
	return '<input type="checkbox" class="flat bulk_select_box" name="check_bulk_action[]" value="' . $id . '" />';
}


function bulk_select_options($options)
{
	$select_options = "";
	foreach ($options as $value => $caption) {
		$select_options .= '<option value="' . $value . '" >' . $caption . '</option>';
	}
	return $select_options;
}


function modal_bulk_actions($form_url, $options)
{
	require "application/helpers/templates/modals/modal_bulk_actions.php";
}


function modal_bulk_actions_alt($form_url, $options)
{
	require "application/helpers/templates/modals/modal_bulk_actions_alt.php";
}


function delete_bulk_items($column, $table)
{ //remind to remove
	$CI =& get_instance();
	$row_id = $CI->input->post('check_bulk_action', TRUE);
	foreach ($row_id as $id) {
		$CI->db->delete($table, array($column => $id));
	}
}


function modal_delete_confirm($id, $title, $item, $url, $custom_msg = NULL)
{
	return '<div class="modal fade" id="delete' . $id . '" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<div class="pull-right">
							<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
						</div>
						<h4 class="modal-title">Delete: ' . $title . '</h4>
					</div><!--/.modal-header-->
					<div class="modal-body">
						Are you sure you want to permanently delete this <?php echo $item; ?>
						<p class="m-t-10">' . $custom_msg . '</p>
					</div>
					<div class="modal-footer">
						<a class="btn btn-sm btn-danger" role="button" href="' . base_url($url . '/' . $id) . '"> Yes, Delete </a>
						<button data-dismiss="modal" class="btn btn-sm"> No, Cancel </button>
					</div>
				</div>
			</div>
		</div>';
}


function user_titles()
{
	$titles = [
		'Mr.',
		'Mrs.',
		'Miss',
		'Mz.',
		'Dr.',
		'Barr.',
		'Engr.',
		'Lt.',
		'Capt.',
		'Chief',
		'Rev.',
		'Pst.',
		'Apst.'
	];

	return $titles;
}


function select_options_vk($options_arr, $selected_val = NULL)
{
	$options = "";
	foreach ($options_arr as $option) {
		$selected = $option == $selected_val ? 'selected' : NULL;
		$options .= '<option ' . $selected . ' value="' . $option . '">' . $option . '</option>';
	}
	echo $options;
}


if (!function_exists('isAssoc')) {
	/**
	 * --------------------------------------------------------------
	 * Is Associative Array ?
	 * --------------------------------------------------------------
	 * Checks to see if the array provided is an ` Associative Array `.
	 *
	 * @param	Array $array
	 * @return	Bool    `True` if value is an Associative Array, `False` otherwise
	 * 
	 */
	function isAssoc($array): bool
	{
		return is_array($array) and (array_values($array) !== $array);
	}
}


if (!function_exists('extractKeys')) {
	function extractKeys(array $data, array $keys)
	{
		$result = array();

		foreach ($keys as $key) {
			if (array_key_exists($key, $data)) {
				$result[$key] = $data[$key];
			}
		}

		return $result;
	}
}


if (!function_exists('valueRemoveKeys')) {
	function valueRemoveKeys($assoc_array, $values_to_remove)
	{
		foreach ($assoc_array as $key => $value) {
			if (in_array($value, $values_to_remove)) {
				unset($assoc_array[$key]);
			}
		}
		return $assoc_array;
	}
}


if (!function_exists('print_p')) {
	function print_p($val)
	{
		echo "<pre>";
		print_r($val);
		echo "</pre>";
	}
}


function isFileInputEmpty($fileInputName)
{
	if (empty($_FILES[$fileInputName]['name'])) {
		return true; // File input is empty
	} else {
		return false; // File input is not empty
	}
}