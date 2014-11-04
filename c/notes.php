<?php
/* Actions ie.) /notes/sell */
switch ($action) {
	case "sell" : sell(); break;
	case "buy" : buy($tag); break;
	case "view" : view($tag); break;
	case "save" : save($tag); break;
	case "delete" : remove($tag); break;
	case "deleteConfirm" : deleteConfirm($tag); break;
	case "purchase" : purchase($tag); break;
	case "download" : download($tag); break;
	case "edit" : edit($tag); break;
	case "user" : user($tag); break;
	case "ipnListener" : ipnListener($tag); break;
	case "rate" : rate(); break;
	default : view($tag); break;
}

function sell(){
	global $user;
	global $error;
	global $success;
	global $content;
	global $professors;
	global $courses;

	if (empty($user->paypal)) {
		$error = "You need to set your paypal account email address on your settings page before you can sell notes.";
		$content = get_include_contents(base() . 'v/dash.tpl.php');
		return;
	}

	$courses = array();
	$professors = array();

	foreach ($user->schools as $school) {
		$courses[] = DB::get_courses();
		$professors[] = DB::get_professors($school->id);
	}

	$content = get_include_contents(base() . 'v/sell_notes.tpl.php');
}

function buy(){
	global $user;
	global $error;
	global $success;
	global $content;
	global $notes;
	global $courses;
	global $professors;
	global $years;
	global $terms;
	global $schools;
	$courses = array();
	$professors = array();
	$years = array();
	$terms = array();
	$schools = array();

	$courses[] = DB::get_courses();	
	$professors[] = DB::get_professors();
	$years[] = DB::get_years();	
	$terms[] = DB::get_terms();
	$schools[] = DB::get_schools();

	// for now users can only have 1 school
	$courses = $courses[0];
	$professors = $professors[0];
	$years = $years[0];
	$terms = $terms[0];
    $schools = $schools[0];

	$notes = array();
	$notes[] = DB::search_notes();

	$content = get_include_contents(base() . 'v/buy_notes.tpl.php');

}

function user($id){
	global $user;
	global $error;
	global $success;
	global $content;
	global $notes;
	global $author;

	$notes = array();

	/* Get notes for the user id given */
	$notes[] = DB::get_user_notes($id);
	$author = DB::get_user($id);

	$content = get_include_contents(base() . 'v/user_notes.tpl.php');

}

function view($tag){
	global $user;
	global $error;
	global $success;
	global $content;
	global $note;
	global $owner;
	global $author;
	global $userrating;

	$owner = FALSE;
	$author = FALSE;
	$note = DB::get_note($tag);
	$schools = array();

	// Check to see if the current user is the author of this note, if so allow them to edit it
	if ($note->userid == $user->id || in_array('admin', $user->access)) {
		$author = TRUE;
	}
	// Check to see if the current user has purchased this note, if so allow them to download it
	$a = db::own_note($user->id, $note->id);
	if ($a->found == 't'){
		$owner = TRUE;
	}

	// Add rating if they own the note
	if ($owner) {
		// Load the user's ranking if they've already submitted one
		$userrating = DB::get_user_note_rating($user->id, $tag);
	}

	/* Show error if user doesn't have access to this note */
    	/* Don't need this as user can now view all notes
	foreach ($user->schools as $school) {
		$schools[] = $school->id;
	}

	if (!in_array($note->school, $schools) && !in_array('admin', $user->access)) {
		$error = "You don't have access to this note!";
		global $notes;
		$notes = array();
		foreach ($user->schools as $school) {
			$notes[] = DB::get_notes($school->id);
		}
		$content = get_include_contents(base() . 'v/buy_notes.tpl.php');
		return;
	}
    	*/

	$content = get_include_contents(base() . 'v/note.tpl.php');
}

