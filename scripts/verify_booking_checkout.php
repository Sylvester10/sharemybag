<?php
// Focused, reproducible verification. --mysql uses disposable tables/database; --http targets localhost only.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root=dirname(__DIR__);$dir=$root.'/evidence/booking-recovery';
if(!is_dir($dir)){mkdir($dir,0775,true);}
$commands=[];
foreach(['booking_checkout_flow','booking_checkout','booking_checkout_quote','booking_gateway','booking_webhook',
    'booking_recovery_view','booking_history_ui','booking_history_routes','traveller_search_cards','booking_mail_once','booking_wizard_stepper','booking_payment_options','booking_vat_suspension',
    'bulk_mail_queue','mail_request_safety','mail_safety']as$test){
    $commands[]=['name'=>$test,'argv'=>[PHP_BINARY,'tests/'.$test.'_test.php'],'definition_file'=>'tests/'.$test.'_test.php'];
}
foreach(['booking_restore','traveller_search_loading','signup_feedback']as$test){
    $commands[]=['name'=>$test,'argv'=>['node','tests/'.$test.'_test.js'],'definition_file'=>'tests/'.$test.'_test.js'];
}
if(in_array('--mysql',$argv,true)){
    $commands[]=['name'=>'mysql_model','argv'=>[PHP_BINARY,'tests/booking_checkout_test.php'],
        'definition_file'=>'tests/booking_checkout_test.php','env'=>['SMB_CHECKOUT_MYSQL'=>'1']];
    $commands[]=['name'=>'mysql_concurrency','argv'=>[PHP_BINARY,'tests/booking_checkout_concurrency_test.php'],
        'definition_file'=>'tests/booking_checkout_concurrency_test.php'];
}
if(in_array('--http',$argv,true)){
    $commands[]=['name'=>'runtime-proof','argv'=>[PHP_BINARY,'scripts/verify_booking_checkout_http.php'],
        'definition_file'=>'scripts/verify_booking_checkout_http.php'];
}
function proof_run($argv,$root,$env=[]){
    $pipes=[];$proc=proc_open($argv,[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root,array_merge(getenv(),$env));
    if(!is_resource($proc)){throw new RuntimeException('Unable to start proof command.');}
    $stdout=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);
    fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($proc);
    return ['exit_code'=>$code,'stdout'=>$stdout,'stderr'=>$stderr];
}
$changed=proof_run(['git','diff','--name-only','--diff-filter=ACMRT'],$root)['stdout'];
$untracked=proof_run(['git','ls-files','--others','--exclude-standard'],$root)['stdout'];
$files=array_values(array_unique(array_filter(explode("\n",trim($changed."\n".$untracked)))));
foreach($files as$file){
    if(preg_match('~^(application|tests|scripts)/.*\.php$~',$file)){
        $commands[]=['name'=>'syntax:'.$file,'argv'=>[PHP_BINARY,'-l',$file],'definition_file'=>$file];
    }elseif(preg_match('~^assets/.*\.js$~',$file)){
        $commands[]=['name'=>'syntax:'.$file,'argv'=>['node','--check',$file],'definition_file'=>$file];
    }
}
$commands[]=['name'=>'whitespace','argv'=>['git','-c','core.whitespace=cr-at-eol','diff','--check'],'definition_file'=>'.gitignore'];
$report=['schema_version'=>1,'generated_at'=>gmdate('c'),'commands'=>[],'passed'=>true];
foreach($commands as$command){
    if(!is_file($root.'/'.$command['definition_file'])){throw new RuntimeException('Missing verification definition file.');}
    $result=proof_run($command['argv'],$root,$command['env']??[]);
    $report['commands'][]=$command+$result;
    if($result['exit_code']!==0){$report['passed']=false;echo 'FAIL '.$command['name']."\n".$result['stdout'].$result['stderr'];}
}
file_put_contents($dir.'/command-transcript.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
$evidence=[];
if(in_array('--http',$argv,true)){$evidence[]=['path'=>'evidence/booking-recovery/http-boundaries.json','produced_by'=>'runtime-proof','kind'=>'runtime','max_age_hours'=>24];}
file_put_contents($dir.'/BUILD_VERIFICATION.json',json_encode(['schema_version'=>1,'persistent_data'=>true,
    'scope'=>'booking recovery','commands'=>$commands,'evidence'=>$evidence],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
$status=proof_run(['git','status','--short'],$root);$revision=proof_run(['git','rev-parse','HEAD'],$root);
$hashes=[];foreach($files as$file){if(is_file($root.'/'.$file)){$hashes[$file]=hash_file('sha256',$root.'/'.$file);}}
file_put_contents($dir.'/BUILD_STATE.json',json_encode(['generated_at'=>gmdate('c'),'revision'=>trim($revision['stdout']),
    'working_tree'=>$status['stdout'],'source_sha256'=>$hashes,'checks_passed'=>$report['passed'],
    'command_transcript'=>'evidence/booking-recovery/command-transcript.json',
    'migration_state'=>is_file($dir.'/migration-state.json')?json_decode(file_get_contents($dir.'/migration-state.json'),true):null,
    'review'=>'Independent review informed earlier corrections; final corrections self-reviewed.',
    'unverified'=>['live provider end-to-end flow','authenticated desktop/mobile browser flow','production SQL/webhooks/cron activation','manual refunds']
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
echo ($report['passed']?'PASS':'FAIL').': '.count($report['commands'])." focused checks; transcript in evidence/booking-recovery/command-transcript.json.\n";
exit($report['passed']?0:1);
