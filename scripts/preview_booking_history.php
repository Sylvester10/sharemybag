<?php
// Render real views and assets using synthetic rows only. No application DB, mail or providers.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root=dirname(__DIR__);define('BASEPATH',$root.'/system/');define('FCPATH',$root.'/');define('APPPATH',$root.'/application/');
function base_url($path=''){return 'http://localhost/smb/'.$path;}
function html_escape($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function form_open($url,$attributes=[]){return '<form id="search_form" action="'.base_url($url).'"><input type="hidden" name="csrf" value="preview">';}
function form_close(){return '</form>';}
function set_select($field,$value){return (($field==='location'&&$value==='Nigeria')||($field==='destination'&&$value==='United Kingdom'))?'selected':'';}
function &get_instance(){return $GLOBALS['preview_ci'];}
$GLOBALS['preview_ci']=(object)['session'=>new class{function flashdata($key){return null;}}];
require APPPATH.'helpers/app_helper.php';require APPPATH.'core/Constants.php';
class BookingUiPreview {
    public $load;
    function __construct(){$this->load=new class{function view($name){}};}
    function render($name,$title,$data){
        extract($data);$user_details=(object)['firstname'=>'Ada','lastname'=>'Example','email'=>'ada@example.invalid','selfie'=>'','is_verified'=>VERIFY_APPROVED];
        ob_start();include APPPATH.'views/users/layout/header.php';include APPPATH.'views/users/'.$name.'.php';
        echo '</div></div></div><style>#preloader{display:none!important}</style>';
        echo '<script>const base_url="'.base_url().'";window.appCsrf={name:"csrf",hash:"preview"};</script>';
        foreach(['assets/general/js/jquery-3.7.1.min.js','assets/users/libs/bootstrap/dist/js/bootstrap.bundle.min.js',
            'assets/users/libs/datatables.net/js/jquery.dataTables.min.js','assets/users/js/datatable/datatable-basic.init.js',
            'assets/users/js/plugins/toastr-init.js','assets/users/js/users_custom.js','assets/general/js/my_functions.js','assets/users/js/search.js',
            'assets/users/js/pricing_payout.js','assets/users/js/booking.js','assets/users/js/booking-history.js']as$path){
            if(is_file(FCPATH.$path)){echo '<script src="'.base_url($path).'?v='.filemtime(FCPATH.$path).'"></script>';}
        }
        // Recorded adapter for this preview only; production requests are not intercepted.
        echo <<<'JS'
<script>
jQuery.ajax=function(options){
    const timer=setTimeout(function(){
        if(options.url.indexOf('user_bookings/search')!==-1){
            if(jQuery('#select_location').val()==='Norway'){if(options.error)options.error({status:503});}
            else if(jQuery('#select_location').val()==='Canada'){options.success({status:false,msg:'No travellers are available for that route right now.'});}
            else{options.success({status:true,travellers:[{hash:'preview-only',destination:'United Kingdom',area:'Lagos',current_state:'Nigeria',departure_state:'Lagos',arrival_airport:'Heathrow',arrival_state:'London',destination_area:'London',days_remaining:'3 Days',travel_date:'6 Nov 2026',available_space:10,bag_locked:0,is_verified:2,profile_completed:1}]});}
        }else if(options.success){options.success({status:false,msg:'Preview only. No payment request was made.'});}
        if(options.complete)options.complete();
    },1800);
    return {abort:function(){clearTimeout(timer);if(options.complete)options.complete();}};
};
</script></body></html>
JS;
        return ob_get_clean();
    }
}
$renderer=new BookingUiPreview();$dir=$root.'/evidence/history-search-implementation';
if(!is_dir($dir)){mkdir($dir,0775,true);}
file_put_contents($dir.'/search.html',$renderer->render('search_travellers','Search Travellers',['account_status'=>1,'is_verified'=>VERIFY_APPROVED,'is_profile_incomplete'=>false]));
$booking=[];$booking_checkout=[];$booking_routes=[];
for($i=0;$i<14;$i++){
    $id=$i+1;$status=in_array($i,[0,2,3,4,11],true)?'pending':'completed';
    $routes=[['United Kingdom','Nigeria'],['Nigeria','United Kingdom'],['Nigeria','Canada'],['Canada','Nigeria']];
    $route=$routes[$i%4];
    $places=['United Kingdom'=>['Gatwick','Greater London'],'Nigeria'=>['Oshodi','Lagos'],'Canada'=>['Scarborough','Toronto']];
    $booking_routes[$id]=(object)['location'=>$route[0],'destination'=>$route[1],
        'area'=>$places[$route[0]][0],'current_state'=>$places[$route[0]][1],
        'destination_area'=>$places[$route[1]][0],'arrival_state'=>$places[$route[1]][1]];
    $booking[]=(object)['id'=>$id,'traveller_id'=>$id,'hash'=>'preview-booking-'.$id,'tracking_id'=>'SMB'.(7654321-$i),'currency'=>'GBP',
        'user_fullname'=>'Ada Example','traveller_name'=>'James Example','traveller_drop_address1'=>'Example drop-off point, London',
        'traveller_drop_address2'=>'Second example drop-off point','traveller_drop_date1'=>'2026-11-06',
        'traveller_current_state'=>'Nigeria','traveller_destination'=>'United Kingdom',
        'traveller_departure_state'=>$i%3===1?'London':'Lagos','traveller_arrival_state'=>$i%3===1?'Abuja':'London',
        'agent_name'=>'Ada Example','agent_phone'=>'+440000000000','agent_email'=>'ada@example.invalid',
        'agent_address'=>'Example Street','agent_locality'=>'London','agent_postcode'=>'SW1A',
        'payment_status'=>$status,'delivery_status'=>'Pending','date_added'=>gmdate('Y-m-d H:i:s',strtotime('2026-11-03 09:00:00 UTC')-$i*86400),
        'selected_space'=>$i%3===1?5:3,'items'=>$i%3===1?'[{"category":"Normal","item_name":"Clothing","size":5,"unit_price":6.5,"unit":"KG"}]':'[{"category":"Normal","item_name":"Clothing","size":2,"unit_price":6.5,"unit":"KG"},{"category":"Laptop","item_name":"Work laptop","size":1,"unit_price":30,"unit":"PC"}]','total_amount'=>46.49];
    if($status==='pending'){$booking_checkout[$id]=['resume_available'=>$i===0||$i===11,'checking_payment'=>$i===3,'review_required'=>$i===4,
        'recovery_expired'=>$i===2,'expiry_checking_payment'=>$i===3,'message'=>$i===4?'This payment needs review. Please contact support before paying again.':'',
        'resume_until'=>time()+($i===2?-10:($i===11&&in_array('--expiry-demo',$argv,true)?20:900))];}
}
file_put_contents($dir.'/history.html',$renderer->render('history','Booking History',compact('booking','booking_checkout','booking_routes')+['resume_token'=>'preview']));
echo "PASS: current views and production assets rendered with synthetic booking rows; preview requests are recorded, not sent.\n";
