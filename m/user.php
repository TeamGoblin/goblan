<?php

class User {
	public $id; // User id
	public $alias; // Alias
	public $email; // Email address
	public $password; // Password
	public $newpassword; // Whether or not this is a new password
	public $created; // Account creation time
	public $modified; // Account last modified time
	public $language; // User default language 
	public $paypal; // User paypal email address
	public $key; // User access key
	public $access; // Account access level NULL by default
	public $logged; // Whether the account is logged in or not
	public $error; // Any errors
	public $success; // Success messages
	public $info; // Informative messages
	public $warning; // Warning messages
	public $keep; // Keep user logged in with cookies or not
	public $school; // Used when registering
	public $newschool; // Used when registering
	public $schools; // Array of schools user is registered with

	/**
	* Construct method
	*
	* @param $arg array
	* Values used to create object
	*/
	function __construct($arg = array()) {
		if (isset($arg['id'])) {
			$this->id = $arg['id'];
			// load rest of data from db
			$this->load();
		}
		if (isset($arg['alias'])) {
			$this->alias = $arg['alias'];
		}
		if (isset($arg['email'])) {
			$this->email = $arg['email'];
		}
		if (isset($arg['password'])) {
			$this->password = $arg['password'];
		}
	}

	/**
	 * Load user info
	 */
	public function load(){
		$q = 'SELECT alias, email, password, created, modified, language, paypal, key
			  FROM users u
			  WHERE u.id = $1';
		$result = pg_query_params($q, array($this->id));
		$row = pg_fetch_object($result);
		if (!empty($row)) {
			$this->alias = $row->alias;
			$this->email = $row->email;
			$this->password = $row->password;
			$this->created = $row->created;
			$this->modified = $row->modified;
			$this->language = $row->language;
			$this->paypal = $row->paypal;
			$this->schools = $this->getSchools();
			$this->access = $this->getAccess(); // returns an array that gets all the access associated with the user
			$this->logged = TRUE;
			
			/* Store session data */
			$_SESSION['notegoblin_id'] = $this->id;
			$_SESSION['notegoblin_user'] = $this->alias;
			$_SESSION['notegoblin_access'] = $this->access;

			/* Create key (updated each page load) */
			$goblin_key = md5($this->randomString() . $this->alias);
			pg_query_params("UPDATE users SET key = $1 WHERE id = $2", array($goblin_key, $this->id));

			/* Update cookie with new key */
			setcookie("notegoblin_key", $goblin_key, time()+60*60*24*30, '/');
			setcookie("notegoblin_login", true, time()+60*60*24*30*12, '/');
			
			if ($this->keep) {
				setcookie("notegoblin_id", $this->id, time()+60*60*24*30, '/');
			}

			$this->key = $goblin_key;
			
		} else {
			$this->logged = FALSE;
			setcookie("notegoblin_key", '', time()-3600);
			setcookie("notegoblin_id", '', time()-3600);
		}
	}

    public function getAccess() {
        $permissions = array();

        $sql = 'SELECT * FROM users_access JOIN access ON users_access.access = access.id
                WHERE users_access.user = $1';
        $result = pg_query_params($sql, array($this->id));
        if ($result)
        {
            $row = pg_fetch_all_objects($result);
            foreach ($row as $user_access)
            {
                $permissions[] = $user_access->role;
            }
        }
        return $permissions;
    }

