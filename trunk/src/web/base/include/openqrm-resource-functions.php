<?php

require_once "openqrm-database-functions.php";
require_once "openqrm-server-functions.php";

global $RESOURCE_INFO_TABLE;

// ######################## resource functions #############################

// checks if a resource exists in the database
function openqrm_resource_exists($mac_address) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select resource_id from $RESOURCE_INFO_TABLE where resource_mac='$mac_address'");
	if ($rs->EOF) {
		return false;
	} else {
		return true;
	}
}

// get next free resource-id
function openqrm_get_next_resource_id() {
	global $RESOURCE_INFO_TABLE;
	$next_free_resource_id=0;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->Execute("select resource_id from $RESOURCE_INFO_TABLE");
	if (!$recordSet)
        print $db->ErrorMsg();
    else
	while (!$recordSet->EOF) {
		if ($recordSet->fields["resource_id"] != $next_free_resource_id) {
			if (openqrm_is_resource_id_free($next_free_resource_id)) {
				return $next_free_resource_id;
			}
		}
		$next_free_resource_id++;
		$recordSet->MoveNext();
	}
    $recordSet->Close();
    $db->Close();
    return $next_free_resource_id;
}



// checks if given resource id is free in the db
function openqrm_is_resource_id_free($resource_id) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select resource_id from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
	if (!$rs)
		print $db->ErrorMsg();
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds resource to the database
function openqrm_add_resource($resource_id, $resource_mac, $resource_ip) {
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $OPENQRM_RESOURCE_BASE_DIR;
	$OPENQRM_SERVER_IP_ADDRESS=openqrm_server_get_ip_address();
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("insert into $RESOURCE_INFO_TABLE (resource_id, resource_localboot, resource_kernel, resource_kernelid, resource_image, resource_imageid, resource_openqrmserver, resource_basedir, resource_serverid, resource_ip, resource_subnet, resource_broadcast, resource_network, resource_mac, resource_uptime, resource_cpunumber, resource_cpuspeed, resource_cpumodel, resource_memtotal, resource_memused, resource_swaptotal, resource_swapused, resource_hostname, resource_load, resource_execdport, resource_senddelay, resource_state, resource_event) values ($resource_id, 0, 'default', 1, 'idle', 1, '$OPENQRM_SERVER_IP_ADDRESS', '$OPENQRM_RESOURCE_BASE_DIR', 1, '$resource_ip', '', '', '', '$resource_mac', 0, 0, 0, '0', 0, 0, 0, 0, 'idle', 0, $OPENQRM_EXEC_PORT, 30, 'booting', 'detected')");
}

// removes resource from the database
function openqrm_remove_resource($resource_id, $resource_mac) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $RESOURCE_INFO_TABLE where resource_id=$resource_id and resource_mac='$resource_mac'");
}


// assigns a kernel and fs-image to a resource
function openqrm_assign_resource($resource_id, $resource_kernel, $resource_kernelid, $resource_image, $resource_imageid, $resource_serverid) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	if ("$resource_imageid" == "1") {
		// idle
		$rs = $db->Execute("update $RESOURCE_INFO_TABLE set
			 resource_kernel='$resource_kernel',
			 resource_kernelid=$resource_kernelid,
			 resource_image='$resource_image',
			 resource_imageid=$resource_imageid,
			 resource_serverid=1 where resource_id=$resource_id");
	} else {
		$rs = $db->Execute("update $RESOURCE_INFO_TABLE set
			resource_kernel='$resource_kernel',
			resource_kernelid=$resource_kernelid,
			resource_image='$resource_image',
			resource_imageid=$resource_imageid,
			resource_serverid=$resource_serverid where resource_id=$resource_id");
	}
}



// set a resource to net- or local boot
// resource_localboot = 0 -> netboot / 1 -> localboot
function openqrm_set_resource_localboot($resource_id, $resource_localboot) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $RESOURCE_INFO_TABLE set resource_localboot=$resource_localboot where resource_id=$resource_id");
}


