<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once( $RootDir.'/include/openqrm-database-functions.php');
global $USER_INFO_TABLE;

require_once( $RootDir.'/class/user.class.php');
require_once ($RootDir.'/class/event.class.php');

function set_env() {
	$OPENQRM_USER = new user($_SERVER['PHP_AUTH_USER']);
	$OPENQRM_USER->set_user();
	$GLOBALS['OPENQRM_USER'] = $OPENQRM_USER;
	
	define('OPENQRM_USER_NAME', $OPENQRM_USER->name);
	define('OPENQRM_USER_ROLE_NAME', $OPENQRM_USER->role);
}

set_env();

?>