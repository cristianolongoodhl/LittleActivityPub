<?php
$sender=$_POST['sender'];
$inbox = $_POST['inbox'];
$inboxURIComponents = parse_url($inbox);

$inboxHost = $inboxURIComponents['host'];
$activity = $_POST['activity'];
$privatekey = trim($_POST['privatekey']);

$digest=$_POST['digest'];
$currentTimeStr = $_POST['date'];
$toBeSigned = "date: $currentTimeStr
digest: $digest";
$signature='tobeinitialized';
openssl_sign($toBeSigned, $signature, $privatekey, OPENSSL_ALGO_SHA256);

$sigHeader = 'keyId="'.$sender.'#main-key",algorithm="rsa-sha256",headers="date digest",signature="' . base64_encode($signature) . '"';
$headers = ['Host: ' . $inboxHost, 'Date: ' . $currentTimeStr, 'Digest: ' . $digest, 'Signature: ' . $sigHeader, 'Content-Type: application/activity+json'];

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
			<div class="w3-container w3-teal">
				<h2>Request details</h2>
			</div>
			<div class="w3-container">
				<h3>Activity</h3>
<pre class="w3-card-4"><code><?php 
echo json_encode(json_decode($activity), JSON_PRETTY_PRINT);
?></code></pre>
				<h3>Signature header string</h3>
<pre class="w3-card-4"><code><?=$toBeSigned?></code></pre>
				<h3>Headers</h3>
				<ul class="w3-card-4">
<?php 
foreach($headers as $value)
	print '<li>'.$value.'</li>';
?>				
				</ul>
			</div>
	</div>
	<div class="w3-card-4">
			<div class="w3-container w3-teal">
				<h2>Response details</h2>
			</div>
			<div class="w3-container">
<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $inbox);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $activity);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$result = curl_exec($ch);
$responseInfo = curl_getInfo($ch);
echo '<p>HTTP Response code <var>' . $responseInfo["http_code"] . '</var></p>';
if ($result === false)
	echo '<p>Curl error: ' . curl_error($ch) . '</p>';
else
	echo "<p>Curl result <sample>$result</sample></p>";
?>
			</div>
	</div>
</body>
</html>