// displays resource parameter for resource_id
function openqrm_get_resource_parameter($resource_id) {
	global $RESOURCE_INFO_TABLE;
	global $KERNEL_INFO_TABLE;
	global $IMAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	// resource parameter
	$recordSet = &$db->Execute("select resource_id, resource_localboot, resource_kernel, resource_kernelid, resource_image, resource_imageid, resource_openqrmserver, resource_basedir, resource_serverid, resource_ip, resource_subnet, resource_broadcast, resource_network, resource_mac, resource_execdport, resource_senddelay from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
	if (!$recordSet)
		print $db->ErrorMsg();
	else
	while (!$recordSet->EOF) {
		array_walk($recordSet->fields, 'print_array');
		$image_id=$recordSet->fields["resource_imageid"];
		$kernel_id=$recordSet->fields["resource_kernelid"];
		$recordSet->MoveNext();
	}
	$recordSet->Close();
	// kernel-parameter
/*	$recordSet = &$db->Execute("select * from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
	if (!$recordSet)
		print $db->ErrorMsg();
	else
	while (!$recordSet->EOF) {
		array_walk($recordSet->fields, 'print_array');
		$recordSet->MoveNext();
	}
	$recordSet->Close();
	// image-parameter
	$recordSet = &$db->Execute("select * from $IMAGE_INFO_TABLE where image_id=$image_id");
	if (!$recordSet)
		print $db->ErrorMsg();
	else
	while (!$recordSet->EOF) {
		array_walk($recordSet->fields, 'print_array');
		$recordSet->MoveNext();
	}
	$recordSet->Close();
	$db->Close();
*/

	// enabled plugins
	// TODO


}

function openqrm_get_resource_parameter_array($resource_id) {
	global $RESOURCE_INFO_TABLE;
    $db = openqrm_get_db_connection();
	$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
	return $resource_array;
}

function openqrm_get_resource_list() {
	global $RESOURCE_INFO_TABLE;
	$resource_list = array();
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_id, resource_ip, resource_state from $RESOURCE_INFO_TABLE");
	if (!$rs)
		print $db->ErrorMsg();
	else
	while (!$rs->EOF) {
		$resource_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $resource_list;
}



function openqrm_update_resource_info($resource_id, $resource_fields) {
	global $RESOURCE_INFO_TABLE;
	if ($resource_id < 0 || ! is_array($resource_fields)) {
		print("Unable to update resource $resource_id");
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($resource_fields["resource_id"]);
	$result = $db->AutoExecute($RESOURCE_INFO_TABLE, $resource_fields, 'UPDATE', "resource_id = $resource_id");
	if (! $result) {
		print("Failed updating resource $resource_id");
	}

	//$resource_uptime, $resource_cpu_number, $resource_cpu_speed, $resource_cpu_model, $resource_mem_total, $resource_mem_used, $resource_swap_total, $resource_swap_used, $resource_hostname, $resource_cpu_load, $resource_state, $resource_event

}

function openqrm_update_resource_status($resource_id, $resource_state, $resource_event) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$query = "update $RESOURCE_INFO_TABLE set
			resource_state='$resource_state',
			resource_event='$resource_event'
			where resource_id=$resource_id";
	$rs = $db->Execute("$query");
}



// helper to get the ip of a resource by resource_id
function openqrm_get_resource_ip_by_resource_id($resource_id) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_ip from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
	if (!$rs)
		print $db->ErrorMsg();
	else
	while (!$rs->EOF) {
		$resource_ip=$rs->fields["resource_ip"];
		$rs->MoveNext();
	}
	return $resource_ip;
}


// helper to get the mac of a resource by resource_id
function openqrm_get_resource_mac_by_resource_id($resource_id) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_mac from $RESOURCE_INFO_TABLE where resource_id='$resource_id'");
	if (!$rs)
		print $db->ErrorMsg();
	else
	while (!$rs->EOF) {
		$resource_mac=$rs->fields["resource_mac"];
		$rs->MoveNext();
	}
	return $resource_mac;
}


// helper to get the id of a resource by resource_mac
function openqrm_get_resource_id_by_resource_mac($resource_mac) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_id from $RESOURCE_INFO_TABLE where resource_mac='$resource_mac'");
	if (!$rs)
		print $db->ErrorMsg();
	else
	while (!$rs->EOF) {
		$resource_id=$rs->fields["resource_id"];
		$rs->MoveNext();
	}
	return $resource_id;
}


// function to send a command to a resource by resource_ip
function openqrm_send_command_to_resource($resource_ip, $resource_command) {
	global $OPENQRM_EXEC_PORT;
	$fp = fsockopen($resource_ip, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
	if(!$fp) {
		echo "$errstr ($errno)<br>";
		exit();
	}
	fputs($fp,"$resource_command");
	fclose($fp);
}

// function to send a command to the openQRM-server
function openqrm_send_command_to_server($server_command) {
	global $OPENQRM_EXEC_PORT;
	global $OPENQRM_SERVER_IP_ADDRESS;
	$fp = fsockopen($OPENQRM_SERVER_IP_ADDRESS, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
	if(!$fp) {
		echo "$errstr ($errno)<br>";
		exit();
	}
	fputs($fp,"$server_command");
	fclose($fp);
}


// returns the number of managed resource
function openqrm_get_resource_count($which) {
	global $RESOURCE_INFO_TABLE;
	$count = 0;
	$db=openqrm_get_db_connection();

    $sql = "select count(resource_id) as num from $RESOURCE_INFO_TABLE where resource_id!=0";
	switch($which) {
		case 'all':
			break;
		case 'online':
			$sql .= " and resource_state='active'";
			break;
		case 'offline':
			$sql .= " and resource_state!='active'";
			break;
	}
	$rs = $db->Execute($sql);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




?>
