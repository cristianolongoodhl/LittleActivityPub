<?php 
require_once 'conf.php';

$username=isset($_GET['username']) ? $_GET['username'] : 'username' ;
$proposedActorURI=LAP_USERS_DIR_URI.$username.'/actor.json';
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
	<script src="client-to-server.js"></script>
	<script>
/**
 * Handle the click event for the sendActivityButton
 */
function handleSendActivityRequest(button){
	button.setAttribute("disabled","true");
	const reqSection = document.getElementById("request");
	request.removeAttribute("hidden");
	const form=document.getElementById("form");
    
	const date=(new Date()).toUTCString();
	document.getElementById("dateField").innerHTML=date;
	
	const activity=removeLineBreaksFromActivity();  
	var digest;
	Promise.all([digestPromise(activity), importPrivateKeyPromise(form.privateKey.value.trim())]).then((values) => {
			digest=values[0];
			const privateKey=values[1];
			document.getElementById("digestField").innerHTML=digest;
			return signatureHeaderPromise(privateKey, '<?=$proposedActorURI?>', date, digest);
		}).then((signature)=>{
			document.getElementById("signatureField").innerHTML=signature;
			return postActivityPromise(activity, date, digest, signature, "outbox.php");
		}).then((response)=>{
			button.removeAttribute("disabled");
			request.focus();
			document.getElementById("response").removeAttribute("hidden");
			document.getElementById("responseCode").innerHTML=response.status;
			return response.text();
		}).then((body)=>{
			document.getElementById("responseBody").innerHTML=body;
		});
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
 * Create a promise which will show the response to the user.
 */
function printResponsePromise(response){
}
	</script>
</head>
<body>
	<h1>Just a Little Activity Pub Server - Send an Activity</h1>
	<div class="w3-card-4">
		<form action="send.php" method="post" class="w3-container" id="form">
			<input type="hidden" name="username" value="<?=$username?>" />
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
 			<p>
				<input type="button" name="sendActivity" value="Send"
					class="w3-btn w3-teal" onclick="handleSendActivityRequest(this)" />
				<a href="index.php#actor<?=$username?>" class="w3-btn w3-teal" >Back</a>				
			</p>
		</form>
	</div>
	
		<div class="w3-card-4" id="request" hidden="true">
			<div class="w3-container w3-teal">
				<h2>Request headers</h2>
			</div>
			<ul>
				<li><em>Date</em> <span id="dateField"></span></li>
				<li><em>Digest</em> <span id="digestField"></span></li>
				<li><em>Signature</em> <span id="signatureField"></span></li>
			</ul>
		</div>
		<div class="w3-card-4" id="response" hidden="true">
			<div class="w3-container w3-teal">
				<h2>Response</h2>
			</div>
			<div class="w3-container">
				<p>Status code <span id="responseCode"></span></p>
				<h3>Body</h3>
				<pre id="responseBody"></pre>
			</div>
		</div>
		
</body>
</html>