function save($tag=null){
	global $user;
	global $error;
	global $success;
	global $content;
	if (empty($tag)) {
		/* Get and filter post data */
		$title = preg_replace("~[^a-zA-Z0-9 \.\-\']~", "", $_POST["title"]);
		$description = preg_replace("~[^a-zA-Z0-9 \.\-\']~", "", $_POST["description"]);
		$schoolID = preg_replace("~[^0-9]~", "", $_POST["school"]);
		$year = preg_replace("~[^0-9]~", "", $_POST['year']);
		$term = preg_replace("~[^a-zA-Z]~", "", $_POST['term']);
		$cost = preg_replace("~[^0-9\.]~", "", $_POST['cost']);
		$courseID = preg_replace("~[^0-9]~", "", $_POST['courseID']);
		$subject = preg_replace("~[^a-zA-Z0-9 \.\-\']~", "", $_POST['subject']);
		$number = preg_replace("~[^a-zA-Z0-9 \.\-\']~", "", $_POST['number']);
		$professorID = preg_replace("~[^0-9]~", "", $_POST['professor']);
		$professorID2 = preg_replace("~[^0-9]~", $_POST['professor2']);
		$fname = preg_replace("~[^a-zA-Z0-9 \.\-\']~", "", $_POST['fname']);
		$lname = preg_replace("~[^a-zA-Z0-9 \.\-\']~", "", $_POST['lname']);

		/* verify required datasets are set */
		if (empty($title)) {
			$error = "Invalid title";
		} else if (empty($description)) {
			$error = "Invalid description";
		} else if (empty($schoolID)) {
			$error = "Invalid school";
		} else if (empty($year)) {
			$error = "Invalid year";
		} else if (empty($term)) {
			$error = "Invalid term";
		} else if (empty($cost)) {
			$error = "Invalid cost";
		}

		// Two places they could've selected professor for, assume professor - if not, use professor2
		if (empty($professorID) && !empty($professorID2)) { 
			$professorID = $professorID2; 
		}

		if (empty($courseID) && (empty($subject) || empty($number))) {
			$error = "Invalid course";
		}

		if (empty($courseID) && empty($professorID) && (empty($fname) || empty($lname))) {
			$error = "Invalid professor";
		}

		if (!empty($error)) {
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}

		/* Move the file first to verify that saving the rest of the data even matters (no file, no data needed) */
		if ($_FILES['file']['error']) {
			$error = "Problem: ";
			switch ($_FILES['file']['error']) {
				case 1: $error .= "File exceeded upload_max_filesize"; break;
				case 2: $error .= "File exceeded max_file_size"; break;
				case 3: $error .= "File only partially uploaded"; break;
				case 4: $error .= "No file uploaded"; break;
				}
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}
		

		$bits = explode('.',$_FILES['file']['name']);
		$ext = strtolower(array_pop($bits));

		$newid = time();

		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$rando = '';
		for ($i = 0; $i < 10; $i++) {
			$rando .= $characters[rand(0, strlen($characters) - 1)];
		}

		$newid = $rando . $newid;

		// File rename process:	
		$_FILES['file']['name'] = $newid.".".$ext; //Change filename to timestamp and same extension
		$filename = $_FILES['file']['name'];
		$upfile = "f";
		$databasename = DIRECTORY_SEPARATOR.$upfile.DIRECTORY_SEPARATOR.$filename;
		$filedir = $upfile.DIRECTORY_SEPARATOR;
		
		if (!move_uploaded_file($_FILES['file']['tmp_name'],$upfile.DIRECTORY_SEPARATOR.$filename)) {
			$error = "There was a problem moving your file";
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}

		/* Move preview file if exists */
		if (!empty($_FILES['preview'])) {
			if ($_FILES['preview']['error']) {
				$error = "Problem: ";
				switch ($_FILES['preview']['error']) {
					case 1: $error .= "Preview exceeded upload_max_filesize"; break;
					case 2: $error .= "Preview exceeded max_file_size"; break;
					case 3: $error .= "Preview only partially uploaded"; break;
					case 4: $error .= "No preview uploaded"; break;
					}
				echo '{"success":"false", "message":"'.$error.'" }';
				die();
			}

			$preview_bits = explode('.',$_FILES['preview']['name']);
			$preview_ext = strtolower(array_pop($preview_bits));

			// Preview rename process:	
			$_FILES['preview']['name'] = $newid.".".$preview_ext; //Change preview to timestamp and same extension
			$preview_filename = $_FILES['preview']['name'];
			$preview_upfile = "f".DIRECTORY_SEPARATOR."previews";
			$preview = DIRECTORY_SEPARATOR.$preview_upfile.DIRECTORY_SEPARATOR.$preview_filename;
			$preview_filedir = $preview_upfile.DIRECTORY_SEPARATOR;
			
			if (!move_uploaded_file($_FILES['preview']['tmp_name'],$preview_upfile.DIRECTORY_SEPARATOR.$preview_filename)) {
				$error = "There was a problem moving the preview file";
				echo '{"success":"false", "message":"'.$error.'" }';
				die();
			}
		}

		$tn = '';
		$use_preview = FALSE;
		if (isset($preview)) {
			$ext = $preview_ext;
			$use_preview = TRUE;
		}
		
		/* Create tn of file */
		$tn = 'i/img/no_tn.gif';

		if ($ext == 'jpg') {
			// create tn
			if ($use_preview) {
				$tn = createThumb($preview_filedir, $preview_filename, $ext, 150, 150);
			} else {
				$tn = createThumb($filedir, $filename, $ext, 150, 150);
			}
		}
		if ($ext == 'png') {
			// create tn
			if ($use_preview) {
				$tn = createThumb($preview_filedir, $preview_filename, $ext, 150, 150);
			} else {
				$tn = createThumb($filedir, $filename, $ext, 150, 150);
			}
		}

		/* Work backward through post data steps to make sure we add 
		 * anything meant to be added otherwise it would've been removed */
		
		/* Create new professor */
		if (!empty($fname)) {
			$professorID = Insert::addProfessor($fname, $lname, $schoolID);
		}

		/* Create new course */
		if (!empty($subject) && !empty($professorID)) {
			$courseID = Insert::addCourse($subject, $number, $professorID, $schoolID);
		}

		if (!$courseID) {
			$error = "Database offline!  Try again soon.";
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}

		/* Create new note */
		$noteID = Insert::addNote($title, $description, $databasename, $courseID, $schoolID, $cost, 'USD', $user->id, $tn, $preview, $year, $term);

		if (!$noteID) {
			$error = "Database offline!  Try again soon.";
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}

		echo '{"success":"true", "noteID":"'.$noteID.'" }';
		die();

	} else {
		// This is an update not a new save
		/* Get and filter post data */
		$title = preg_replace("~[^a-zA-Z0-9 \.\-\']~", "", $_POST["title"]);
		$description = preg_replace("~[^a-zA-Z0-9 \.\-\']~", "", $_POST["description"]);
		$year = preg_replace("~[^0-9]~", "", $_POST['year']);
		$term = preg_replace("~[^a-zA-Z]~", "", $_POST['term']);
		$cost = preg_replace("~[^0-9\.]~", "", $_POST['cost']);

		/* verify required datasets are set */
		if (empty($title)) {
			$error = "Invalid title";
		} else if (empty($description)) {
			$error = "Invalid description";
		} else if (empty($year)) {
			$error = "Invalid year";
		} else if (empty($term)) {
			$error = "Invalid term";
		} else if (empty($cost)) {
			$error = "Invalid cost";
		}

		if (!empty($error)) {
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}

		/* save note */
		$n = new Note($tag);
		$note = $n->note;
		$note->title = $title;
		$note->description = $description;
		$note->year = $year;
		$note->term = $term;
		$note->cost = $cost;
		$pass = $n->save();

		if (!$pass) {
			$error = "Database offline!  Try again soon.";
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}

		echo '{"success":"true", "noteID":"'.$note->id.'" }';
		die();
	}

}

