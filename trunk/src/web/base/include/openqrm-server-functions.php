<?php

require_once "openqrm-database-functions.php";
global $RESOURCE_INFO_TABLE;

// ######################## server functions #############################


// helper to get the ip of the openQRM-server
function openqrm_server_get_ip_address() {
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




?>