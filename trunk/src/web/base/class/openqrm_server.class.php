<?php

// This class represents the openQRM-server

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
global $RESOURCE_INFO_TABLE;

class openqrm_server {

var $id = '';


// ---------------------------------------------------------------------------------
// general server methods
// ---------------------------------------------------------------------------------

// returns the ip of the openQRM-server
function get_ip_address() {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_openqrmserver from $RESOURCE_INFO_TABLE where resource_id='0'");
	if (!$rs)
		print $db->ErrorMsg();
	else
	while (!$rs->EOF) {
		$resource_openqrmserver=$rs->fields["resource_openqrmserver"];
		$rs->MoveNext();
	}
	return $resource_openqrmserver;
}


// function to send a command to the openQRM-server
function send_command($server_command) {
	global $OPENQRM_EXEC_PORT;
	global $OPENQRM_SERVER_IP_ADDRESS;
	$fp = fsockopen($OPENQRM_SERVER_IP_ADDRESS, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
	if(!$fp) {
		echo "ERROR: Could not connect to the openQRM-Server!<br>";
		echo "ERROR: $errstr ($errno)<br>";
		exit();
	}
	fputs($fp,"$server_command");
	fclose($fp);
}



// ---------------------------------------------------------------------------------

}

?>