function purchase($tag=null){
	global $user;
	global $error;
	$headers = array();
	$baseurl = 'https://svcs.paypal.com/AdaptivePayments/Pay'; //production
	$username = ('jake_api1.teamgoblin.com');
	$password = ('BUSR6VWGNTME96YV');
	$signature = ('A4LgJzGyw4h8-E07vzBzgRCdCpe7ANrzyum5JhF-i7o0VnsGUylz1PzH');
	$appid = ('APP-40G05538LR861104F');
	$returnurl = ('http://notegoblin.com/notes/view/'); // where the user is sent upon successful completion
	$cancelurl = ('http://notegoblin.com/notes/view/'); // where the user is sent upon canceling the transaction
	$lang = ('en_US');
	//$jonte = ('jonte@teamgoblin.com');
	$teamgoblin = ('jake@teamgoblin.com');
	$ipnURL = 'http://notegoblin.com/notes/ipnListener';
	$post = '';

	// Load the passed note id to get the cost, title, and seller email
	$note = DB::get_note($tag);
	$seller = $note->email; // seller email
	if (empty($seller)) { // if the user deleted their account we make the money
		$seller = 'jake@teamgoblin.com';
	}

    $percentage = 0.7; // default is 70%
    $paypal1 = .30; // default is 30%

    $now = new DateTime('now');
    $created = new DateTime($seller->created);

    $difference = $now->diff($created, true);

    if ($difference->days < 31)
    {
        $percentage = 0.8;
        $paypal1 = 0.2;
    }

	$cost = (float)$note->cost; // retail cost
	$payout = round($percentage * $cost,2); // amount to be paid out, 70%

	// Calculate amount paid to notegoblin (30%-fees)
	$paypal2 = .029;
	$notegoblin_amt = round(($cost - $payout - $paypal1 - ($cost * $paypal2)),2);
	//$notegoblin_amt = round($notegoblin_amt/2,2);

	// Create transaction (buyer, seller, note, retail, payout, currency)
	$transaction_id = Insert::addTransaction($user->id, $note->userid, $note->id, $cost, $payout, $note->currency);
	if (empty($transaction_id)) {
		return;
	}

	$ipnURL .= "/" . $transaction_id;

	$headers[] = "X-PAYPAL-SECURITY-USERID: ".$username;
	$headers[] = "X-PAYPAL-SECURITY-PASSWORD: ".$password;
	$headers[] = "X-PAYPAL-SECURITY-SIGNATURE: ".$signature;
	$headers[] = "X-PAYPAL-APPLICATION-ID: ".$appid;
	$headers[] = "X-PAYPAL-REQUEST-DATA-FORMAT: NV";
	$headers[] = "X-PAYPAL-RESPONSE-DATA-FORMAT: JSON";
	
	$post .= 'actionType=PAY';
	$post .= '&cancelUrl='.$cancelurl.$note->id;
	$post .= '&currencyCode='.$note->currency;
	$post .= '&custom='.$transaction_id;
	$post .= '&feesPayer=PRIMARYRECEIVER';
	$post .= '&ipnNotificationUrl='.$ipnURL;
	$post .= '&memo=Notegoblin Note ' . $note->title;
	$post .= '&paymentType=DIGITALGOODS';
	$post .= '&receiverList.receiver(0).amount='.$cost; // AMOUNT THEY ARE PAYING TOTAL
	$post .= '&receiverList.receiver(0).email='.$seller; // PRIMARY RECEIVER IS SELLER
	$post .= '&receiverList.receiver(0).primary=true';
	$post .= '&receiverList.receiver(1).amount='.$notegoblin_amt; // AMOUNT PAYING TO NOTEGOBLIN
	$post .= '&receiverList.receiver(1).email='.$teamgoblin; // NOTEGOBLIN ACCOUNT
	$post .= '&receiverList.receiver(1).primary=false';
	//$post .= '&receiverList.receiver(2).amount='.$notegoblin_amt; // AMOUNT PAYING TO JONTE
	//$post .= '&receiverList.receiver(2).email='.$jonte; // JONTE ACCOUNT
	//$post .= '&receiverList.receiver(2).primary=false';
	$post .= '&requestEnvelope.errorLanguage='.$lang;
	$post .= '&returnUrl='.$returnurl.$note->id;
	$post .= '&trackingID='.$transaction_id;
	$output_str = CurlMePost($baseurl,$headers,$post);
	$output = JSON_decode($output_str);
	$ack = $output->responseEnvelope->ack;
	if ($ack == 'Success') {
        $name = "Note Goblin"; //senders name
        $from = "help@notegoblin.com"; //senders e-mail adress
        $message = "Hello,\nA new purchase was made on NoteGoblin.com!\nBuyer: $user->id Seller: $note->userid Transaction: $transaction_id\n\nNoteGoblin Staff";
        $subject = 'New purchase on NoteGoblin!'; //subject
        mailer($name, $from, implode(',', unserialize(NOTEGOBLIN_GROUP_EMAIL)), $subject, $message);

		$token = $output->payKey ?: '';
		header('Location: https://www.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey='.$token);
		die();
	} else {
		$error = "Paypal had an error, please try again later.";
		view($tag);
		//var_dump($output_str);
		//var_dump($output);
		//die();
	}
}

