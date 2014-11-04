<?php

/* Actions ie.) /landing/sell */
switch ($action) {
	default : c_landing(); break;
}

/* Load the top 6 notes the user is selling and has purchased */
function c_landing() {
	global $error;
	global $success;
	global $content;

	$content = get_include_contents(base() . 'v/landing.tpl.php');
}