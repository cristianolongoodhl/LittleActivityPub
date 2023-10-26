<?php
require_once 'conf.php';

$username=$_GET['username'];
if (!isset($username)){
	print 'No username specified';
	exit;
}

header('Access-Control-Allow-Origin: *');

/**
 * Store an activity into the actor inbox
 * @param stdClass $activity
 */
function saveToInbox($username, $activity)
{
	$file = new SplFileObject(LAP_USERS_DIR_PATH.$username.'/inbox.json', 'r+');
	$file->flock(LOCK_EX);

	$inboxFile = $file->fread($file->getSize());
	$inbox = json_decode($inboxFile, false);
	$inbox->orderedItems[$inbox->totalItems++] = $activity;
	$inboxFileUpdated = json_encode($inbox, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	$file->rewind();
	$file->fwrite($inboxFileUpdated);
	$file->fflush();
}

/**
 * Inbox receiving Activity Pub activities.
 */
switch ($_SERVER['REQUEST_METHOD']){
	case 'POST': 	
		$activity = json_decode(file_get_contents("php://input"), false);
		if ($activity === null) {
			http_response_code(400);
			print 'Invalid format for the incoming message: not a JSON';
			exit();
		}
		saveToInbox($username, $activity);
		break;
	default: //return the inbox 
		header("Content-Type: application/activity+json");
		$s=file_get_contents(LAP_USERS_DIR_PATH.$username.'/inbox.json');
		print $s;
}
