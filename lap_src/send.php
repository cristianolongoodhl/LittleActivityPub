<?php

$username=$_POST['username'];
$activityAsStr=$_POST['activity'];
$activityAsJSON=json_decode($activityAsStr, false);

if (!(isset($activityAsJSON->actor))){
	print "Actor missing";
	exit();
}

$sender=$activityAsJSON->actor;
$actor=json_decode(file_get_contents($sender), false);
if (!(isset($activityAsJSON->actor))){
	print 'Unable to retrieve sender actor '.$sender;
	exit();
}
//$senderKey=$_POST['senderKey'];
$senderKey=$actor->{"publicKey"}->{"id"};
if (!(isset($senderKey))){
	print 'Unable to retrieve sender actor key id '.$sender;
	exit();
}

$inbox = $_POST['inbox'];
$inboxURIComponents = parse_url($inbox);

$inboxHost = $inboxURIComponents['host'];

$digest=$_POST['digest'];
$currentTimeStr = $_POST['date'];
$signatureReceivedBase64=$_POST['signature'];
$toBeSigned = "date: $currentTimeStr\ndigest: $digest";

$verified = openssl_verify($toBeSigned, base64_decode($signatureReceivedBase64), $actor->publicKey->publicKeyPem, OPENSSL_ALGO_SHA256);
$sigHeader = 'keyId="'.$senderKey.'",algorithm="rsa-sha256",headers="date digest",signature="' . $signatureReceivedBase64 . '"';
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
echo json_encode(json_decode($activityAsStr), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?></code></pre>
<h3>To be signed</h3>
<pre><?=$toBeSigned?></pre>
				<h3>Signature</h3>
<pre class="w3-card-4"><code><?=$signatureReceivedBase64?></code></pre>
<pre class="w3-card-4"><code>Verified <?php if ($verified) echo 'Yes'; else echo 'No';?></code></pre>
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
curl_setopt($ch, CURLOPT_POSTFIELDS, $activityAsStr);
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
		<p><a class="w3-btn w3-teal" href="index.php#actor<?=$username?>">Back</a></p>	
	</div>
</body>
</html>
