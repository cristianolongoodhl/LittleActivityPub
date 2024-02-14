<?php
require_once 'conf.php';

$requestHeaders=getAllHeaders();
if (!isset($requestHeaders['Digest'])){
	print 'Digest header required'; 
	exit(400);
}
$digest=$requestHeaders['Digest'];

if (!isset($requestHeaders['X-OpenDataHacklab-activitydate'])){
	print 'X-OpenDataHacklab-activitydate header required';
	exit(400);
}
$date=$requestHeaders['X-OpenDataHacklab-activitydate'];

if (!isset($requestHeaders['Signature'])){
	print 'Signature header required';
	exit(400);
}
$signatureHeader=$requestHeaders['Signature'];
$r=array();
preg_match('/keyId="(.*?)"/', $signatureHeader, $r);
$signatureKeyId=$r[1];
preg_match('/signature="(.*?)"/', $signatureHeader, $r);
$signature=$r[1];

//body of the post request
$requestBody=file_get_contents('php://input');
$activityJson=json_decode($requestBody);
if ($activityJson==null){
	print 'Activity is not a valid json object';
	exit(400);	
}

if (!str_starts_with($activityJson->actor, LAP_USERS_DIR_URI)){
	print 'Invalid actor '.$activityJson->actor;
	exit(401);
}
$actorpath=substr($activityJson->actor, strlen(LAP_USERS_DIR_URI));
// get the actor object 
$actorStr=file_get_contents(LAP_USERS_DIR_PATH.$actorpath);
if ($actorStr==false){
	print 'No such actor '.$activityJson->actor;
	exit(401);
}

//from now on we can assume that the actor is well-formed
$actor=json_decode($actorStr);


//check signature
if (strcmp($actor->id, $signatureKeyId)!=0){
	print 'Signature keyId '.$signatureKeyId.' differs from actor';
	exit(401);
}
$signatureVerification=openssl_verify("date: $date\ndigest: $digest", base64_decode($signature),
	$actor->publicKey->publicKeyPem, OPENSSL_ALGO_SHA256);
if ($signatureVerification==0){
	print 'Signature verification failed '.$signature;
	exit(401);
} else if ($signatureVerification==-1 || $signatureVerification==false){
	print 'Unable to verify signature';
	exit(500);
}

header('Access-Control-Allow-Origin: *');

$f=fopen(LAP_USERS_DIR_PATH.'outbox.log','a+');

fwrite($f,"HEADERS\n");
foreach (getallheaders() as $name => $value) {
	fwrite($f, "$name: $value\n");
}
fwrite($f,"BODY\n");
$requestBody = file_get_contents('php://input');
fwrite($f, "$requestBody \n");
fflush($f);
fclose($f);

print 'ciao';
?>