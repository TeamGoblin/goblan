<?php
/**
 * Custom Global Functions 
 */


/* Include PHP code into a variable */
function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    return false;
}

/* Postgres function to get result as a list of objects */
function pg_fetch_all_objects($resource) {
	$return = array();
	while ($row = pg_fetch_object($resource)) {
		$return[] = $row;
	}
	return $return;
}
