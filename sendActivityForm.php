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
		<form action="send.php" method="post" class="w3-container">
			<p>
				<label>Sender actor URI <input type="url" name="sender" class="w3-input w3-border" /></label>
			</p>
			<p>
				<label>Inbox <input type="url" name="inbox" class="w3-input w3-border" /></label>
			</p>
			<p>
				<label>Activity <textarea name="activity" class="w3-input w3-border" rows="20"></textarea></label>
			</p>
			<p>
				<label>Your private key <textarea
						placeholder="Put your private key pem here" name="privatekey"
						class="w3-input w3-border" rows="10"></textarea>
				</label>
			</p>
			<p>
				<input type="submit" name="createAccount" value="Send"
					class="w3-btn w3-teal " />
			</p>
		</form>
	</div>
</body>
</html>
