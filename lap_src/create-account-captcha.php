<?php
if (!isset($_POST['newusername']) || !isset($_POST['publickey'])) die('Some field missing');
$newusername = $_POST['newusername'];
$publickey = $_POST['publickey'];
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
	<h1>Just a Little Activity Pub Server - Create a New Account</h1>

	<form id="form" method="POST" action="create-account.php" >
		<div class="w3-card-4">
			<div class="w3-container w3-teal">
				<p>Please show me that you are not a robot.</p>
			</div>
			<fieldset class="w3-container">
				<input type="hidden" name="newusername" value="<?=$newusername?>" /> 
				<input type="hidden" name="publickey" value="<?=$publickey?>" />
				<input type="hidden" name="signature" value="nosignatureprovided" /> 
				<p>
					<label for="captcha">Insert the text contained in the
						following image</label> <img src="captcha.php" /> 
					<input
						type="text" class="w3-border" name="captcha" size="5"
						maxlength="5" required /> 
				</p>
				<p>
					<label>Enter your private key to test whether it corresponds to the public one. Note that this key will not be sent outside your browser.<textarea
							placeholder="Put your private key pem here" name="privateKey"
							class="w3-input w3-border" rows="10" id="privateKey" required></textarea>
					</label>
				</p>
	 			<p>
					<input type="submit" name="createAccount" value="Create Account"
						class="w3-btn w3-teal" />
					<a href="index.php" class="w3-btn w3-teal" >Cancel</a>				
				</p>				
			</fieldset>
		</div>
	</form>
	<script type="text/javascript">
<?php 
require_once 'rsa.js';
?>
const form=document.getElementById("form");
form.addEventListener('submit', (e) => {
	e.preventDefault();
	importPrivateKey(form.privateKey.value.trim())
		.then((privateKey)=>{return sign(privateKey, form.captcha.value);})
		.then((signature)=>{form.signature.value=signature; form.submit();});
});			
	</script>
</body>
</html>

