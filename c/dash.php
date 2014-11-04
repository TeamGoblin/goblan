<?php

/* Actions ie.) /dash/sell */
switch ($action) {
	case "selling" : selling(); break;
	case "purchased" : purchased(); break;
	default : dashboard(); break;
}

/* Load all of the notes that the user is selling */
function selling() {
	global $user;
	global $error;
	global $success;
	global $content;
	global $notes;
	$notes = DB::get_selling_notes((int)$user->id);
	$content = get_include_contents(base() . 'v/selling.tpl.php');
}

/* Load all of the notes that the user has purchased */
function purchased() {
	global $user;
	global $error;
	global $success;
	global $content;
	global $notes;
	$notes = DB::get_purchased_notes((int)$user->id);
	$content = get_include_contents(base() . 'v/purchased.tpl.php');
}

/* Load the top 6 notes the user is selling and has purchased */
function dashboard() {
	global $user;
	global $error;
	global $success;
	global $content;
	global $notes;
	$notes = array();
	$notes[] = DB::get_selling_notes((int)$user->id, 6);
	$notes[] = DB::get_purchased_notes((int)$user->id, 6);
	$content = get_include_contents(base() . 'v/dash.tpl.php');
}