<?php 
require_once 'conf.php';
$username=$_GET['username'];
if (!isset($username)){
	print 'No username provided';
	die();
}

$proposedActorURI=LAP_SRC_DIR_URI.'actor.php?username='.urlencode($username);
$proposedActivityURI=LAP_USERS_DIR_URI.$username.'/activity/'.time();
?>
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
	<h1>Just a Little Activity Pub Server - Send an Activity</h1>
	<div class="w3-card-4">
		<form action="send.php" method="post" class="w3-container" id="form">
			<input type="hidden" name="username" value="<?=$username?>" />
		<!--  
			<p>
				<label>Sender actor URI <input type="url" name="sender"
					class="w3-input w3-border" /></label>
			</p>
			<p>
				<label>Sender key URI <input type="url" name="senderKey"
					class="w3-input w3-border" /></label>
			</p>
		-->
			<p>
				<label>Target inbox <input type="url" name="inbox"
					class="w3-input w3-border" required placeholder="Enter here the URI of the inbox to send the activity to"/></label>
			</p>
			<p>
				<label>Activity <textarea name="activity" id="activity" class="w3-input w3-border"
						rows="20" required>
{
	"@context": "https://www.w3.org/ns/activitystreams",
	"id": "<?=$proposedActivityURI?>",
	"type": WRITEME,
	"actor": "<?=$proposedActorURI?>",
	"object": WRITEME
}
</textarea></label>
			</p>
			<p>
				<label>Your private key <textarea
						placeholder="Put your private key pem here" name="privateKey"
						class="w3-input w3-border" rows="10" id="privateKey"></textarea>
				</label>
			</p>
			<input type="hidden" name="date" id="date" />
			<input type="hidden" name="digest" id="digest" />
			<input type="hidden" name="signature" id="signature" />
 			<p>
				<input type="submit" name="sendActivity" value="Send"
					class="w3-btn w3-teal" />
				<a href="index.php#actor<?=$username?>" class="w3-btn w3-teal" >Back</a>				
			</p>
		</form>
	</div>
	<script>
const form=document.getElementById("form");
const te=new TextEncoder("UTF-8"); 

/**
 * Set date field with current time, in the format required by HTTPS message signature
 */
function setDateField(){
  const currentTime=new Date();
  form.date.value=currentTime.toUTCString();
  return form.date.value;
}

/**
 * Remove line breaks.
 *
 * we remove line breaks as LF may be changed to CRLF on submit
 * see https://stackoverflow.com/questions/69835705/formdata-textarea-puts-r-carriage-return-when-sent-with-post
 */ 
function removeLineBreaksFromActivity(){
  const message=form.activity.value.replace(/\r/g, '').replace(/\n/g, '');  
  form.activity.value=message;
  return message;
}

/**
 * Create a digest of the activity field and use it to set the value of the digest field
 *
 * @return a promise
 */
function createActivityDigestPromise(activity){
  const buff=te.encode(activity);
  return window.crypto.subtle.digest('SHA-256', buff).then(param => {
  	// https://stackoverflow.com/questions/9267899/arraybuffer-to-base64-encoded-string
    form.digest.value = "SHA-256="+btoa(String.fromCharCode(...new Uint8Array(param)));
    return form.digest.value;
  });
}

//see https://developer.mozilla.org/en-US/docs/Web/API/SubtleCrypto/importKey#pkcs_8_import
//TODO moved to rsa.js

function str2ab(str) {
  const buf = new ArrayBuffer(str.length);
  const bufView = new Uint8Array(buf);
  for (let i = 0, strLen = str.length; i < strLen; i++) {
    bufView[i] = str.charCodeAt(i);
  }
  return buf;
}

/*
Import a PEM encoded RSA private key, to use for RSA-PSS signing.
Takes a string containing the PEM encoded key, and returns a Promise
that will resolve to a CryptoKey representing the private key.

TODO moved to rsa.js
*/
function importPrivateKey(pem) {
  // fetch the part of the PEM string between header and footer
  const pemHeader = "-----BEGIN PRIVATE KEY-----";
  const pemFooter = "-----END PRIVATE KEY-----";
  const pemContents = pem.substring(
    pemHeader.length,
    pem.length - pemFooter.length
  );
  // base64 decode the string to get the binary data
  const binaryDerString = window.atob(pemContents);
  // convert from a binary string to an ArrayBuffer
  const binaryDer = str2ab(binaryDerString);

  return window.crypto.subtle.importKey(
    "pkcs8",
    binaryDer,
	   {
	     name: "RSASSA-PKCS1-v1_5",
	     hash: "SHA-256"
	   },
	false, //true,
    ["sign"]
  );
}

/**
 * Get a promise which will generate the signature for the signature header from date and digest field values
 * and set the signature field value
 */
function createSignPromise(privateKey, date, digest){
	const toBeSigned = "date: "+date+"\ndigest: "+digest;
	return window.crypto.subtle.sign(
		"RSASSA-PKCS1-v1_5",
	  	privateKey,
	  	te.encode(toBeSigned)).then((signature) => {
	  		form.signature.value=btoa(String.fromCharCode(...new Uint8Array(signature)));
	  	}) ;
  	
}

form.addEventListener('submit', (e) => {
	e.preventDefault();
	const date=setDateField();
	const activity=removeLineBreaksFromActivity();  
	Promise.all([createActivityDigestPromise(activity), importPrivateKey(form.privateKey.value.trim())]).then((values) => {
		const digest=values[0];
		const privateKey=values[1];
		return createSignPromise(privateKey, date, digest);
	}).then(()=>{form.submit();});
});	
	</script>
</body>
</html>
