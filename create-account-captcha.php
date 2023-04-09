<?php
if (!isset($_POST['newusername'])) die('Username field missing');
$newusername = $_POST['newusername'];
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

	<form method="POST" action="create-account.php" >
		<div class="w3-card-4">
			<div class="w3-container w3-teal">
				<p>Please show me that you are not a robot.</p>
			</div>
			<fieldset class="w3-container">
				<input type="hidden" name="message" value="prova" />
				<p>
					<label for="captcha">Insert the text contained in the
						following image</label> <img src="captcha.php" /> <input
						type="text" class="w3-border" name="captcha" size="5"
						maxlength="5" required /> <input type="hidden" name="newusername"
						value="<?=$newusername?>" /> <input type="submit"
						name="createAccount" value="Create Account"
						class="w3-btn w3-teal " />
				</p>
			</fieldset>
		</div>
	</form>
</body>
</html>

