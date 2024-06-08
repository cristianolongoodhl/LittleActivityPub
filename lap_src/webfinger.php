<?php
require 'conf.php';
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

$username=$resourcePieces[0];
$actorJsonRelPath=LAP_USERS_DIR_PATH.$username;
if (!is_dir($actorJsonRelPath)){
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
$link->href=LAP_SRC_DIR_URI.'/actor.php?username='.urlencode($username);


print json_encode($jrd, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?>
