<?php
$username=$_GET['username'];
if (!isset($username)){
	print 'No username specified';
	exit;
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
	<h1>Actor <?=$username?></h1>
	<div class="w3-container w3-card-4">
		<p>
		TODO
		</p>
		<p><a href="index.php" class="w3-btn w3-teal">Back</a> </p>
	</div>
</body>
</html>
