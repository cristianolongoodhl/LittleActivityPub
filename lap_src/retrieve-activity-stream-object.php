<?php
if (!isset($_POST['objecturl'])) die('invalid operation, missing object URL');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $_POST['objecturl']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/ld+json; profile="https://www.w3.org/ns/activitystreams'));

$responseHeaders = '';
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
	$len = strlen($header);
	$headerSplit = explode(':', $header, 2);
	if (count($headerSplit) < 2) // ignore invalid headers
	return $len;

	$responseHeaders .= $header;
	return $len;
});

//curl_setopt($ch, CURLOPT_VERBOSE, true);
$result = curl_exec($ch);

$requestInfo = curl_getinfo($ch, CURLINFO_HEADER_OUT); // request headers
$responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
if ($result === false) {
	$responseBody = 'Request failed: ' . curl_error($ch);
} else {
	$responseBodyAsJSON = json_decode($result, false, 100);
	if ($responseBodyAsJSON !== null) 
		$responseBody = json_encode($responseBodyAsJSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
curl_close($ch);
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
	<h1>Just a Little Activity Pub Server - Retrieve an ActivityStream
		object</h1>
	<div class="w3-card-4">
		<div class="w3-container w3-teal">
			<h2>Request</h2>
		</div>
		<div class="w3-container">
			<pre>
				<samp>
<?=$requestInfo?></samp>
			</pre>
		</div>
	</div>
	<div class="w3-card-4">
		<div class="w3-container w3-teal">
			<h2>Response</h2>
		</div>
		<div class="w3-container">
			<h2>HTTP code</h2>
			<pre>
				<samp>
<?=$responseCode?></samp>
			</pre>
			<h2>Body</h2>
			<pre>
<samp>
<?=$responseBody?></samp>
			</pre>
			<h2>Headers</h2>
			<pre>
				<samp>
<?=$responseHeaders?></samp>
			</pre>
		</div>
	</div>

	<p>
		<a href="index.php#retrieve-activity-stream-object"
			class="w3-btn w3-teal ">Back</a>
	</p>
</body>