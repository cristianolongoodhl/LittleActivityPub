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
	$file=fopen('users/'.$username.'.json','x');
	if ($file==false) return false;

	//18 is the lenght of create-account.php
	//TODO move into a shared utilities file
	$baseURI=(empty($_SERVER['HTTPS']) ? 'http' : 'https').'://'.$_SERVER['SERVER_NAME'].(substr($_SERVER['REQUEST_URI'],0,strlen($_SERVER['REQUEST_URI'])-18));
		
	$actor=new stdClass();
	$actor->{"@context"}=array("https://www.w3.org/ns/activitystreams", "https://w3id.org/security/v1");
	$actor->id=$baseURI.'users/'.$username.'.json';
	$actor->preferredUsername=$username;	
	$actor->key=new stdClass();
	$actor->key->id=$baseURI.'users/'.$username.'-key.json';
	$actor->key->owner=$actor->id;
	$actor->key->publicKeyPem=$publickey;
	$actor->inbox=$baseURI.'inbox.php?user='.$username;	
	$actor->outbox=$baseURI.'outbox.php?user='.$username;
	
	$actorJSON=json_encode($actor, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	fwrite($file, $actorJSON);
	fflush($file);
	fclose($file);
	
	//key is provided in its own file, so that it can be retrieved for HTTP Message Signature verification
// 	$actor->key->{"@context"}="https://w3id.org/security/v1";
// 	$keyJSON=json_encode($actor->key, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	
// 	//here we are assuming that such a key file not exists, as it refers to a new user
// 	$keyfile=fopen('users/'.$username.'-key.json','x');
// 	fwrite($keyfile, $keyJSON);
// 	fflush($keyfile);
// 	fclose($keyfile);
	
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