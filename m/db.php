<?php

class DB {
	/* Search schools */
	public static function searchSchools($query) {
		$q = "SELECT * FROM schools WHERE name ILIKE $1 ORDER BY name, city";
		$r = pg_query_params($q, array("%".$query."%"));
		$return = pg_fetch_all_objects($r);
		return $return;
	}

	/* Count of users matching given query */
	public static function uniqueUser($query) {
		$q = "SELECT COUNT(*) FROM users WHERE alias = $1";
		$r = pg_query_params($q, array($query));
		$return = pg_fetch_object($r);
		return $return->count;
	}

    /* Get all notes for a given period */
    public static function get_notes_from_period($dateFrom, $dateTo){
        $q = 'SELECT
		notes.*, courses.subject, courses.number, users.alias, professors.fname, professors.lname,
		(SELECT AVG(ratings.rating) avgrating FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = notes.id)
		FROM notes
		INNER JOIN courses on courses.id = notes.course
		INNER JOIN users on notes.author = users.id
		INNER JOIN professors on courses.professor = professors.id
		WHERE (notes.deleted = false OR notes.deleted is null)
		AND notes.created BETWEEN $1 AND $2
		ORDER BY created;';
        $result = pg_query_params($q, array($dateFrom, $dateTo));
        return pg_fetch_all_objects($result);
    }

	/* Count of users matching given query */
	public static function uniqueEmail($query) {
		$q = "SELECT COUNT(*) FROM users WHERE email = $1";
		$r = pg_query_params($q, array($query));
		$return = pg_fetch_object($r);
		return $return->count;
	}

	/* Return countries from database */
	public static function get_countries() {
		$q = 'SELECT * FROM countries ORDER BY name;';
		$result = pg_query($q);
		return pg_fetch_all($result);
	}

	/* Return schools from database */
	public static function get_schools() {
		$q = 'SELECT * FROM schools ORDER BY name;';
		$result = pg_query($q);
		return pg_fetch_all_objects($result);
	}

	/* Return courses from database for a given school */
	public static function get_courses($school=null) {
		if (!empty($school)) {
			$param = "SELECT * FROM courses INNER JOIN course_school cs on cs_course = courses.id WHERE cs.school = $1";
			$result = pg_query_params($q, array($school));
		} else {
			$q = 'SELECT * FROM courses ORDER BY subject, number;';
			$result = pg_query($q);
		}
		return pg_fetch_all_objects($result);
	}

	/* Return professors from database for a given school */
	public static function get_professors($school=null) {
		if (!empty($school)) {
			$q = 'SELECT * FROM professors WHERE school = $1 ORDER BY lname, fname;';
			$result = pg_query_params($q, array($school));
		} else {
			$q = 'SELECT * FROM professors ORDER BY lname, fname;';
			$result = pg_query($q);
		}
		return pg_fetch_all_objects($result);
	}

	/* Return years from database for a given school */
	public static function get_years($school=null) {
		if (!empty($school)) {
			$q = 'SELECT distinct year FROM notes WHERE school = $1 ORDER BY year DESC;';
			$result = pg_query_params($q, array($school));
		} else {
			$q = 'SELECT distinct year FROM notes ORDER BY year DESC;';
			$result = pg_query($q);	
		}
		return pg_fetch_all_objects($result);
	}

	/* Return terms from database for a given school */
	public static function get_terms($school=null) {
		if (!empty($school)) {
			$q = 'SELECT distinct term FROM notes WHERE school = $1;';
			$result = pg_query_params($q, array($school));
		} else {
			$q = 'SELECT distinct term FROM notes';
			$result = pg_query($q);	
		}
		return pg_fetch_all_objects($result);
	}

	/* Return user data for a given user id */
	public static function get_user($id) {
		$q = 'SELECT * FROM users WHERE id = $1';
		$result = pg_query_params($q, array($id));
		return pg_fetch_object($result);
	}

	/* Return comments for given id/type from database */
	public static function get_comments($id, $type) {
		$q = 'SELECT c.id, c.body, c.created, c.author author_id, u.alias author, u.country_id, countries.name country_name, countries.abbv, countries.file, u.created joined FROM comments c 
			  INNER JOIN users u ON c.author = u.id
			  INNER JOIN countries ON u.country_id = countries.id
			  WHERE ref_id=$1 
			  AND ref_type=$2 
			  ORDER BY created;';
		$result = pg_query_params($q, array($id, $type));
		return pg_fetch_all($result);
	}

	/* Returns specific given id note */
	public static function get_note($id) {
		$q = 'SELECT 
		notes.*, courses.subject, courses.number, users.id userid, users.alias, users.email, professors.fname, professors.lname,
		(SELECT AVG(ratings.rating) avgrating FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = notes.id)
		FROM notes
		INNER JOIN courses on courses.id = notes.course
		INNER JOIN course_professor cp on cp.course = courses.id
		INNER JOIN users on notes.author = users.id
		INNER JOIN professors on cp.professor = professors.id
		WHERE notes.id = $1';
		$result = pg_query_params($q, array($id));
		return pg_fetch_object($result);
	}

