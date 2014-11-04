<?php
standard();

/* Action to load standard default interface for logging in or registering */
function standard() {
	global $user;
	global $error;
	global $success;
	global $content;
	global $action;
	global $schools;
	$schools = DB::get_schools();
	$content = get_include_contents(base() . 'v/register.tpl.php');
}