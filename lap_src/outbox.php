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
	print $headerName.' header required';
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
	if (!str_starts_with($actorURI, LAP_USERS_DIR_URI)){
		print 'Invalid actor '.$actorURI;
		exit(401);
	}
	$actorpath=substr($actorURI, strlen(LAP_USERS_DIR_URI));
	// get the actor object
	$actorStr=file_get_contents(LAP_USERS_DIR_PATH.$actorpath);
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

//body of the post request
$requestBody=file_get_contents('php://input');
$digest=getDigestOrDie($requestBody);

$activity=json_decode($requestBody);
if ($activity==null){
	print 'Activity is not a valid json object';
	exit(400);
}

$date=getHeaderOrDie('X-OpenDataHacklab-activitydate');
$signatureHeader=getHeaderOrDie('Signature');

$actor=getLocalActorOrDie($activity->actor);

verifySignatureOrDie($signatureHeader, $actor, $date, $digest);



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