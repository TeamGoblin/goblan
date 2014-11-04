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

	/* Check if form was posted, if so, shove data into db */
	if (isset($_POST['name'])) {
		$fullname = strtolower(preg_replace("~[^a-zA-Z0-9\.\_\-\+\@ ]~", "", $_POST["name"]));
		$alias = strtolower(preg_replace("~[^a-zA-Z0-9\.\_\-\+\@ ]~", "", $_POST["alias"]));
		$email = strtolower(preg_replace("~[^a-zA-Z0-9\.\_\-\+\@]~", "", $_POST["email"]));

		$insert = array($fullname, $alias, $email);
		
		$q = <<<end
			INSERT INTO users
			VALUES ($1, $2, false, $3);
end;
		if ($result = pg_query_params($q, $insert)) {
			// E-mail me and the user
			$name = "GobLAN 2014"; //senders name
			$from = "jake@teamgoblin.com"; //senders e-mail adress
			$mail_body = "Hello,\nThanks for signing up to attend the GobLAN 2014 event.\n\nDon't forget to pay the $10 either in cash or via paypal to jake@teamgoblin.com\n\nThanks,\n//Jake";
			$subject = "GobLAN 2014 Registration"; //subject

			$eol = "\n";
			# Common Headers
			$headers = "From: $name <$from>".$eol;
			$headers .= "Reply-To: $name <$from>".$eol;
			$headers .= "Return-Path: $name <$from>".$eol;     // these two to set reply address
			$headers .= "Message-ID:<".time()." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
			$headers .= "X-Mailer: PHP v".phpversion().$eol;           // These two to help avoid spam-filters 

			$headers2 = "-f $from";

			mail($email, $subject, $mail_body, $headers, $headers2); //mail command :)

			$mail_body = "New GobLAN 2014 Registrant:\n\nName: $fullname\nAlias: $alias\nEmail: $email\n\nThanks";
			mail($from,$subject,$mail_body,$headers,$headers2);
			$success = "Thanks for registering, don't forget to pay by sending $10 to jake@teamgoblin.com via PayPal or in person with cash!";
		} else {
			$error = "Something went wrong, try again later.";
		}

	}

	$content = get_include_contents(base() . 'v/landing.tpl.php');
}