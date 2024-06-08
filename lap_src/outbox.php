<?php
require_once 'conf.php';

/**
 * If the header with the specified name has been received, 
 * return the corresponding value. Die with a bad request error code otherwise.
 * 
 * @param string $headerName name of the header
 * @return string header value
 */
function getHeaderOrDie(string $headerName){
	$requestHeaders=getAllHeaders();
	if (isset($requestHeaders[$headerName]))
		return $requestHeaders[$headerName];
	print $headerName.' header required'.PHP_EOL;
	print 'Received headers :';
	foreach($requestHeaders as $k=>$v)
		print $k.' ';	
	exit(400);
}

/**
 * Check that the digest provided by the digest header corresponds with the request body
 * 
 * @param $requestBody 
 * @return $string request body digest
 */
function getDigestOrDie(string $requestBody){
	$digestHeader=getHeaderOrDie('Digest');
	$expected='SHA-256='.base64_encode(openssl_digest($requestBody, 'sha256', true));
	if (strcmp($digestHeader, $expected)==0)
		return $digestHeader;
	print 'Digest header does not match request body';
	print "\nExpected ".$expected;
	print "\nActual ".$digestHeader;
	exit(401);
}

/**
 * If the actor is a local user, return the actor description. Die with a 401 error code, otherwise
 * @param string $actorURI
 * @return Object an object representing the actor 
 */
function getLocalActorOrDie(string $actorURI){
	$prefix=LAP_SRC_DIR_URI.'actor.php?username=';
	if (!str_starts_with($actorURI, $prefix)){
		print 'Invalid actor '.$actorURI;
		exit(401);
	}
	$username=urldecode(substr($actorURI, strlen($prefix)));
	// get the actor object
	$actorStr=file_get_contents(LAP_USERS_DIR_PATH.$username.'/actor.json');
	if ($actorStr==false){
		print 'No such actor '.$actorURI;
		exit(401);
	}
	//from now on we can assume that the actor is well-formed
	return json_decode($actorStr);	
}

/**
 * Extract the parameter from a signature header value
 * @param string $name
 * @param string $signatureHeader
 * @return string
 */
function getSignatureParameter(string $parameter, string $signatureHeader){
	$r=array();
	preg_match('/'.$parameter.'="(.*?)"/', $signatureHeader, $r);
	return $r[1];
}

/**
 * Perform the signature verification
 * 
 * @param string $signatureHeader 
 * @param object $actor 
 * @param string $date
 * @param string $digest
 */
function verifySignatureOrDie(string $signatureHeader, object $actor, string $date, string $digest){
	$keyId=getSignatureParameter('keyId', $signatureHeader);
	if (strcmp($actor->id, $keyId)!=0){
		print 'Signature keyId '.$keyId.' differs from actor';
		exit(401);
	}
		
	$signature=getSignatureParameter('signature', $signatureHeader);
	$signatureVerification=openssl_verify("date: $date\ndigest: $digest", base64_decode($signature),
		$actor->publicKey->publicKeyPem, OPENSSL_ALGO_SHA256);
	if ($signatureVerification==0){
		print 'Signature verification failed '.$signature;
		exit(401);
	} else if ($signatureVerification==-1 || $signatureVerification==false){
		print 'Unable to verify signature';
		exit(500);
	}
}

/**
 * Retrieve the inbox to be used to send an activity to an actor. Precedence is given to shared inboxes
 * @param $actorURI object description 
 * @return string|NULL the inbox, if any. Null otherwise 
 */
function retrieveInbox(string $actorURI){
	$ch=curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $actorURI);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/ld+json; profile="https://www.w3.org/ns/activitystreams'));
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	
	$result = curl_exec($ch);
	if(curl_error($ch)) {
		print 'Unable to fetch '.$actorURI." . Error: ".curl_errno($ch).PHP_EOL;
		curl_close($ch);
		return null;
	}
	curl_close($ch);
	
	$actor=json_decode($result);
	if (isset($actor->endpoints) && isset($actor->endpoints->sharedInbox))
		return $actor->endpoints->sharedInbox;
	if (isset($actor->inbox))
		return $actor->inbox;
	
	print 'No inbox defined for '.$actorURI.PHP_EOL;
	return null;
}

/**
 * Put all the inboxes of actors in the array into the $inboxes result parameter
 * @param $inboxes
 * @param $actorURIs 
 */
function retrieveAllInboxes(array &$inboxes, $actorURIOrArray){
	$actorURIs=is_array($actorURIOrArray) ? $actorURIOrArray : array($actorURIOrArray);
	foreach($actorURIs as $actorURI){
		$inbox=retrieveInbox($actorURI);
		if ($inbox!=null)
			$inboxes[$inbox]=$actorURI;
	}
}

/**
 * Retrieve all the inboxes of agents in the audience target of an activity
 * @param object $activity
 * @return array target inboxes
 */
function retrieveTargetInboxes(object $activity){
	$inboxes=array();
	if (isset($activity->to))
		retrieveAllInboxes($inboxes, $activity->to);
	if (isset($activity->cc))
		retrieveAllInboxes($inboxes, $activity->cc);
	if (isset($activity->bto))
		retrieveAllInboxes($inboxes, $activity->bto);
	if (isset($activity->bcc))
		retrieveAllInboxes($inboxes, $activity->bcc);
	if (isset($activity->audience))
		retrieveAllInboxes($inboxes, $activity->audience);
	return array_keys($inboxes);						
}


/**
 * Send a POST request to $targetURI with the specified headers and body
 * 
 * @param string $activityAsStr POST body
 * @param string $date value for the date header
 * @param string $digest value for the digest header
 * @param string $signature value for the signature header
 * @param string $inbox post request target
 */
function post(string $activityAsStr, string $date, string $digest, string $signatureHeader, string $inbox){
	$inboxURIComponents = parse_url($inbox);	
	$inboxHost = $inboxURIComponents['host'];
	$headers = ['Host: ' . $inboxHost, 'Date: ' . $date, 'Digest: ' . $digest, 'Signature: ' . $signatureHeader, 		
		'Content-Type: application/activity+json'];
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $inbox);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $activityAsStr);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	
	$result = curl_exec($ch);
	$responseInfo = curl_getInfo($ch);
	print 'Sending to '.$inbox.' Response status '.$responseInfo["http_code"].($result?'':curl_error($ch));
	curl_close($ch);
}

//body of the post request
$requestBody=file_get_contents('php://input');
$digest=getDigestOrDie($requestBody);

$activity=json_decode($requestBody);
if ($activity==null){
	print 'Activity is not a valid json object';
	exit(400);
}

$date=getHeaderOrDie('X-Opendatahacklab-Activitydate');
$signatureHeader=getHeaderOrDie('Signature');
$actor=getLocalActorOrDie($activity->actor);
verifySignatureOrDie($signatureHeader, $actor, $date, $digest);

header('Access-Control-Allow-Origin: *');

$inboxes=retrieveTargetInboxes($activity);
foreach ($inboxes as $inbox)
	post($requestBody, $date, $digest, $signatureHeader, $inbox);
?>
