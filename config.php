<?php
include_once('db_settings.php');
date_default_timezone_set('UTC');

/* Connect to database */
$db = pg_connect("host=".$host." dbname=".$database." user=".$name." password=".$pass." connect_timeout=5") or die('501 Database Error');
define('FROM_SUPPORT_TEAMGOBLIN_HEADER', 'From: support@teamgoblin.com');
define('NEW_USER_REGISTRATION_MESSAGE', 'A new user has recently signed up on NoteGoblin.com!');
define('NEW_USER_REGISTRATION', 'New User Registration');
define('NOTEGOBLIN_GROUP_EMAIL', serialize(array('jonte@teamgoblin.com', 'jake@teamgoblin.com')));

/* Globals available for any file */
$user = NULL; // used to store information about the current user
$content = NULL; // used to store the main content output
$error = NULL; // used to store errors to display at the top of the page
$success = NULL; // used to store success to display at the top of the page
$warning = NULL; // used to store warnings to display at the top of the page
$info = NULL; // used to store information to display at the top of the page
$c = array(); // used to store variables in the controller to pass to the view
$action = ''; // used to store controller action
$type = ''; // used to store controller type if admin page
$tag = ''; // used to store controller tag id
$page = ''; // used to store controller type if normal page

/* Set base */
chdir(__DIR__);

if ( ! defined('DS')) {	define('DS', DIRECTORY_SEPARATOR); }

/* Set base pass for including files */
function base() { return __DIR__.DS; }

/* Load helper files */
include_once(base() . 'i/code/helpers.php'); // helpers
include_once(base() . 'i/code/hash.php'); // bcrypt
include_once(base() . 'i/code/curl.php'); // cURL handler

/* Load models */
include_once(base() . 'm/user.php'); // User model
include_once(base() . 'm/db.php'); // DB model
include_once(base() . 'm/transaction.php'); // Transaction model

$urls = explode('/',$_SERVER['REQUEST_URI']);
foreach ($urls as $key => $url) {
	$q = stripos($url, '?');
	if ($q !== False) {
		$urls[$key] = substr($url, 0, $q);
	}
}

/* Get page action, type and tag */
if (!empty($urls[1])){
	if ($urls[1] == 'admin') {
		if (!empty($urls[2])){
			$type = $urls[2];
		}
		if (!empty($urls[3])){
			$action = $urls[3];
		}
		if (!empty($urls[4])){
			$tag = $urls[4];
		}
	} else { // Not an admin page so there is no type
		if (!empty($urls[2])){
			$action = $urls[2];
		}
		if (!empty($urls[3])){
			$tag = $urls[3];
		}
	}
}

/* Load Controller */
if (!empty($urls[1])) {
	$page = $urls[1];

	if (file_exists(base() . 'c/' . $page . '.php')){
		include_once(base() . 'c/' . $page . '.php');
	} else {
		// Doesn't exist, load default
		include_once(base() . landing());
	}

} else {
	// No controller set, load default
	include_once(base() . landing());
}

/* load landing page controller */
function landing() { 
	return 'c/landing.php';
}

/* private */
function mailer($name, $from, $to, $subject, $message)
{
    $eol = "\n";
    # Common Headers
    $headers = "From: $name <$from>".$eol;
    $headers .= "Reply-To: $name <$from>".$eol;
    $headers .= "Return-Path: $name <$from>".$eol;     // these two to set reply address
    $headers .= "Message-ID:<".time()." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
    $headers .= "X-Mailer: PHP v".phpversion().$eol;           // These two to help avoid spam-filters

    $headers2 = "-f $from";

    return mail($to, $subject, $message, $headers, $headers2); //mail command :)
}