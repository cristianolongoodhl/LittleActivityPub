<?php

/**
 * Handle WebFinger (RFC7033) requests in order to return user actor URI from actor preferred name.
 */  
$resource=$_GET['resource'];
if (!isset($resource)){
	http_response_code(400); 
	print 'no resource provided';
	exit;
}

if (!str_starts_with($resource,'acct:')){
	http_response_code(400); 
	print 'resource must start with acct:';
	exit;
}

//5 is strlen('acct:');
$resourcePieces=explode('@',substr($resource, 5));
//if (count($resourcePieces)!=2){
//	http_response_code(400); 
//	print 'invalid actor';
//	exit;
//}

$username=$resourcePieces[0];
$actorJsonRelPath='../lap_users/'.$username.'.json';
if (!file_exists($actorJsonRelPath)){
	http_response_code(404); 
	print 'user '.$username.' not found';
	exit;
}

header('Content-Type: application/jrd+json');
header('Access-Control-Allow-Origin: *');
$jrd=new stdClass();
$jrd->subject=$resource;
$link=new stdClass();
$jrd->links=array($link);
$link->rel='self';
$link->type='application/activity+json';
//31 is the size of lap_src/webfinger.php?resource=
$baseURI=(empty($_SERVER['HTTPS']) ? 'http' : 'https').'://'.$_SERVER['SERVER_NAME'].substr($_SERVER['REQUEST_URI'],0,strlen($_SERVER['REQUEST_URI'])-31-strlen($resource));
$link->href=$baseURI.'lap_users/'.$username.".json";


print json_encode($jrd, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?>