function edit($tag) {
	global $user;
	global $error;
	global $success;
	global $content;
	global $professors;
	global $courses;
	global $note;

	$owner = FALSE;
	$author = FALSE;
	$note = DB::get_note($tag);

	// Check to see if the current user is the author of this note, if so allow them to edit it
	if ($note->userid == $user->id || in_array('admin', $user->access)) {
		$author = TRUE;
	}

	if (!$author) {
		// Set error and load the note page
		$error = "You aren't the author of this note!";
		$content = get_include_contents(base() . 'v/buy_notes.tpl.php');
		return;
	}

	$content = get_include_contents(base() . 'v/edit_note.tpl.php');
}

function remove($tag) {
	global $user;
	global $error;
	global $success;
	global $content;
	global $professors;
	global $courses;
	global $note;

	$owner = FALSE;
	$author = FALSE;
	$note = DB::get_note($tag);

	// Check to see if the current user is the author of this note, if so allow them to edit it
	if ($note->userid == $user->id || in_array('admin', $user->access)) {
		$author = TRUE;
	}

	if (!$author) {
		// Set error and load the note page
		$error = "You aren't the author of this note!";
		$content = get_include_contents(base() . 'v/buy_notes.tpl.php');
		return;
	}

	$content = get_include_contents(base() . 'v/delete_note.tpl.php');
}

