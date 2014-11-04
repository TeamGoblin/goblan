<?php
/* Actions ie.) /user/login */
switch ($action) {
	case "login" : login(); break;
	case "logout" : logout(); break;
	case "reset" : resetpw($tag); break;
	case "forgot" : forgot($tag); break;
	case "view" : view($tag); break;
	case "msg" : msg($tag); break;
	case "save" : save(); break;
	default : edit(); break;
}

/* Action to log user in */
function login() {
	global $user;
	global $error;
	global $success;
	$email = $_POST["email"];
	$pass = $_POST["password"];
	$keep = $_POST["keep"];

	$user->email = $email;
	$user->password = $pass;
	$user->keep = $keep;
    $user->access = $user->getAccess();
	$attempt = $user->login();
    $location = 'Location: /login';
	if ($attempt) {
        // check if it is admin
        if (in_array('admin', $user->access))
        {
            $location = 'Location: /admin_users';
        }
        else
        {
            $location = 'Location: /';
        }
	}
    header($location);
}

/* Action to log user out */
function logout() {
	global $user;
	global $error;
	global $success;
	$user->logout();
	unset($user);
	header('Location: /');
}

/* Action to create new user or edit user */
function edit() {
	global $user;
	global $content;
	$content = get_include_contents(base() . 'v/edit_user.tpl.php');
}

/* Action to actually save information from user registration or edit */
function save() {
	global $user;
	global $error;
	global $success;
	global $warning;
	global $info;
	global $content;
	$user->newpassword = false;

	// Clean'm
	$email = strtolower(preg_replace("~[^a-zA-Z0-9\.\_\-\+\@]~", "", $_POST["email"]));
	$alias = strtolower(preg_replace("~[^a-zA-Z0-9\_]~", "", $_POST["alias"]));
	$schoolID = $_POST['schoolID'];
	$newschool = strtolower(preg_replace("~[^a-zA-Z0-9\' \:\[\]]~", "", $_POST["newschool"]));
	$language = strtolower(preg_replace("~[^a-zA-Z]~", "", $_POST["language"]));
	$paypal = strtolower(preg_replace("~[^a-zA-Z0-9\.\_\-\+\@]~", "", $_POST["paypal"]));

	$pass = $_POST["password"];
	$id = preg_replace("~[^0-9]~", "", $_POST["id"]);
	$new = (isset($id) && $id != 0) ? false : true;

	// Verify'm
	if ($id != $user->id && !empty($id)) {
		header('Location: /');
	}

	// Save'm
	$user->alias = $alias;
	$user->email = $email;
	$user->language = $language;
	$user->paypal = $paypal;

	// Don't set school on edit page
	if (!empty($schoolID)){
		$user->school = $schoolID;
	}
	if (!empty($newschool)){
		$user->newschool = $newschool;
	}

	if (!empty($pass)) {
		$user->password = $pass;
		$user->newpassword = TRUE;
	}

	$success = $user->save();
	
	if (!$success) {
		if ($new) {
			if (strpos($user->error, "email_unique") !== false) { $user->error = "Email address is already in use."; }
			if (strpos($user->error, "alias_unique") !== false) { $user->error = "Alias is already in use."; }
			$error = $user->error;
			$user = null;
			$content = get_include_contents(base() . 'v/register.tpl.php');
		} else {
			if (strpos($user->error, "email_unique") !== false) { $user->error = "Email address is already in use."; }
			if (strpos($user->error, "alias_unique") !== false) { $user->error = "Alias is already in use."; }
			$error = $user->error;
			$content = get_include_contents(base() . 'v/edit_user.tpl.php');
		}
	} else {
		if ($new) {
            // send out an email both to jonte and jake
            $name = "Note Goblin"; //senders name
            $from = "help@notegoblin.com"; //senders e-mail adress
            $message = NEW_USER_REGISTRATION_MESSAGE;
            $subject = NEW_USER_REGISTRATION; //subject

            mailer($name, $from, implode(',', unserialize(NOTEGOBLIN_GROUP_EMAIL)), $subject, $message);
			$user->login();
			$success = $user->success;
			header('Location: /dash');
		} else {
			$success = $user->success;
			$content = get_include_contents(base() . 'v/edit_user.tpl.php');
		}
	}
}

/* Action to load forgot page or send forgot pw email */
function forgot($tag=null) {
	global $user;
	global $error;
	global $success;
	global $content;
	global $content_title;
	if ($tag == 'email') {
		if (!empty($_POST['email'])){
			$email = strtolower(preg_replace("~[^a-zA-Z0-9\.\_\-\+\@]~", "", $_POST["email"]));
			$key = $user->emailCheck($email);
			if (!$key) {
				$content = get_include_contents(base() . 'v/forgot.tpl.php');
				$error = 'Email fail.';
				$content_title = "Forgot";
				return;
			}

			$name = "Note Goblin"; //senders name
			$from = "help@notegoblin.com"; //senders e-mail adress
			$mail_body = "Hello,\nYou've requested a password reset.  As such, we've obliged.\nIn order to reset your password please visit http://notegoblin.com/user/reset/$key\n\nThanks and see you soon,\nNoteGoblin Staff"; //mail body
			$subject = "Note Goblin Password Reset Request"; //subject

			$eol = "\n";
			# Common Headers
			$headers = "From: $name <$from>".$eol;
			$headers .= "Reply-To: $name <$from>".$eol;
			$headers .= "Return-Path: $name <$from>".$eol;     // these two to set reply address
			$headers .= "Message-ID:<".time()." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
			$headers .= "X-Mailer: PHP v".phpversion().$eol;           // These two to help avoid spam-filters 

			$headers2 = "-f $from";

			mail($email, $subject, $mail_body, $headers, $headers2); //mail command :) 

			header('Location: /login/reset');
		} else {
			$content = get_include_contents(base() . 'v/forgot.tpl.php');
			$error = 'Email fail.';
		}
	} else {
		$content = get_include_contents(base() . 'v/forgot.tpl.php');
	}
	$content_title = "Forgot";
}

/* Action to show new password box or actually reset the password */
function resetpw($tag=null) {
	global $user;
	global $error;
	global $success;
	global $content;
	global $content_title;
	if (empty($tag)) {
		header('Location: /');
	}
	if ($tag == "pass") {
		$password = $_POST["pass"];
		$id = $_POST["id"];
		$key = $_POST["key"];
		$user->password = $password;
		$user->id = $id;
		$user->key = $key;
		if ($user->savepw()) {
			$success = $user->success;
			$user->login();
			$content = get_include_contents(base() . 'v/dash.tpl.php');
		} else {
			$error = $user->error;
		}
		
	} else {
		$id = $user->keyCheck($tag);
		if (!$id) {
			header('Location: /');
		}
		$user->id = $id;
		$user->key = $tag;
		$content = get_include_contents(base() . 'v/resetpw.tpl.php');
		$content_title = "Reset";	
	}
}


function view($tag=null) {
	global $user;
	global $c;
	global $error;
	global $success;
	global $content;
	global $content_title;
	if (empty($tag)) {
		// load current user tag, if no current user just go back to main page
	} else {
		$q = <<<end
			SELECT alias, email, created, modified
			FROM users
			WHERE id = $1
end;
		$result = pg_query_params($q, array($tag));
	}

	$c['user'] = pg_fetch_object($result);
	if ($c['user']) {			
		$content = get_include_contents(base() . 'v/user.tpl.php');
		$content_title = $c['user']->alias;
	} else {
		header('Location: /');
	}
}