	/**
	 * Save User Info
	 */
	public function save() {
		// Check update or new
		if (!empty($this->id)) {
			// Verify all required fields are set
			if ($this->verify()){
				// Check new password or not
				if (!empty($this->newpassword) && $this->password) {
					$t_hasher = new PasswordHash(12, FALSE);
					$hash = $t_hasher->HashPassword($this->password);
					$q = 'UPDATE users SET alias=$1, email=$2, password=$3, paypal=$4, language=$5, modified=now() WHERE id = $6;';
					$result = pg_query_params($q, array($this->alias, $this->email, $hash, $this->paypal, $this->language, $this->id));
				} else {
					$q = 'UPDATE users SET alias=$1, email=$2, paypal=$3, language=$4, modified=now() WHERE id = $5;';
					$result = pg_query_params($q, array($this->alias, $this->email, $this->paypal, $this->language, $this->id));
				}
				if (!$result) {
					$this->error = pg_last_error();
				} else {
					$return = true;
					$this->success = "User information updated.";
				}
			}
		} else {
			// Verify all required fields are set
			if ($this->verifyNew()){
				$t_hasher = new PasswordHash(12, FALSE);
				$hash = $t_hasher->HashPassword($this->password);
				$q = 'INSERT INTO users (id, alias, email, password, created, modified, active) VALUES (default, $1, $2, $3, now(), now(), FALSE) RETURNING id;';

				$result = pg_query_params($q, array($this->alias, $this->email, $hash));
				if (!$result) {
					$this->error = pg_last_error();
				} else {
					$row = pg_fetch_object($result);
					$this->id = $row->id;
					$return = true;
					$this->success = "Thanks for signing up!";

                    if (!empty($this->access))
                    {
                        // add the access for this user
                        $this->error .= "non empty" . $this->id . ' access ' . $this->access . ' e ';
                        $q = 'INSERT INTO users_access VALUES($1, $2)';
                        pg_query_params($q, array($this->id, $this->access));
                    }
                    else {
                        $this->error .= "It's empty";
                    }

					// Verify school is set
					if ($this->school) {
						$q = 'INSERT INTO users_schools ("user", school) VALUES ($1, $2)';
						$result = pg_query_params($q, array((int)$this->id, (int)$this->school));
						if (!$result) {
							$this->error = pg_last_error();
							$return = false;
						} else {
							$q = "UPDATE users SET active = TRUE WHERE id = $1";
							$result = pg_query_params($q, array($this->id));
						}
					} else {
						$return = false;
						$this->error .= "Your account has been registered!  Unfortunately, your school has not yet partnered with NoteGoblin.  We'll notify you once your school has been added!  Thanks for your interest.";
						// Register school and user in not yet provided school table and don't activate user
						// First check to see if school name is already in unavail schools
						$q = 'SELECT * FROM unavail_schools WHERE name =$1';
						$result = pg_query_params($q, array($this->newschool));
						$row = pg_fetch_object($result);
						if (empty($row->id)){
							// If its not, then insert it
							$q = 'INSERT INTO unavail_schools (id, name) VALUES (default, $1) RETURNING id';
							$result = pg_query_params($q, array($this->newschool));
							$row = pg_fetch_object($result);
						}
						// Insert user link to unavail school
						$q = 'INSERT INTO users_unavail_schools ("user", unavail_school) VALUES ($1, $2)';
						$result = pg_query_params($q, array($this->id, $row->id));
					}
				}
			}
		}
		return $return;
	}

	/**
	 * Save user password only
	 */
	public function savepw() {
		$return = false;
		// Check to see that the key matches the user id
		if ($this->userCheck()) {
			if ($this->password) {
				$t_hasher = new PasswordHash(12, FALSE);
				$hash = $t_hasher->HashPassword($this->password);
				$q = 'UPDATE users SET password=$1, modified=now() WHERE id = $2 RETURNING email;';
				$result = pg_query_params($q, array($hash, $this->id));
				if (!$result) {
					$this->error = pg_last_error();
				} else {
					$row = pg_fetch_object($result);
					$this->email = $row->email;
					$return = true;
					$this->success = "Password updated.";
				}
			}
		}
		return $return;
	}

	/**
	 * Log user in
	 */
	public function login() {
		$return = false;
		$q = 'SELECT * FROM users WHERE email=$1 AND active=true;';
		$result = pg_query_params($q, array(strtolower($this->email)));
		$row = pg_fetch_object($result);
		if (!empty($row)) {
			if ($this->passcheck($row->password)) {
				$this->id = $row->id;
				$this->load();
				$return = true;
				
				/* Add success message */
				$this->success = "You've logged in.";
			}
		} else {
			$this->error = "Login failed.";
		}
		return $return;
	}

