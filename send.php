<?php
$sender=$_POST['sender'];
$inbox = $_POST['inbox'];
$inboxURIComponents = parse_url($inbox);

$inboxHost = $inboxURIComponents['host'];
$activity = $_POST['activity'];
$privatekey = trim($_POST['privateKey']);

$digest=$_POST['digest'];
$currentTimeStr = $_POST['date'];
$signatureReceivedBase64=$_POST['signature'];
$toBeSigned = "date: $currentTimeStr\ndigest: $digest";
$toBeSignedReceived=$_POST['toBeSigned'];
$signature='tobeinitialized';
openssl_sign($toBeSigned, $signature, $privatekey, OPENSSL_ALGO_SHA256);

$publicKey="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxspIEsiZvpeEepTF6vNl
UHuvJc2dO178DRu/Ug4d2pLF+NWW87CKJL8PKRMnZ4rxdbmyGjcgKWQ24+uRcnrr
SEj/4X23uT+LzRxccRllxerz0j5vr5z+2GLXFUA+Y4Gc36W1fL89B0Wexwxp14pr
1soy+YVWqVrjWR6liRmWzvMGeS9m1+FCPs4zuYk4Wy7n2rI45lRQgmeyYUcY0bMd
4UF9kKhJwX17+1/aKT89oATyzsjj5BUpzwvL6JcvY/lUqyMXCSsok9fVY/PW1RCS
2mLtriXnzQp5CYqIN0gK03c5593rjaL3vg3bZ1MiARrLQ3uvhQCvN8livBu4pfjj
bQIDAQAB
-----END PUBLIC KEY-----";
$verified = openssl_verify($toBeSigned, $signature, $publicKey, OPENSSL_ALGO_SHA256);
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
echo json_encode(json_decode($activity), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?></code></pre>
<h3>To be signed</h3>
<pre><?=$toBeSignedReceived?></pre>
<p>Is as expected?<?php echo strcmp($toBeSigned,$toBeSignedReceived)==0 ? 'yes':'no';?></p>
				<h3>Received signature</h3>
<pre class="w3-card-4"><code>Expected <?php echo base64_encode($signature); ?></code></pre>
<pre class="w3-card-4"><code>Actual <?php echo $signatureReceivedBase64;?></code></pre>
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