function deleteConfirm($tag) {
	global $user;
	global $error;
	global $success;
	global $content;
	global $professors;
	global $courses;
	global $note;

	$owner = FALSE;
	$author = FALSE;
	$note = DB::get_note($tag);

	// Check to see if the current user is the author of this note, if so allow them to edit it
	if ($note->userid == $user->id || in_array('admin', $user->access)) {
		$author = TRUE;
	}

	if (!$author) {
		// Set error and load the note page
		$error = "You aren't the author of this note!";
		$content = get_include_contents(base() . 'v/buy_notes.tpl.php');
		return;
	}

	/* delete the note */
	$n = new Note();
	$n->id = $tag;
	$pass = $n->delete();

	if (!$pass) {
		$error = "Database offline!  Try again soon.";
		echo '{"success":"false", "message":"'.$error.'" }';
		die();
	}

	echo '{"success":"true"}';
	die();
}

function download($tag) {
	global $user;
	global $error;
	global $success;
	global $content;
	global $note;
	global $owner;

	$owner = FALSE;
	$note = DB::get_note($tag);

	$bits = explode('.',$note->file);
	$ext = strtolower(array_pop($bits));

	// Check to see if the current user has purchased this note, if so allow them to download it
	$a = db::own_note($user->id, $note->id);
	if ($a->found == 't'){
		$owner = TRUE;
	}

	if ($owner) {
		// Download file
		// Setup
		$mimeTypes = array(
			'pdf' => 'application/pdf',
			'txt' => 'text/plain',
			'html' => 'text/html',
			'doc' => 'application/msword',
			'docx' => 'application/msword',
			'ppt' => 'application/vnd.ms-powerpoint',
			'png' => 'image/png',
			'jpg' => 'image/jpg',
		);

		// Send Headers
		header('Content-Type: ' . $mimeTypes[$ext]); 
		header('Content-Disposition: attachment; filename="' .  URLify::filter($note->title) .".". $ext . '"');
		header('Content-Transfer-Encoding: binary');
		header('Accept-Ranges: bytes');
		header('Cache-Control: private');
		header('Pragma: private');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		readfile(base().$note->file);
		die();
	} else {
		// Set error and load the note page
		$error = "You don't own this note!";

		/* Show error if user doesn't have access to this note */
		foreach ($user->schools as $school) {
			$schools[] = $school->id;
		}
		if (!in_array($note->school, $schools)) {
			$error = "You don't have access to this note!";
			global $notes;
			$notes = array();
			foreach ($user->schools as $school) {
				$notes[] = DB::get_notes($school->id);
			}
			$content = get_include_contents(base() . 'v/buy_notes.tpl.php');
			return;
		}

		$content = get_include_contents(base() . 'v/note.tpl.php');
	}
}

