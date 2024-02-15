<?php
require_once 'conf.php';

//create the users directory if not exists
if (!is_dir(LAP_USERS_DIR_PATH))
	if (!mkdir(LAP_USERS_DIR_PATH)){
		print 'Unable to create directory '.LAP_USERS_DIR_PATH;
}

/**
 * Get all usernames of existing accounts 
 * @return string[] the array of all usernames, if any. False, otherwise.
 */
function getAllUserNames()
{
	$files = scandir(LAP_USERS_DIR_PATH);
	$n = 0;
	foreach ($files as $file)
		if ($file!=='.' && $file!=='..') 
			$usernames[$n++] = $file;
	return $n == 0 ? false : $usernames;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<title>Little Activity Pub Server</title>
<meta charset="UTF-8" />
<link rel="stylesheet" type="text/css"
	href="https://www.w3schools.com/w3css/4/w3.css" />
<link id="style" rel="stylesheet" type="text/css" href="lap.css" />
<script>

function handleRSAKeyPairGeneration(){
	createKeyPair().then((keyPair) => { 
		return Promise.all([convertPriKToPem(keyPair).then((priKeyAsPEM)=>{window.alert("Your private key, save it and keep it safe.\n\n"+priKeyAsPEM);}),
			convertPubKToPem(keyPair).then((pubKeyAsPEM)=>{
					document.getElementById("publicKeyPem").value=pubKeyAsPEM;
				})
			]);
		});
}

/**
 * Just generate an RSA key pair
 * @return a promise
 */
function createKeyPair(){
	return window.crypto.subtle.generateKey(
		{
		    name: "RSASSA-PKCS1-v1_5",
		    modulusLength: 2048,
		    publicExponent: new Uint8Array([0x01, 0x00, 0x01]),
		    hash: "SHA-256"
		  },
		  true,
		  ["sign", "verify"]
	);
}


/**
 * Turn the private key in the given key pair into the PEM format
 * see https://github.com/mdn/dom-examples/blob/main/web-crypto/export-key/pkcs8.js
 * 
 * @param keyPair CryptoKeyPair
 * @return a promise which will resolve with a string representing the PEM encoded private key
 */
function convertPriKToPem(keyPair){
	return window.crypto.subtle.exportKey(
      "pkcs8",
      keyPair.privateKey
    ).then((exported) => {
    	const exportedAsString = String.fromCharCode.apply(null, new Uint8Array(exported)); //array buffer to string
	    const exportedAsBase64 = window.btoa(exportedAsString);
    	return `-----BEGIN PRIVATE KEY-----\n${exportedAsBase64}\n-----END PRIVATE KEY-----`;    	
    });
}

/**
 * Turn the public key in the given key pair into the PEM format
 * see https://github.com/mdn/dom-examples/blob/main/web-crypto/export-key/pkcs8.js
 * 
 * @param keyPair CryptoKeyPair
 * @return a promise which will resolve with a string representing the PEM encoded private key
 */
function convertPubKToPem(keyPair){
	return window.crypto.subtle.exportKey(
      "spki",
      keyPair.publicKey
    ).then((exported) => {
    	const exportedAsString = String.fromCharCode.apply(null, new Uint8Array(exported)); //array buffer to string
	    const exportedAsBase64 = window.btoa(exportedAsString);
    	return `-----BEGIN PUBLIC KEY-----\n${exportedAsBase64}\n-----END PUBLIC KEY-----`;    	
    });
}

</script>

</head>
<body>
	<h1>Just a Little Activity Pub Server</h1>
	<div class="w3-container w3-card-4">
		<p>
			This server is a partial implementation of the server part of
			the <a href="https://www.w3.org/TR/activitypub/">ActivityPub</a>
			protocol, intended for didactic purposes.
		</p>

		<p>
			LittleActivityPub is a free-software project. Source codes are
			available in the <a
				href="https://github.com/cristianolongoodhl/LittleActivityPub">LittleActivityPub
				repository on github</a>. So, please, contribute or just use and
			reuse it.
		</p>
	</div>

	<div class="w3-card-4">
		<div class="w3-container w3-teal">
			<h2 id="retrieve-activity-stream-object">Retrieve ActivityStream object</h2>
		</div>
		<div class="w3-container" id="retrieve-activity-stream-object">
		<p>Retrieve from the internet by URI an object in the ActivityStream format (media type <code>application/ld+json; profile="https://www.w3.org/ns/activitystreams</code>). 
		<form action="retrieve-activity-stream-object.php" method="POST">
				<p>
					<label for="objecturl">URI</label>
					<input type="url" name="objecturl" class="w3-input w3-border" placeholder="https://mastodon.bida.im/users/aaronwinstonsmith" required/>
				</p>
				<p>
					<input type="submit" name="retrieve" value="Retrieve" class="w3-btn w3-teal " />
				</p>
		</form>
		
		</div>
	</div>
	<div class="w3-card-4">
		<div class="w3-container w3-teal">
			<h2>Create an Actor</h2>
		</div>
		<div class="w3-container">
			<p>Feel free to create a new account to start experimenting with the
				ActivityPub protocol.</p>
			<form action="create-account-captcha.php" method="POST">

				<p>
					<label for="newusername">Enter the <em>username</em> for your <a
						href="https://www.w3.org/TR/activitypub/#actor-objects">Actor</a></label>
					<input type="text" name="newusername" class="w3-input w3-border" required />
				</p>
				<p>
					<label for="publickey">Enter the RSA <em>public key</em> (as PEM)
						associated with the new account
					</label>
				</p>
				<div class="w3-card-4">
					<p>An RSA key pair can be generated, for example, with the
						following commands on a linux shell:</p>
					<pre>
<code> openssl genrsa -out private.pem 2048</code>
<code> openssl rsa -in private.pem -outform PEM -pubout -out public.pem</code>
						</pre>
					<p>
						The public and private keys, encoded in PEM format, will be on the
						files
						<code>private.pem</code>
						and
						<code>public.pem</code>
						,respectively.
					</p>
					<p>Alternatively, you can let your browser generating a novel key pair for you. <em>Don't forget to store safely on your PC your private key</em>, it will be essential!</p>
					<p><button type="button" id="create-key-pair" onclick="handleRSAKeyPairGeneration();" class="w3-btn w3-teal">Create key pair</button></p>
				</div>
				<p>
					<textarea placeholder="Put your public key pem here" id="publicKeyPem"
						name="publickey" class="w3-input w3-border" rows="10" required></textarea>
				</p>
				<p>
					<strong>About cookies</strong> - starting the account creation you
					will access site pages which use session cookies for captcha. <input
						type="submit" name="createAccount" value="Create Account"
						class="w3-btn w3-teal " />
				</p>
			</form>
		</div>
	</div>

<?php
$usernames = getAllUserNames();
if ($usernames) {
	?>
	<div class="w3-card-4">
		<div class="w3-container w3-teal">
			<h2>Accounts</h2>
		</div>
		<div class="w3-container">
<?php
	
foreach ($usernames as $username){
	print '<h3 id="actor'.$username.'">'.$username . '@'.$_SERVER['SERVER_NAME'].'</h3>';
	print '<ul>';
	$actor=LAP_USERS_DIR_URI.$username."/actor.json";
	print('<li>Actor <a target="_blank" href="'.$actor.'">'.$actor.'</a></li>');
	$inbox=LAP_SRC_DIR_URI.'inbox.php?username='.$username;	
	print('<li>Inbox provided by this server <!-- (may differ from those provided in the actor description)--> <a target="_blank" href="'.$inbox.'">'.$inbox.'</a></li>');
	print('<li>Send an activity <a href="sendActivityForm.php?username='.$username.'" class="w3-btn w3-teal" title="send an activity to a specified target">to target</a>'.
		' <a href="sendActivityFormFullJS.php?username='.$username.'" class="w3-btn w3-teal" title="send an activity to all the actors in its audience">to audience</a></li>'); 
	print '</ul>';}
	?>		
		</div>
	</div>
<?php
}
?>
</body>
</html>
