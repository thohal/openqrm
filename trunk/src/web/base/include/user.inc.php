<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once( $RootDir.'include/openqrm-database-functions.php');
require_once( $RootDir.'class/user.class.php');

function set_env() {
	$OPENQRM_USER = new user($_SERVER['PHP_AUTH_USER']);
	$OPENQRM_USER->set_user();
	$GLOBALS['OPENQRM_USER'] = $OPENQRM_USER;
	
	define('OPENQRM_USER_NAME', $user->name['value']);
	define('OPENQRM_USER_ROLE_ID', $user->role['value']);
	define('OPENQRM_USER_ROLE_NAME', $user->role['label']);
}

set_env();

?>