function ipnListener($tag) {	
	// STEP 1: read POST data

	// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
	// Instead, read raw POST data from the input stream.
	$raw_post_data = file_get_contents('php://input');
	$raw_post_array = explode('&', $raw_post_data);
	$myPost = array();
	foreach ($raw_post_array as $keyval) {
		$keyval = explode ('=', $keyval);
		if (count($keyval) == 2)
			$myPost[$keyval[0]] = urldecode($keyval[1]);
	}
	// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
	$req = 'cmd=_notify-validate';
	if(function_exists('get_magic_quotes_gpc')) {
		$get_magic_quotes_exists = true;
	}
	foreach ($myPost as $key => $value) {
		if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
			$value = urlencode(stripslashes($value));
		} else {
			$value = urlencode($value);
		}
		$req .= "&$key=$value";
	}

	// STEP 2: POST IPN data back to PayPal to validate

	$baseurl = 'https://www.paypal.com/cgi-bin/webscr'; // LIVE API
	//$baseurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; // SANDBOX API
	$output_str = CurlMePost($baseurl,array('Connection: Close'),$req);

	// STEP 3: Inspect IPN validation result and act accordingly

	if (strcmp ($output_str, "VERIFIED") == 0) {
		// The IPN is verified, process it:
		$pass = TRUE;
		
		// check whether the payment_status is Completed
		if ($_POST['status'] != 'COMPLETED') {
			$pass = FALSE;
		}

		// check that action type is pay
		if ($_POST['action_type'] != 'PAY') {
			$pass = FALSE;
		}

		// check that the tag for the transaction is set
		if (empty($tag)) {
			$pass = FALSE;
		}
		
		// If all checks pass, update transaction table to complete
		if ($pass) {
			Transaction::complete($tag);
		}
	} else if (strcmp ($output_str, "INVALID") == 0) {
		// IPN invalid, log for manual investigation
		error_log("The response from IPN was:" .$output_str);
	}
}

function rate() {
	global $user;
	global $error;
	global $success;
	global $content;
	global $note;

	$rating = $_POST['value'];
	$noteid = $_POST['id'];
	
	// Check if user has already rated this note, if so update their rating
	$check = DB::get_user_note_rating($noteid, $user->id);
	if (!empty($check)) {
		// Update
		if (Insert::updateRating($check->id, $value)) {
			// Success
			echo '{"success":"true"}';
			die();
		} else {
			// Fail
			$error = "Database offline!  Try again soon.";
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}
	} else {
		// If they haven't already rated this note, create a new rating	
		if (Insert::addRating($noteid, $user->id, $rating)) {
			// Success
			echo '{"success":"true"}';
			die();
		} else {
			// Fail
			$error = "Database offline!  Try again soon.";
			echo '{"success":"false", "message":"'.$error.'" }';
			die();
		}
	}
}