	/**
	 * Log user out
	 */
	public function logout() {
		$return = false;
		/* clear cookie and session data */
		setcookie("notegoblin_key", '', time()-3600, '/');
		setcookie("notegoblin_id", '', time()-3600, '/');

		$_SESSION['notegoblin_id'] = '';
		$_SESSION['notegoblin_user'] = '';
		$_SESSION['notegoblin_access'] = '';

		$this->alias = NULL;
		$this->email = NULL;
		$this->password = NULL;
		$this->created = NULL;
		$this->modified = NULL;
		$this->modified = NULL;
		$this->language = NULL;
		$this->paypal = NULL;
		$this->key = NULL;
		$this->access = NULL;
		$this->logged = false;

		$return = true;
		return $return;
	}

	/**
	 * Reset user password
	 */
	public function reset() {
		$password = $this->randomString();
		$this->password = $password;
		$this->save();
	}

	/**
	 * Check user password
	 */
	public function passcheck($hash) {
		$return = false;
		$t_hash = new PasswordHash(12, false);
		$check = $t_hash->CheckPassword($this->password, $hash);
		if ($check){
			$return = true;
		} else {
			$this->error = "Login failed.";
		}
		return $return;
	}

	/**
	 * Check email address and return key
	 */
	public function emailCheck($email) {
		$return = false;
		$q = 'SELECT key FROM users WHERE email=$1;';
		$result = pg_query_params($q, array(strtolower($email)));
		$row = pg_fetch_object($result);
		if (!empty($row)) {
			$return = $row->key;
		} else {
			$this->error = "Invalid Email.";
		}
		return $return;
	}

	/**
	 * Check key for user
	 */
	public function keyCheck($key) {
		$return = false;
		$q = 'SELECT id FROM users WHERE key=$1;';
		$result = pg_query_params($q, array($key));
		$row = pg_fetch_object($result);
		if (!empty($row)) {
			$return = $row->id;
		} else {
			$this->error = "Invalid key.";
		}
		return $return;
	}

	/**
	 * User check - verify userID matches given KEY
	 */
	public function userCheck() {
		$return = false;
		$q = 'SELECT key FROM users WHERE id=$1;';
		$result = pg_query_params($q, array($this->id));
		$row = pg_fetch_object($result);
		if (!empty($row)) {
			if ($this->key == $row->key) {
				$return = true;
			} else {
				$this->error = "Invalid key.";
			}
		} else {
			$this->error = "Invalid user.";
		}
		return $return;
	}

	/**
	 * Verify all required fields are met
	 */
	public function verify() {
		$pass = true;
		if (empty($this->alias)) { $this->error = "Alias is required."; $pass = false; }
		if (empty($this->email)) { $this->error = "Email is required."; $pass = false; }
		return $pass;
	}

	/**
	 * Verify all required fields are met for new users
	 */
	public function verifyNew() {
		$pass = true;
		if (empty($this->alias)) { $this->error = "Alias is required."; $pass = false; }
		if (empty($this->email)) { $this->error = "Email is required."; $pass = false; }
		if (empty($this->password)) { $this->error = "Password is required."; $pass = false; }
		return $pass;
	}

	/**
	 * Generate a random string/password
	 */
	function randomString() {
	    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < 12; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}

	/**
	 * Given a user get list of user schools
	 */
	function getSchools() {
		$q = 'SELECT * FROM users_schools us
			  INNER JOIN schools on us.school = schools.id
			  WHERE us.user = $1';
		$result = pg_query_params($q, array($this->id));
		if ($result) {
			$return = pg_fetch_all_objects($result);
		} else {
			$return = FALSE;
		}
		return $return;
	}

}