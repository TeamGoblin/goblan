<?php

switch ($action) {
	case "submit" : submit(); break;
	default : contact(); break;
}

/* Action to load contact */
function contact() {
	global $user;
	global $error;
	global $success;
	global $content;
	global $action;
	$content = get_include_contents(base() . 'v/contact.tpl.php');
}

/* Submit contact form */
function submit() {
	global $user;
	global $error;
	global $success;
	global $content;
	global $action;

	if (!empty($_POST['email'])){
		$contactemail = strtolower(preg_replace("~[^a-zA-Z0-9\.\_\-\+\@]~", "", $_POST["email"]));
		$body = $_POST['body'];
		$email = 'jake@teamgoblin.com, help@notegoblin.com';
		if (!$contactemail) {
			$content = get_include_contents(base() . 'v/contact.tpl.php');
			$error = 'Email fail.';
			return;
		}

		$name = "Note Goblin"; //senders name
		$from = "help@notegoblin.com"; //senders e-mail adress
		$mail_body = "From: $contactemail\n\nMessage: $body"; //mail body
		$subject = "Note Goblin Contact Form"; //subject

		$eol = "\n";
		# Common Headers
		$headers = "From: $name <$from>".$eol;
		$headers .= "Reply-To: $name <$from>".$eol;
		$headers .= "Return-Path: $name <$from>".$eol;     // these two to set reply address
		$headers .= "Message-ID:<".time()." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
		$headers .= "X-Mailer: PHP v".phpversion().$eol;           // These two to help avoid spam-filters 

		$headers2 = "-f $from";

		mail($email, $subject, $mail_body, $headers, $headers2); //mail command :) 
		$success = 'Thanks for contacting us, we will get back to you soon!';
	} else {
		$error = 'Email fail.';
	}
	$content = get_include_contents(base() . 'v/contact.tpl.php');
}