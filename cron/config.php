<?php

define( 'DB_HOST', 'localhost' );
define( 'DB_NAME', 'dtytdevsrv_rifas_dev' );
define( 'DB_USER', 'dtytdevsrv_rifasuser' );
define( 'DB_PASS', 'jZgctHK5qB' );

function create_connection() {
	$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );
	if( $mysqli->connect_errno ) {
		return false;
	}
	$mysqli->set_charset("utf8");
	return $mysqli;
}

?>