	/* Return notes from database for a given school */
	public static function get_notes($school=null) {
		$add = null;

		if (!empty($school)) {
			$add = 'AND cs.school = $1 ';
		}

		$q = 'SELECT 
		notes.*, courses.subject, courses.number, users.alias, professors.fname, professors.lname,
		(SELECT AVG(ratings.rating) avgrating FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = notes.id)
		FROM notes
		INNER JOIN courses on courses.id = notes.course
		INNER JOIN course_professor cp on cp.course = courses.id
		INNER JOIN course_school cs on cs.course = courses.id
		INNER JOIN users on notes.author = users.id
		INNER JOIN professors on cp.professor = professors.id
		WHERE (notes.deleted = false OR notes.deleted is null)'
		. $add .
		'ORDER BY created;';
		if (!empty($school)) {
			$result = pg_query_params($q, array($school));
		} else {
			$result = pg_query($q);
		}
		return pg_fetch_all_objects($result);
	}

	/* Return notes sold by a specific user */
	public static function get_user_notes($user) {
		$q = 'SELECT 
		notes.*, courses.subject, courses.number, users.alias, professors.fname, professors.lname,
		(SELECT AVG(ratings.rating) avgrating FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = notes.id)
		FROM notes
		INNER JOIN courses on courses.id = notes.course
		INNER JOIN course_professor cp on cp.course = courses.id
		INNER JOIN users on notes.author = users.id
		INNER JOIN professors on cp.professor = professors.id
		WHERE users.id = $1
		AND (notes.deleted = false OR notes.deleted is null)
		ORDER BY created;';
		$result = pg_query_params($q, array($user));
		return pg_fetch_all_objects($result);
	}

	/* Return notes from database for a given school matching filters */
	/* Fix for variable number of parameters here */
	public static function search_notes($school = null, $course = null, $professor = null, $term = null, $year = null){
		$params = 1;
		$aparam = array();

		$q = 'SELECT 
		notes.*, courses.subject, courses.number, users.alias, professors.fname, professors.lname,
		(SELECT AVG(ratings.rating) avgrating FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = notes.id)
		FROM notes
		INNER JOIN courses on courses.id = notes.course
		INNER JOIN course_professor cp on cp.course = courses.id
		INNER JOIN course_school cs on cs.course = courses.id
		INNER JOIN users on notes.author = users.id
		INNER JOIN professors on cp.professor = professors.id
		WHERE (notes.deleted = false OR notes.deleted is null) ';
		
		// Add school if exists
		if (!empty($school)) {
			$q .= 'AND cs.school = $'.$params.' '; 
			$params++;
			$aparam[] = $school;
		}

		// add course if exists
		if (!empty($course)) {
			$q .= 'AND notes.course = $'.$params.' ';
			$params++;
			$aparam[] = $course;
		}

		// add professor if exists
		if (!empty($professor)) {
			$q .= 'AND cp.professor = $'.$params.' ';
			$params++;
			$aparam[] = $professor;
		}

		// add term/year if exist
		if (!empty($term)) {
			$q .= 'AND notes.term = $'.$params.' ';
			$params++;
			$aparam[] = $term;
			$q .= 'AND notes.year = $'.$params.' ';
			$aparam[] = $year;
		}
		$q .= 'ORDER BY notes.title;';

		$result = pg_query_params($q, $aparam);
		return pg_fetch_all_objects($result);
	}

	/* Return notes the current user is selling */
	public static function get_selling_notes($user, $limit=NULL) {
		$q = 'SELECT 
		notes.*, courses.subject, courses.number, users.alias, professors.fname, professors.lname,
		(SELECT AVG(ratings.rating) avgrating FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = notes.id)
		FROM notes
		INNER JOIN courses on courses.id = notes.course
		INNER JOIN course_professor cp on cp.course = courses.id
		INNER JOIN users on notes.author = users.id
		INNER JOIN professors on cp.professor = professors.id
		WHERE notes.author = $1 
		AND (notes.deleted = false OR notes.deleted is null) ';
		if (!empty($limit)) {
			$q .= 'LIMIT $2';
			$result = pg_query_params($q, array($user, $limit));
		} else {
			$result = pg_query_params($q, array($user));
		}
		return pg_fetch_all_objects($result);
	}

