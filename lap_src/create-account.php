<!DOCTYPE html>
<html lang="en">
<head>
<title>Little Activity Pub Server</title>
<meta charset="UTF-8" />
<link rel="stylesheet" type="text/css"
	href="https://www.w3schools.com/w3css/4/w3.css" />
<link id="style" rel="stylesheet" type="text/css" href="lap.css" />
</head>
<body>
	<h1>Just a Little Activity Pub Server - Create a New Account</h1>

<?php
require_once 'conf.php';
/**
 * Create the directory where all the actor related files will be placed
 * 
 * @param string $username
 * @return boolean true if success, false if an actor with the same username already exists
 */
function createActorDirectoryIfNotExists($username){
	$userdir=LAP_USERS_DIR_PATH.$username.'/';
	if (is_dir($userdir))
		return false;
	
	if (file_exists($userdir)){
		print 'A file '.$userdir.' already exists';
		exit();
	}
	if (!mkdir($userdir)){
		print 'Unable to create directory '.$userdir;
		exit();
	}
	return true;
}
/**
 * Create the json file representing the actor
 * @param string $publickey
 * 
 * @return Object the json object representing the actor profile
 */
function createActor($username, $publickey){
	$userdir=LAP_USERS_DIR_PATH.$username.'/';
		
	$file=fopen($userdir.'actor.json','x');
	if ($file==false){
		print 'Unable to create file '.$userdir.'actor.json';
		exit();
	}
		
	$actor=new stdClass();
	$actor->{"@context"}=array("https://www.w3.org/ns/activitystreams", "https://w3id.org/security/v1");
	$actor->id=LAP_SRC_DIR_URI.'actor.php?username='.urlencode($username);
	$actor->type='Person';
	$actor->preferredUsername=$username;	
	$actor->publicKey=new stdClass();
	$actor->publicKey->id=$actor->id.'#main-key';
	$actor->publicKey->owner=$actor->id;
	$actor->publicKey->publicKeyPem=$publickey;
	$actor->inbox=LAP_SRC_DIR_URI.'inbox.php?username='.$username;	
	$actor->outbox=LAP_SRC_DIR_URI.'outbox.php?username='.$username;
	
	$actorJSON=json_encode($actor, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	fwrite($file, $actorJSON);
	fflush($file);
	fclose($file);
		
	return $actor;
}

/**
 * Create an empty inbox file for the specified actor
 * @param string $username
 * @return Object the json object representing the inbox
 */
function createEmptyInbox($username){
	$userdir=LAP_USERS_DIR_PATH.$username.'/';
	
	$file=fopen($userdir.'inbox.json','x');
	if ($file==false){
		print 'Unable to create file '.$userdir.'inbox.json';
		exit();
	}
	
	$inbox=new stdClass();
	$inbox->{"@context"}="https://www.w3.org/ns/activitystreams";
	$inbox->id=LAP_SRC_DIR_URI.'inbox.php?username='.$username;
	$inbox->type='OrderedCollection';
	$inbox->totalItems=0;
	$inbox->orderedItems=array();
	
	$inboxJSON=json_encode($inbox, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	fwrite($file, $inboxJSON);
	fflush($file);
	fclose($file);
	
	return $inbox;
	
}
session_start();
if (!isset($_POST['newusername']) || !isset($_POST['publickey']) || !isset($_POST['captcha']) || !isset($_POST['signature'])) die("wrong parameters");
$username=$_POST['newusername'];
$publickey=$_POST['publickey'];
$signature=$_POST['signature'];
$captcha=$_POST['captcha'];
if ($_SESSION['captcha'] != $captcha) {
	session_destroy();
	?>
	<div class="w3-card-4">
		<div class="w3-container">
			<p>
				Captcha validation failed: the text you enterend is wrong. <a
					href="index.php" class="w3-btn w3-teal">Back</a>
			</p>
		</div>
	</div>
<?php
} else if (openssl_verify($captcha, $signature, $publickey, OPENSSL_ALGO_SHA256)){
?>	
	<div class="w3-card-4">
		<div class="w3-container">
			<p>
				Signature verification failed.<a
					href="index.php" class="w3-btn w3-teal">Back</a>
			</p>
		</div>
	</div>
<?php
} else if (createActorDirectoryIfNotExists($username)===false){
	?>
	<div class="w3-card-4">
		<div class="w3-container">
			<p>
				The username <?=$username?> already exists. Please indicate a different username.<a
					href="index.php" class="w3-btn w3-teal">Back</a>
			</p>
		</div>
	</div>
<?php
} else {
	$actor=createActor($username, $publickey);
	createEmptyInbox($username);
?> 
	<div class="w3-card-4">
		<div class="w3-container w3-teal">
			<h2>Account <?=$username?> created</h2>
		</div>
		<div class="w3-container">
		<p>The corresponding actor is <a href="<?=$actor->id?>"><?=$actor->id?></a>.</p>
		<pre><code>
<?php 
print json_encode($actor, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?>
		</code></pre>
		<a href="index.php#actor<?=$username?>" class="w3-btn w3-teal">Back</a>
		</div>
	</div>
<?php 	
}
?>
</body>
</html>
