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
		'United Kingdom',
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
		'Air Maroc',
		'Air Peace',
		'British Airways',
		'EgyptAir',
		'Emirates',
		'Ethiopian Airlines',
		'KLM',
		'Kenya Airways',
		'Lufthansa',
		'Qatar Airways',
		'RwandAir',
		'Turkish Airlines',
		'Virgin Atlantic',
	);
	return $airlines;
}


function kilogram()
{
	$weights = array();
	for ($i = 1; $i <= 100; $i++) {
		$weights[] = $i;
	}
	return $weights;
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


function english_cities()
{
	$cities = array(
		'Bath',
		'Birmingham',
		'Bradford',
		'Brighton',
		'Bristol',
		'Cambridge',
		'Canterbury',
		'Cardiff',
		'Carlisle',
		'Chelmsford',
		'Chester',
		'Chichester',
		'Coventry',
		'Cumbria',
		'Derby',
		'Durham',
		'Essex',
		'Exeter',
		'Gloucester',
		'Hereford',
		'Kingston upon Hull',
		'Lancaster',
		'Leeds',
		'Leicester',
		'Lichfield',
		'Lincoln',
		'Liverpool',
		'London',
		'Luton',
		'Manchester',
		'Newcastle upon Tyne',
		'Norwich',
		'Nottingham',
		'Oxford',
		'Peterborough',
		'Plymouth',
		'Portsmouth',
		'Preston',
		'Ripon',
		'Salford',
		'Salisbury',
		'Sheffield',
		'Southampton',
		'St Albans',
		'Stoke-on-Trent',
		'Sunderland',
		'Surrey',
		'Sussex',
		'Truro',
		'Wakefield',
		'Wales',
		'Wells',
		'Westminster',
		'Winchester',
		'Wolverhampton',
		'Worcester',
		'York'
	);
	return $cities;
}



function dialing_codes()
{
	$codes = array(
		'Afghanistan' => '+93',
		'Albania' => '+355',
		'Algeria' => '+213',
		'Andorra' => '+376',
		'Angola' => '+244',
		'Antigua and Barbuda' => '+1-268',
		'Argentina' => '+54',
		'Armenia' => '+374',
		'Australia' => '+61',
		'Austria' => '+43',
		'Azerbaijan' => '+994',
		'Bahamas' => '+1-242',
		'Bahrain' => '+973',
		'Bangladesh' => '+880',
		'Barbados' => '+1-246',
		'Belarus' => '+375',
		'Belgium' => '+32',
		'Belize' => '+501',
		'Benin' => '+229',
		'Bhutan' => '+975',
		'Bolivia' => '+591',
		'Bosnia and Herzegovina' => '+387',
		'Botswana' => '+267',
		'Brazil' => '+55',
		'Brunei' => '+673',
		'Bulgaria' => '+359',
		'Burkina Faso' => '+226',
		'Burundi' => '+257',
		'Cabo Verde' => '+238',
		'Cambodia' => '+855',
		'Cameroon' => '+237',
		'Canada' => '+1',
		'Central African Republic' => '+236',
		'Chad' => '+235',
		'Chile' => '+56',
		'China' => '+86',
		'Colombia' => '+57',
		'Comoros' => '+269',
		'Congo (Congo-Brazzaville)' => '+242',
		'Congo, Democratic Republic of the (Congo-Kinshasa)' => '+243',
		'Costa Rica' => '+506',
		'Croatia' => '+385',
		'Cuba' => '+53',
		'Cyprus' => '+357',
		'Czechia (Czech Republic)' => '+420',
		'Denmark' => '+45',
		'Djibouti' => '+253',
		'Dominica' => '+1-767',
		'Dominican Republic' => '+1-809',
		'Ecuador' => '+593',
		'Egypt' => '+20',
		'El Salvador' => '+503',
		'Equatorial Guinea' => '+240',
		'Eritrea' => '+291',
		'Estonia' => '+372',
		'Eswatini (fmr. "Swaziland")' => '+268',
		'Ethiopia' => '+251',
		'Fiji' => '+679',
		'Finland' => '+358',
		'France' => '+33',
		'Gabon' => '+241',
		'Gambia' => '+220',
		'Georgia' => '+995',
		'Germany' => '+49',
		'Ghana' => '+233',
		'Greece' => '+30',
		'Grenada' => '+1-473',
		'Guatemala' => '+502',
		'Guinea' => '+224',
		'Guinea-Bissau' => '+245',
		'Guyana' => '+592',
		'Haiti' => '+509',
		'Honduras' => '+504',
		'Hungary' => '+36',
		'Iceland' => '+354',
		'India' => '+91',
		'Indonesia' => '+62',
		'Iran' => '+98',
		'Iraq' => '+964',
		'Ireland' => '+353',
		'Israel' => '+972',
		'Italy' => '+39',
		'Jamaica' => '+1-876',
		'Japan' => '+81',
		'Jersey' => '+44-1534',
		'Jordan' => '+962',
		'Kazakhstan' => '+7',
		'Kenya' => '+254',
		'Kiribati' => '+686',
		'Kuwait' => '+965',
		'Kyrgyzstan' => '+996',
		'Laos' => '+856',
		'Latvia' => '+371',
		'Lebanon' => '+961',
		'Lesotho' => '+266',
		'Liberia' => '+231',
		'Libya' => '+218',
		'Liechtenstein' => '+423',
		'Lithuania' => '+370',
		'Luxembourg' => '+352',
		'Madagascar' => '+261',
		'Malawi' => '+265',
		'Malaysia' => '+60',
		'Maldives' => '+960',
		'Mali' => '+223',
		'Malta' => '+356',
		'Marshall Islands' => '+692',
		'Mauritania' => '+222',
		'Mauritius' => '+230',
		'Mexico' => '+52',
		'Micronesia' => '+691',
		'Moldova' => '+373',
		'Monaco' => '+377',
		'Mongolia' => '+976',
		'Montenegro' => '+382',
		'Morocco' => '+212',
		'Mozambique' => '+258',
		'Myanmar (formerly Burma)' => '+95',
		'Namibia' => '+264',
		'Nauru' => '+674',
		'Nepal' => '+977',
		'Netherlands' => '+31',
		'New Zealand' => '+64',
		'Nicaragua' => '+505',
		'Niger' => '+227',
		'Nigeria' => '+234',
		'North Korea' => '+850',
		'North Macedonia (formerly Macedonia)' => '+389',
		'Norway' => '+47',
		'Oman' => '+968',
		'Pakistan' => '+92',
		'Palau' => '+680',
		'Palestine State' => '+970',
		'Panama' => '+507',
		'Papua New Guinea' => '+675',
		'Paraguay' => '+595',
		'Peru' => '+51',
		'Philippines' => '+63',
		'Poland' => '+48',
		'Portugal' => '+351',
		'Qatar' => '+974',
		'Romania' => '+40',
		'Russia' => '+7',
		'Rwanda' => '+250',
		'Saint Kitts and Nevis' => '+1-869',
		'Saint Lucia' => '+1-758',
		'Saint Vincent and the Grenadines' => '+1-784',
		'Samoa' => '+685',
		'San Marino' => '+378',
		'Sao Tome and Principe' => '+239',
		'Saudi Arabia' => '+966',
		'Senegal' => '+221',
		'Serbia' => '+381',
		'Seychelles' => '+248',
		'Sierra Leone' => '+232',
		'Singapore' => '+65',
		'Slovakia' => '+421',
		'Slovenia' => '+386',
		'Solomon Islands' => '+677',
		'Somalia' => '+252',
		'South Africa' => '+27',
		'South Korea' => '+82',
		'South Sudan' => '+211',
		'Spain' => '+34',
		'Sri Lanka' => '+94',
		'Sudan' => '+249',
		'Suriname' => '+597',
		'Sweden' => '+46',
		'Switzerland' => '+41',
		'Syria' => '+963',
		'Taiwan' => '+886',
		'Tajikistan' => '+992',
		'Tanzania' => '+255',
		'Thailand' => '+66',
		'Togo' => '+228',
		'Tonga' => '+676',
		'Trinidad and Tobago' => '+1-868',
		'Tunisia' => '+216',
		'Turkey' => '+90',
		'Turkmenistan' => '+993',
		'Tuvalu' => '+688',
		'Uganda' => '+256',
		'Ukraine' => '+380',
		'United Arab Emirates' => '+971',
		'United Kingdom' => '+44',
		'United States' => '+1',
		'Uruguay' => '+598',
		'Uzbekistan' => '+998',
		'Vanuatu' => '+678',
		'Vatican City' => '+379',
		'Venezuela' => '+58',
		'Vietnam' => '+84',
		'Yemen' => '+967',
		'Zambia' => '+260',
		'Zimbabwe' => '+263',
	);
	return $codes;
}


function country_codes()
{
	$country_codes = array(
		'Afghanistan' => ['code' => '+93', 'flag' => 'cf-af'],
		'Albania' => ['code' => '+355', 'flag' => 'cf-al'],
		'Algeria' => ['code' => '+213', 'flag' => 'cf-dz'],
		'Andorra' => ['code' => '+376', 'flag' => 'cf-ad'],
		'Angola' => ['code' => '+244', 'flag' => 'cf-ao'],
		'Antigua and Barbuda' => ['code' => '+1-268', 'flag' => 'cf-ag'],
		'Argentina' => ['code' => '+54', 'flag' => 'cf-ar'],
		'Armenia' => ['code' => '+374', 'flag' => 'cf-am'],
		'Australia' => ['code' => '+61', 'flag' => 'cf-au'],
		'Austria' => ['code' => '+43', 'flag' => 'cf-at'],
		'Azerbaijan' => ['code' => '+994', 'flag' => 'cf-az'],
		'Bahamas' => ['code' => '+1-242', 'flag' => 'cf-bs'],
		'Bahrain' => ['code' => '+973', 'flag' => 'cf-bh'],
		'Bangladesh' => ['code' => '+880', 'flag' => 'cf-bd'],
		'Barbados' => ['code' => '+1-246', 'flag' => 'cf-bb'],
		'Belarus' => ['code' => '+375', 'flag' => 'cf-by'],
		'Belgium' => ['code' => '+32', 'flag' => 'cf-be'],
		'Belize' => ['code' => '+501', 'flag' => 'cf-bz'],
		'Benin' => ['code' => '+229', 'flag' => 'cf-bj'],
		'Bhutan' => ['code' => '+975', 'flag' => 'cf-bt'],
		'Bolivia' => ['code' => '+591', 'flag' => 'cf-bo'],
		'Bosnia and Herzegovina' => ['code' => '+387', 'flag' => 'cf-ba'],
		'Botswana' => ['code' => '+267', 'flag' => 'cf-bw'],
		'Brazil' => ['code' => '+55', 'flag' => 'cf-br'],
		'Brunei' => ['code' => '+673', 'flag' => 'cf-bn'],
		'Bulgaria' => ['code' => '+359', 'flag' => 'cf-bg'],
		'Burkina Faso' => ['code' => '+226', 'flag' => 'cf-bf'],
		'Burundi' => ['code' => '+257', 'flag' => 'cf-bi'],
		'Cabo Verde' => ['code' => '+238', 'flag' => 'cf-cv'],
		'Cambodia' => ['code' => '+855', 'flag' => 'cf-kh'],
		'Cameroon' => ['code' => '+237', 'flag' => 'cf-cm'],
		'Canada' => ['code' => '+1', 'flag' => 'cf-ca'],
		'Central African Republic' => ['code' => '+236', 'flag' => 'cf-cf'],
		'Chad' => ['code' => '+235', 'flag' => 'cf-td'],
		'Chile' => ['code' => '+56', 'flag' => 'cf-cl'],
		'China' => ['code' => '+86', 'flag' => 'cf-cn'],
		'Colombia' => ['code' => '+57', 'flag' => 'cf-co'],
		'Comoros' => ['code' => '+269', 'flag' => 'cf-km'],
		'Congo Brazzaville' => ['code' => '+242', 'flag' => 'cf-cg'],
		'Costa Rica' => ['code' => '+506', 'flag' => 'cf-cr'],
		'Croatia' => ['code' => '+385', 'flag' => 'cf-hr'],
		'Cuba' => ['code' => '+53', 'flag' => 'cf-cu'],
		'Cyprus' => ['code' => '+357', 'flag' => 'cf-cy'],
		'Czech Republic' => ['code' => '+420', 'flag' => 'cf-cz'],
		'Democratic Republic of the Congo' => ['code' => '+243', 'flag' => 'cf-cd'],
		'Denmark' => ['code' => '+45', 'flag' => 'cf-dk'],
		'Djibouti' => ['code' => '+253', 'flag' => 'cf-dj'],
		'Dominica' => ['code' => '+1-767', 'flag' => 'cf-dm'],
		'Dominican Republic' => ['code' => '+1-809', 'flag' => 'cf-do'],
		'Ecuador' => ['code' => '+593', 'flag' => 'cf-ec'],
		'Egypt' => ['code' => '+20', 'flag' => 'cf-eg'],
		'El Salvador' => ['code' => '+503', 'flag' => 'cf-sv'],
		'Equatorial Guinea' => ['code' => '+240', 'flag' => 'cf-gq'],
		'Eritrea' => ['code' => '+291', 'flag' => 'cf-er'],
		'Estonia' => ['code' => '+372', 'flag' => 'cf-ee'],
		'Swaziland' => ['code' => '+268', 'flag' => 'cf-sz'],
		'Ethiopia' => ['code' => '+251', 'flag' => 'cf-et'],
		'Fiji' => ['code' => '+679', 'flag' => 'cf-fj'],
		'Finland' => ['code' => '+358', 'flag' => 'cf-fi'],
		'France' => ['code' => '+33', 'flag' => 'cf-fr'],
		'Gabon' => ['code' => '+241', 'flag' => 'cf-ga'],
		'Gambia' => ['code' => '+220', 'flag' => 'cf-gm'],
		'Georgia' => ['code' => '+995', 'flag' => 'cf-ge'],
		'Germany' => ['code' => '+49', 'flag' => 'cf-de'],
		'Ghana' => ['code' => '+233', 'flag' => 'cf-gh'],
		'Greece' => ['code' => '+30', 'flag' => 'cf-gr'],
		'Grenada' => ['code' => '+1-473', 'flag' => 'cf-gd'],
		'Guatemala' => ['code' => '+502', 'flag' => 'cf-gt'],
		'Guinea' => ['code' => '+224', 'flag' => 'cf-gn'],
		'Guinea Bissau' => ['code' => '+245', 'flag' => 'cf-gw'],
		'Guyana' => ['code' => '+592', 'flag' => 'cf-gy'],
		'Haiti' => ['code' => '+509', 'flag' => 'cf-ht'],
		'Honduras' => ['code' => '+504', 'flag' => 'cf-hn'],
		'Hungary' => ['code' => '+36', 'flag' => 'cf-hu'],
		'Iceland' => ['code' => '+354', 'flag' => 'cf-is'],
		'India' => ['code' => '+91', 'flag' => 'cf-in'],
		'Indonesia' => ['code' => '+62', 'flag' => 'cf-id'],
		'Iran' => ['code' => '+98', 'flag' => 'cf-ir'],
		'Iraq' => ['code' => '+964', 'flag' => 'cf-iq'],
		'Ireland' => ['code' => '+353', 'flag' => 'cf-ie'],
		'Israel' => ['code' => '+972', 'flag' => 'cf-il'],
		'Italy' => ['code' => '+39', 'flag' => 'cf-it'],
		'Jamaica' => ['code' => '+1-876', 'flag' => 'cf-jm'],
		'Japan' => ['code' => '+81', 'flag' => 'cf-jp'],
		'Jordan' => ['code' => '+962', 'flag' => 'cf-jo'],
		'Kazakhstan' => ['code' => '+7', 'flag' => 'cf-kz'],
		'Kenya' => ['code' => '+254', 'flag' => 'cf-ke'],
		'Kiribati' => ['code' => '+686', 'flag' => 'cf-ki'],
		'Kuwait' => ['code' => '+965', 'flag' => 'cf-kw'],
		'Kyrgyzstan' => ['code' => '+996', 'flag' => 'cf-kg'],
		'Laos' => ['code' => '+856', 'flag' => 'cf-la'],
		'Latvia' => ['code' => '+371', 'flag' => 'cf-lv'],
		'Lebanon' => ['code' => '+961', 'flag' => 'cf-lb'],
		'Lesotho' => ['code' => '+266', 'flag' => 'cf-ls'],
		'Liberia' => ['code' => '+231', 'flag' => 'cf-lr'],
		'Libya' => ['code' => '+218', 'flag' => 'cf-ly'],
		'Liechtenstein' => ['code' => '+423', 'flag' => 'cf-li'],
		'Lithuania' => ['code' => '+370', 'flag' => 'cf-lt'],
		'Luxembourg' => ['code' => '+352', 'flag' => 'cf-lu'],
		'Madagascar' => ['code' => '+261', 'flag' => 'cf-mg'],
		'Malawi' => ['code' => '+265', 'flag' => 'cf-mw'],
		'Malaysia' => ['code' => '+60', 'flag' => 'cf-my'],
		'Maldives' => ['code' => '+960', 'flag' => 'cf-mv'],
		'Mali' => ['code' => '+223', 'flag' => 'cf-ml'],
		'Malta' => ['code' => '+356', 'flag' => 'cf-mt'],
		'Marshall Islands' => ['code' => '+692', 'flag' => 'cf-mh'],
		'Mauritania' => ['code' => '+222', 'flag' => 'cf-mr'],
		'Mauritius' => ['code' => '+230', 'flag' => 'cf-mu'],
		'Mexico' => ['code' => '+52', 'flag' => 'cf-mx'],
		'Micronesia' => ['code' => '+691', 'flag' => 'cf-fm'],
		'Moldova' => ['code' => '+373', 'flag' => 'cf-md'],
		'Monaco' => ['code' => '+377', 'flag' => 'cf-mc'],
		'Mongolia' => ['code' => '+976', 'flag' => 'cf-mn'],
		'Montenegro' => ['code' => '+382', 'flag' => 'cf-me'],
		'Morocco' => ['code' => '+212', 'flag' => 'cf-ma'],
		'Mozambique' => ['code' => '+258', 'flag' => 'cf-mz'],
		'Myanmar' => ['code' => '+95', 'flag' => 'cf-mm'],
		'Namibia' => ['code' => '+264', 'flag' => 'cf-na'],
		'Nauru' => ['code' => '+674', 'flag' => 'cf-nr'],
		'Nepal' => ['code' => '+977', 'flag' => 'cf-np'],
		'Netherlands' => ['code' => '+31', 'flag' => 'cf-nl'],
		'New Zealand' => ['code' => '+64', 'flag' => 'cf-nz'],
		'Nicaragua' => ['code' => '+505', 'flag' => 'cf-ni'],
		'Niger' => ['code' => '+227', 'flag' => 'cf-ne'],
		'Nigeria' => ['code' => '+234', 'flag' => 'cf-ng'],
		'North Korea' => ['code' => '+850', 'flag' => 'cf-kp'],
		'North Macedonia' => ['code' => '+389', 'flag' => 'cf-mk'],
		'Norway' => ['code' => '+47', 'flag' => 'cf-no'],
		'Oman' => ['code' => '+968', 'flag' => 'cf-om'],
		'Pakistan' => ['code' => '+92', 'flag' => 'cf-pk'],
		'Palau' => ['code' => '+680', 'flag' => 'cf-pw'],
		'Palestine State' => ['code' => '+970', 'flag' => 'cf-ps'],
		'Panama' => ['code' => '+507', 'flag' => 'cf-pa'],
		'Papua New Guinea' => ['code' => '+675', 'flag' => 'cf-pg'],
		'Paraguay' => ['code' => '+595', 'flag' => 'cf-py'],
		'Peru' => ['code' => '+51', 'flag' => 'cf-pe'],
		'Philippines' => ['code' => '+63', 'flag' => 'cf-ph'],
		'Poland' => ['code' => '+48', 'flag' => 'cf-pl'],
		'Portugal' => ['code' => '+351', 'flag' => 'cf-pt'],
		'Qatar' => ['code' => '+974', 'flag' => 'cf-qa'],
		'Romania' => ['code' => '+40', 'flag' => 'cf-ro'],
		'Russia' => ['code' => '+7', 'flag' => 'cf-ru'],
		'Rwanda' => ['code' => '+250', 'flag' => 'cf-rw'],
		'Saint Kitts and Nevis' => ['code' => '+1-869', 'flag' => 'cf-kn'],
		'Saint Lucia' => ['code' => '+1-758', 'flag' => 'cf-lc'],
		'Saint Vincent and the Grenadines' => ['code' => '+1-784', 'flag' => 'cf-vc'],
		'Samoa' => ['code' => '+685', 'flag' => 'cf-ws'],
		'San Marino' => ['code' => '+378', 'flag' => 'cf-sm'],
		'Sao Tome and Principe' => ['code' => '+239', 'flag' => 'cf-st'],
		'Saudi Arabia' => ['code' => '+966', 'flag' => 'cf-sa'],
		'Senegal' => ['code' => '+221', 'flag' => 'cf-sn'],
		'Serbia' => ['code' => '+381', 'flag' => 'cf-rs'],
		'Seychelles' => ['code' => '+248', 'flag' => 'cf-sc'],
		'Sierra Leone' => ['code' => '+232', 'flag' => 'cf-sl'],
		'Singapore' => ['code' => '+65', 'flag' => 'cf-sg'],
		'Slovakia' => ['code' => '+421', 'flag' => 'cf-sk'],
		'Slovenia' => ['code' => '+386', 'flag' => 'cf-si'],
		'Solomon Islands' => ['code' => '+677', 'flag' => 'cf-sb'],
		'Somalia' => ['code' => '+252', 'flag' => 'cf-so'],
		'South Africa' => ['code' => '+27', 'flag' => 'cf-za'],
		'South Korea' => ['code' => '+82', 'flag' => 'cf-kr'],
		'South Sudan' => ['code' => '+211', 'flag' => 'cf-ss'],
		'Spain' => ['code' => '+34', 'flag' => 'cf-es'],
		'Sri Lanka' => ['code' => '+94', 'flag' => 'cf-lk'],
		'Sudan' => ['code' => '+249', 'flag' => 'cf-sd'],
		'Suriname' => ['code' => '+597', 'flag' => 'cf-sr'],
		'Sweden' => ['code' => '+46', 'flag' => 'cf-se'],
		'Switzerland' => ['code' => '+41', 'flag' => 'cf-ch'],
		'Syria' => ['code' => '+963', 'flag' => 'cf-sy'],
		'Taiwan' => ['code' => '+886', 'flag' => 'cf-tw'],
		'Tajikistan' => ['code' => '+992', 'flag' => 'cf-tj'],
		'Tanzania' => ['code' => '+255', 'flag' => 'cf-tz'],
		'Thailand' => ['code' => '+66', 'flag' => 'cf-th'],
		'Togo' => ['code' => '+228', 'flag' => 'cf-tg'],
		'Tonga' => ['code' => '+676', 'flag' => 'cf-to'],
		'Trinidad and Tobago' => ['code' => '+1-868', 'flag' => 'cf-tt'],
		'Tunisia' => ['code' => '+216', 'flag' => 'cf-tn'],
		'Turkey' => ['code' => '+90', 'flag' => 'cf-tr'],
		'Turkmenistan' => ['code' => '+993', 'flag' => 'cf-tm'],
		'Tuvalu' => ['code' => '+688', 'flag' => 'cf-tv'],
		'Uganda' => ['code' => '+256', 'flag' => 'cf-ug'],
		'Ukraine' => ['code' => '+380', 'flag' => 'cf-ua'],
		'United Arab Emirates' => ['code' => '+971', 'flag' => 'cf-ae'],
		'United Kingdom' => ['code' => '+44', 'flag' => 'cf-gb'],
		'United States' => ['code' => '+1', 'flag' => 'cf-us'],
		'Uruguay' => ['code' => '+598', 'flag' => 'cf-uy'],
		'Uzbekistan' => ['code' => '+998', 'flag' => 'cf-uz'],
		'Vanuatu' => ['code' => '+678', 'flag' => 'cf-vu'],
		'Vatican City' => ['code' => '+379', 'flag' => 'cf-va'],
		'Venezuela' => ['code' => '+58', 'flag' => 'cf-ve'],
		'Vietnam' => ['code' => '+84', 'flag' => 'cf-vn'],
		'Yemen' => ['code' => '+967', 'flag' => 'cf-ye'],
		'Zambia' => ['code' => '+260', 'flag' => 'cf-zm'],
		'Zimbabwe' => ['code' => '+263', 'flag' => 'cf-zw'],

	);
	return $country_codes;
}

function country_to_code($country_name)
{
	$countries = [
		'United States' => 'us',
		'United Kingdom' => 'gb',
		'Nigeria' => 'ng',
		'France' => 'fr',
		'Germany' => 'de',
		// Add other country mappings...
	];
	return isset($countries[$country_name]) ? $countries[$country_name] : 'unknown';
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
	$CI = &get_instance(); //get instance of code igniter super object
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
	$CI = &get_instance(); //get instance of code igniter super object
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


function custom_flash_message_success($message)
{
	$CI = &get_instance();

	// Check if flash data exists
	if ($CI->session->flashdata($message)) {
		$msg = $CI->session->flashdata($message);
		$title = $CI->session->flashdata('title') ?? 'Success';
		$timeout = $CI->session->flashdata('msg_timeout') ?? 6000;

		// Generate toastr success notification script
		echo "<script>
                window.addEventListener('DOMContentLoaded', function() {
                    toastr.success('{$msg}', '{$title}', {
                        progressBar: true,
                        timeOut: {$timeout}
                    });
                });
              </script>";

		// Clear the flash data
		$CI->session->set_flashdata($message, false);
	}
}



function custom_flash_message_danger($message)
{
	$CI = &get_instance();

	// Check if flash data exists
	if ($CI->session->flashdata($message)) {
		$msg = $CI->session->flashdata($message);
		$title = $CI->session->flashdata('title') ?? 'Error';
		$timeout = $CI->session->flashdata('msg_timeout') ?? 5000;

		// Generate toastr error notification script
		echo "<script>
                window.addEventListener('DOMContentLoaded', function() {
                    toastr.error('{$msg}', '{$title}', {
                        progressBar: true,
                        timeOut: {$timeout}
                    });
                });
              </script>";

		// Clear the flash data
		$CI->session->set_flashdata($message, false);
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

function x_month_year($date)
{ //format date in the form eg August, 2018 from timestamp in db
	return date("F, Y", strtotime($date));
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

function x_date_month_time_full($date)
{ //format date in the form eg 23rd August, 2018 21:49 PM from timestamp in db
	return date("jS M, Y h:i A", strtotime($date));
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


function get_last_login_ago($last_login)
{
	if ($last_login == NULL) {
		return '<span class="text-danger">Unknown</span>';
	}
	//add mysql-server time difference to time;
	//$time_diff = mysql_time_difference;
	$last_login = strtotime($last_login);
	//$last_login = strtotime("+$time_diff hours", strtotime($last_login));
	$now = time(); //current time
	$units = 1; //units to show... eg. 9 hours ago, 3 weeks ago. 
	if (($now - $last_login) <= login_refresh_time) {
		return '<span class="text-success text-bold">Online<sup><i class="fa fa-dot-circle-o fa-pulse"></i></sup></span>';
	} else {
		return strtolower(timespan($last_login, $now, $units)) . ' ago';
	}
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
	$CI = &get_instance(); //get instance of code igniter super object
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


function generate_tracking_id($length = 6)
{
	$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$code = 'SMB';
	$max = strlen($characters) - 1;

	for ($i = 0; $i < $length; $i++) {
		$rand = mt_rand(0, $max);
		$code .= $characters[$rand];
	}

	return $code;
}


function generate_verification_code()
{
	$characters = '0123456789';
	$max = strlen($characters) - 1;
	$code = '';  // Initialize $code

	for ($i = 0; $i < 6; $i++) {
		$rand = mt_rand(0, $max); // Generate a random index
		$code .= $characters[$rand];  // Append the random character
	}

	return $code;  // Return strictly 6 characters
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


if (! function_exists('isValue')) {
	function isValue($table, $column, $value)
	{
		// Get CodeIgniter instance
		$CI = &get_instance();

		// Load database library
		$CI->load->database();

		// Query to check if value exists in the specified column of the specified table
		$query = $CI->db->select($column)
			->from($table)
			->where($column, $value)
			->get();

		// Check if there's a row with the specified value in the specified column
		return ($query->num_rows() > 0);
	}
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
	$CI = &get_instance();
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


if (! function_exists('addSeperator')) {
	function addSeperator($string, $blocks, $separator = "-")
	{
		$result = '';
		$string_length = strlen($string);
		$index = 0;
		foreach ($blocks as $length) {
			$section = substr($string, $index, $length);
			$result .= $section . $separator;
			$index += $length;
		}
		// Remove the last separator character
		$result = rtrim($result, $separator);
		return $result;
	}
}

function production_url($path = '') {
	return trim('https://sharemybag.co.uk/'.$path);
}
