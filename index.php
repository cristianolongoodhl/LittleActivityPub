<?php

function ensureUsersDirectoryExists()
{
	if (!file_exists('users')) {
		mkdir('users', 0755, true);
	}
}

/**
 * Get all usernames of existing accounts 
 * 
 * @return string[] the array of all usernames, if any. False, otherwise.
 */
function getAllUserNames()
{
	$usernames = array();
	$files = scandir('users');
	$n = 0;
	foreach ($files as $file)
		if (str_ends_with($file, '.json')) 
		//remove the json suffix
		$usernames[$n++] = substr($file, 0, strlen($file) - 5);
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
</head>
<body>
	<h1>Just a Little Activity Pub Server</h1>
	<div class="w3-container w3-card-4">
		<p>
			This is a minimal and incomplete implementation of the server part of
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
			<h2>Create a new account</h2>
		</div>
		<div class="w3-container">
			<p>Feel free to create a new account to start experimenting with the
				ActivityPub protocol.</p>
			<form action="create-account-captcha.php" method="POST">

				<p>
					<label for="newusername">Enter the <em>username</em> for your <a
						href="https://www.w3.org/TR/activitypub/#actor-objects">Actor</a></label>
					<input type="text" name="newusername" class="w3-input w3-border" />
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
				</div>
				<p>
					<textarea placeholder="Put your public key pem here"
						name="publickey" class="w3-input w3-border" rows="10"></textarea>
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
ensureUsersDirectoryExists();
$usernames = getAllUserNames();
if ($usernames) {
	?>
	<div class="w3-card-4">
		<div class="w3-container w3-teal">
			<h2>Accounts</h2>
		</div>
		<div class="w3-container">
			<ul>
<?php
	foreach ($usernames as $username)
		print('<li>' . $username . '</li>');
	?>		
		</ul>
		</div>
	</div>
<?php
}
?>

</body>
</html>