	/* Return notes the current user has purchased */
	public static function get_purchased_notes($user, $limit=NULL){
		$q = 'SELECT 
		notes.*, courses.subject, courses.number, users.alias, professors.fname, professors.lname,
		(SELECT AVG(ratings.rating) avgrating FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = notes.id)
		FROM transactions
		inner join notes on transactions.note = notes.id
		INNER JOIN courses on courses.id = notes.course
		INNER JOIN course_professor cp on cp.course = courses.id
		INNER JOIN users on notes.author = users.id
		INNER JOIN professors on cp.professor = professors.id
		WHERE transactions.buyer = $1
		AND transactions.completed = TRUE ';
		if (!empty($limit)) {
			$q .= 'LIMIT $2';
			$result = pg_query_params($q, array($user, $limit));
		} else {
			$result = pg_query_params($q, array($user));
		}
		return pg_fetch_all_objects($result);
	}

	/* Check if user has purchased the note */
	public static function own_note($user, $note) {
		$q = "SELECT EXISTS (SELECT id FROM transactions WHERE buyer=$1 AND note=$2 AND completed='t') found";
		$result = pg_query_params($q, array($user, $note));
		return pg_fetch_object($result);
	}

	/* Check if user has rated a specific note already */
	public static function get_user_note_rating($user, $note) {
		$q = "SELECT ratings.* FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = $1 AND author = $2";
		$result = pg_query_params($q, array($note, $user));
		return pg_fetch_object($result);
	}

	/* Get avg user rating for a given note */
	public static function get_note_rating($note){
		$q = "SELECT AVG(ratings.rating) avgrating FROM ratings INNER JOIN note_ratings ON ratings.id = note_ratings.rating WHERE note = $1";
		$result = pg_query_params($q, array($note));
		return pg_fetch_object($result);
	}

	/* Filter schools from database */
	public static function filter_schools($course=null, $professor=null) {
		$q = 'SELECT s.id, s.name newtitle FROM schools s';
		if (!empty($course) && empty($professor)) {
			$q .= ' INNER JOIN course_school cs ON cs.school = s.id';
			$q .= ' WHERE cs.course = $1';
			$q .= ' ORDER BY s.name';
			$result = pg_query_params($q, array($course));
		}
		else if (!empty($course) && !empty($professor)) {
			$q .= ' INNER JOIN course_school cs ON cs.school = s.id';
			$q .= ' INNER JOIN professors p ON p.school = s.id';
			$q .= ' WHERE cs.course = $1 AND p.id = $2';
			$q .= ' ORDER BY s.name';
			$result = pg_query_params($q, array($course, $professor));
		}
		else if (empty($course) && !empty($professor)) {
			$q .= ' INNER JOIN professors p ON p.school = s.id';
			$q .= ' WHERE p.id = $1';
			$q .= ' ORDER BY s.name';
			$result = pg_query_params($q, array($professor));
		}
		return pg_fetch_all_objects($result);
	}

	/* Filter courses from database */
	public static function filter_courses($school=null, $professor=null) {
		$q = "SELECT c.id, c.subject || ' ' || c.number newtitle FROM courses c";
		if (!empty($school) && empty($professor)) {
			$q .= ' INNER JOIN course_school cs ON cs.course = c.id';
			$q .= ' WHERE cs.school = $1';
			$q .= ' ORDER BY c.subject, c.number';
			$result = pg_query_params($q, array($school));
		}
		else if (!empty($school) && !empty($professor)) {
			$q .= ' INNER JOIN course_school cs ON cs.course = c.id';
			$q .= ' INNER JOIN course_professor cp ON cp.school = c.id';
			$q .= ' WHERE cs.school = $1 AND cp.professor = $2';
			$q .= ' ORDER BY c.subject, c.number';
			$result = pg_query_params($q, array($school, $course));
		}
		else if (empty($school) && !empty($professor)) {
			$q .= ' INNER JOIN course_professor cp ON cp.school = c.id';
			$q .= ' WHERE cp.professor = $1';
			$q .= ' ORDER BY c.subject, c.number';
			$result = pg_query_params($q, array($course));
		}
		return pg_fetch_all_objects($result);
	}

	/* Filter professors from the database */
	public static function filter_professors($school=null, $course=null) {
		$q = "SELECT p.id, p.lname || ', ' || p.fname newtitle FROM professors p";
		if (!empty($school) && empty($course)) {
			$q .= ' WHERE p.school = $1';
			$q .= ' ORDER BY p.lname, p.fname';
			$result = pg_query_params($q, array($school));
		}
		else if (!empty($school) && !empty($course)) {
			$q .= ' INNER JOIN course_professor cp ON cp.professor = p.id';
			$q .= ' WHERE p.school = $1 AND cp.course = $2';
			$q .= ' ORDER BY p.lname, p.fname';
			$result = pg_query_params($q, array($school, $course));
		}
		else if (empty($school) && !empty($course)) {
			$q .= ' INNER JOIN course_professor cp ON cp.professor = p.id';
			$q .= ' WHERE cp.course = $1';
			$q .= ' ORDER BY p.lname, p.fname';
			$result = pg_query_params($q, array($course));
		}
		return pg_fetch_all_objects($result);
	}
}
