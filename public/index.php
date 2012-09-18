<?php
//the winds be blowing, batten down the settings and configuration
define('PIRATE_API', 'http://isithackday.com/arrpi.php?');
define('NEXMO_API', 'http://rest.nexmo.com/sms/json?');
foreach(array('NEXMO_KEY' => 'key', 'NEXMO_SECRET' => 'secret') as $setting => $default){
    defined($setting)
    || define($setting, (getenv($setting) ? getenv($setting) : $default));
}

//be thar request valid, with all expected parameters?
foreach(array('to', 'msisdn', 'text') as $param){
    if(!isset($_REQUEST[$param])){
        error_log('missing required param: ' . $param);
        error_log(var_export($_REQUEST, true));
        return; //do nothing
    }
}

//get yonder pirate translation
try{
    $text = file_get_contents(PIRATE_API . http_build_query(array('text' => $_REQUEST['text'])));
} catch(Exception $e) {
    error_log($e->getMessage());
    $text = "Arg. Something be wrong. If you be feeling lucky, you might be thinking of trying it again.";
}

//shove off matey, and this here message goes in the Nexmo bottle
$response = file_get_contents(NEXMO_API . http_build_query(array(
	'username' => NEXMO_KEY, 
	'password' => NEXMO_SECRET, 
	'to' => $_REQUEST['msisdn'], 
	'from' => $_REQUEST['to'],
    'text' => $text
)));

//echo $response;

//keep a right ship's log
$data = json_decode($response, true);
if(!isset($data['messages'][0]['message-id'])){
    error_log('error: ' . $response);
    return;
}

error_log("sent: '{$text}' to: {$data['messages'][0]['to']} from: {$_REQUEST['to']} [ {$data['messages'][0]['message-id']} ]");