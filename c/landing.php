<?php

/* Actions ie.) /landing/sell */
switch ($action) {
	default : landing(); break;
}

/* Load the top 6 notes the user is selling and has purchased */
function landing() {
	global $error;
	global $success;
	global $content;

	$content = get_include_contents(base() . 'v/landing.tpl.php');
}