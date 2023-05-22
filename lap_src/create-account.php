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

/**
 * @param string $username
 * @param string $publickey
 * 
 * @return Object|boolean json object representing the actor profile if a new account is created, false otherwise 
 * TODO syncronized
 */
function createActorIfNotExists($username, $publickey){
	if (!file_exists('../lap_users'))
		mkdir('../lap_users', 0755, true);
	
	$file=fopen('../lap_users/'.$username.'.json','x');
	if ($file==false) return false;

	//26 is the lenght of lap_src/create-account.php
	//TODO move into a shared utilities file
	$baseURI=(empty($_SERVER['HTTPS']) ? 'http' : 'https').'://'.$_SERVER['SERVER_NAME'].(substr($_SERVER['REQUEST_URI'],0,strlen($_SERVER['REQUEST_URI'])-26));
		
	$actor=new stdClass();
	$actor->{"@context"}=array("https://www.w3.org/ns/activitystreams", "https://w3id.org/security/v1");
	$actor->id=$baseURI.'lap_users/'.$username.'.json';
	$actor->type='Person';
	$actor->preferredUsername=$username;	
	$actor->publicKey=new stdClass();
	$actor->publicKey->id=$baseURI.'lap_users/'.$username.'.json#main-key';
	$actor->publicKey->owner=$actor->id;
	$actor->publicKey->publicKeyPem=$publickey;
	$actor->inbox=$baseURI.'inbox.php?user='.$username;	
	//$actor->outbox=$baseURI.'outbox.php?user='.$username;
	
	$actorJSON=json_encode($actor, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	fwrite($file, $actorJSON);
	fflush($file);
	fclose($file);
		
	return $actor;
}
session_start();
if (!isset($_POST['newusername']) || !isset($_POST['publickey']) || !isset($_POST['captcha'])) die("wrong parameters");
$username=$_POST['newusername'];
$publickey=$_POST['publickey'];
if ($_SESSION['captcha'] != $_POST['captcha']) {
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
} else if (($actor=createActorIfNotExists($username, $publickey))==false){
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
	$createActivity=new stdClass();
	$createActivity->{'@context'}='https://www.w3.org/ns/activitystreams';
	$createActivity->type='create';
	$createActivity->actor=$actor->id;
	
?> 
	<div class="w3-card-4">
		<div class="w3-container w3-teal">
			<h2>Account <?=$username?> created</h2>
		</div>
		<div class="w3-container">
		<p>The corresponding actor is <a href="<?=$actor->id?>"><?=$actor->id?></a>.</p>
		<p>Notify all federated servers that this actor has been created.</p>
		<blockquote>
<?php 
print json_encode($actor, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?>
		</blockquote>
		<a href="index.php" class="w3-btn w3-teal">Back</a>
		</div>
	</div>
<?php 	
}
?>
</body>
</html>