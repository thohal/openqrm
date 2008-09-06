<?php

// This class represents a resource in openQRM (physical hardware or virtual machine)


$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BootServiceDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/boot-service/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

global $RESOURCE_INFO_TABLE;
$RESOURCE_TIME_OUT=120;
global $RESOURCE_TIME_OUT;
$event = new event();
global $event;

class resource {

var $id = '';
var $localboot = '';
var $kernel = '';
var $kernelid = '';
var $image = '';
var $imageid = '';
var $openqrmserver = '';
var $basedir = '';
var $applianceid = '';
var $ip = '';
var $subnet = '';
var $broadcast = '';
var $network = '';
var $mac = '';
var $uptime = '';
var $cpunumber = '';
var $cpuspeed = '';
var $cpumodel = '';
var $memtotal = '';
var $memused = '';
var $swaptotal = '';
var $swapused = '';
var $hostname = '';
var $load = '';
var $execdport = '';
var $senddelay = '';
var $capabilities = '';
var $lastgood = '';
var $state = '';
var $event = '';

// ---------------------------------------------------------------------------------
// methods to create an instance of a resource object filled from the db
// ---------------------------------------------------------------------------------

// returns a resource from the db selected by id, mac or ip
function get_instance($id, $mac, $ip) {
	global $RESOURCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_id=$id");
	} else if ("$mac" != "") {
		$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_mac='$mac'");
	} else if ("$ip" != "") {
		$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_ip='$ip'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		exit(-1);
	}
	foreach ($resource_array as $index => $resource) {
		$this->id = $resource["resource_id"];
		$this->localboot = $resource["resource_localboot"];
		$this->kernel = $resource["resource_kernel"];
		$this->kernelid = $resource["resource_kernelid"];
		$this->image = $resource["resource_image"];
		$this->imageid = $resource["resource_imageid"];
		$this->openqrmserver = $resource["resource_openqrmserver"];
		$this->basedir = $resource["resource_basedir"];
		$this->applianceid = $resource["resource_applianceid"];
		$this->ip = $resource["resource_ip"];
		$this->subnet = $resource["resource_subnet"];
		$this->broadcast = $resource["resource_broadcast"];
		$this->network = $resource["resource_network"];
		$this->mac = $resource["resource_mac"];
		$this->uptime = $resource["resource_uptime"];
		$this->cpunumber = $resource["resource_cpunumber"];
		$this->cpuspeed = $resource["resource_cpuspeed"];
		$this->cpumodel = $resource["resource_cpumodel"];
		$this->memtotal = $resource["resource_memtotal"];
		$this->memused = $resource["resource_memused"];
		$this->swaptotal = $resource["resource_swaptotal"];
		$this->swapused = $resource["resource_swapused"];
		$this->hostname = $resource["resource_hostname"];
		$this->load = $resource["resource_load"];
		$this->execdport = $resource["resource_execdport"];
		$this->senddelay = $resource["resource_senddelay"];
		$this->capabilities = $resource["resource_capabilities"];
		$this->lastgood = $resource["resource_lastgood"];
		$this->state = $resource["resource_state"];
		$this->event = $resource["resource_event"];
	}
	return $this;
}

// returns a resource from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "");
	return $this;
}

// returns a resource from the db selected by ip
function get_instance_by_ip($ip) {
	$this->get_instance("", "", $ip);
	return $this;
}

// returns a resource from the db selected by mac
function get_instance_by_mac($mac) {
	$this->get_instance("", $mac, "");
	return $this;
}



// ---------------------------------------------------------------------------------
// getter + setter
// ---------------------------------------------------------------------------------

function get_id() {
	return $this->id;
}

function set_id($id) {
	$this->id = $id;
}



// ---------------------------------------------------------------------------------
// general resource methods
// ---------------------------------------------------------------------------------

// checks if a resource exists in the database
function exists($mac_address) {
	global $RESOURCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select resource_id from $RESOURCE_INFO_TABLE where resource_mac='$mac_address'");
	if (!$rs)
		$event->log("exists", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return false;
	} else {
		return true;
	}
}


// checks if given resource id is free in the db
function is_id_free($resource_id) {
	global $RESOURCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select resource_id from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
	if (!$rs)
		$event->log("exists", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}



// adds resource to the database
function add($resource_fields) {
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $OPENQRM_RESOURCE_BASE_DIR;
	global $event;
	global $RootDir;
	$openqrm_server = new openqrm_server();
	$OPENQRM_SERVER_IP_ADDRESS = $openqrm_server->get_ip_address();
	if (!is_array($resource_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Resource_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	# set defaults
	$resource_fields["resource_basedir"]=$OPENQRM_RESOURCE_BASE_DIR;
	$resource_fields["resource_openqrmserver"]=$OPENQRM_SERVER_IP_ADDRESS;
	$resource_fields["resource_execdport"]=$OPENQRM_EXEC_PORT;
	$resource_fields["resource_kernel"]='default';
	$resource_fields["resource_kernelid"]='1';
	$resource_fields["resource_image"]='idle';
	$resource_fields["resource_imageid"]='1';
	$resource_fields["resource_senddelay"]=30;
	$resource_fields["resource_lastgood"]=$_SERVER['REQUEST_TIME'];

	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($RESOURCE_INFO_TABLE, $resource_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Failed adding new resource to database", "", "", 0, 0, 0);
	}
	// new resource hook
	$plugin = new plugin();
	$enabled_plugins = $plugin->enabled();
	foreach ($enabled_plugins as $index => $plugin_name) {
		$plugin_new_resource_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-resource-hook.php";
		if (file_exists($plugin_new_resource_hook)) {
			$event->log("check_all_states", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found plugin $plugin_name handling new-resource event.", "", "", 0, 0, $resource_id);
			require_once "$plugin_new_resource_hook";
			$resource_function="openqrm_"."$plugin_name"."_resource";
			$resource_function("add", $resource_fields);
		}
	}

}

// removes resource from the database
function remove($resource_id, $resource_mac) {
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $RootDir;
	global $event;
	$openqrm_server = new openqrm_server();
	$OPENQRM_SERVER_IP_ADDRESS = $openqrm_server->get_ip_address();

	// remove resource hook
	$plugin = new plugin();
	$enabled_plugins = $plugin->enabled();
	foreach ($enabled_plugins as $index => $plugin_name) {
		$plugin_new_resource_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-resource-hook.php";
		if (file_exists($plugin_new_resource_hook)) {
			$event->log("check_all_states", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found plugin $plugin_name handling remove-resource event.", "", "", 0, 0, $resource_id);
			require_once "$plugin_new_resource_hook";
			$resource_fields["resource_id"]=$resource_id;
			$resource_fields["resource_mac"]=$resource_mac;
			$resource_function="openqrm_"."$plugin_name"."_resource";
			$resource_function("remove", $resource_fields);
		}
	}

	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $RESOURCE_INFO_TABLE where resource_id=$resource_id and resource_mac='$resource_mac'");
}


// assigns a kernel and fs-image to a resource
function assign($resource_id, $resource_kernelid, $resource_kernel, $resource_imageid, $resource_image) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $RESOURCE_INFO_TABLE set
		 resource_kernelid=$resource_kernelid,
		 resource_kernel='$resource_kernel',
		 resource_imageid=$resource_imageid,
		 resource_image='$resource_image' where resource_id=$resource_id");
}



// set a resource to net- or local boot
// resource_localboot = 0 -> netboot / 1 -> localboot
function set_localboot($resource_id, $resource_localboot) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $RESOURCE_INFO_TABLE set resource_localboot=$resource_localboot where resource_id=$resource_id");
}


// displays resource parameter for resource_id
function get_parameter($resource_id) {
	global $RESOURCE_INFO_TABLE;
	global $KERNEL_INFO_TABLE;
	global $IMAGE_INFO_TABLE;
	global $APPLIANCE_INFO_TABLE;
	global $BootServiceDir;
	global $event;
	$db=openqrm_get_db_connection();
	// resource parameter
	$recordSet = &$db->Execute("select * from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
	if (!$recordSet)
		$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$recordSet->EOF) {
		array_walk($recordSet->fields, 'print_array');
		$image_id=$recordSet->fields["resource_imageid"];
		$kernel_id=$recordSet->fields["resource_kernelid"];
		$recordSet->MoveNext();
	}
	$recordSet->Close();
	// kernel-parameter
	$recordSet = &$db->Execute("select * from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
	if (!$recordSet)
		$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$recordSet->EOF) {
		array_walk($recordSet->fields, 'print_array');
		$recordSet->MoveNext();
	}
	$recordSet->Close();
	// image-parameter
	$recordSet = &$db->Execute("select * from $IMAGE_INFO_TABLE where image_id=$image_id");
	if (!$recordSet)
		$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$recordSet->EOF) {
		array_walk($recordSet->fields, 'print_array');
		$image_storageid=$recordSet->fields["image_storageid"];
		$recordSet->MoveNext();
	}
	$recordSet->Close();
	// storage parameter
	if (strlen($image_storageid)) {
		$storage = new storage();
		$storage->get_instance_by_id($image_storageid);
		$storage_resource_id = $storage->resource_id;
		$recordSet = &$db->Execute("select resource_ip from $RESOURCE_INFO_TABLE where resource_id=$storage_resource_id");
		if (!$recordSet)
			$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$recordSet->EOF) {
			//array_walk($recordSet->fields, 'print_array');
			$image_storage_server_ip=$recordSet->fields['resource_ip'];
			$recordSet->MoveNext();
		}
		$recordSet->Close();
		echo "image_storage_server_ip=$image_storage_server_ip\n";
	}
	// appliance parameter
	$recordSet = &$db->Execute("select * from $APPLIANCE_INFO_TABLE where appliance_resources=$resource_id and appliance_stoptime='0'");
	if (!$recordSet)
		$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$recordSet->EOF) {
		array_walk($recordSet->fields, 'print_array');
		$recordSet->MoveNext();
	}
	$recordSet->Close();


	$db->Close();

	$plugin = new plugin();
	$enabled_plugins = $plugin->enabled();

	foreach ($enabled_plugins as $index => $plugin_name) {
		$plugin_list = "$plugin_list$plugin_name ";
		// add to list of boot-services only if boot-services for the resource exists
		$plugin_boot_service = "$BootServiceDir/boot-service-$plugin_name.tgz";
		if (file_exists($plugin_boot_service)) {
			$boot_service_list = "$boot_service_list$plugin_name ";
		}
	}
	echo "openqrm_plugins=\"$plugin_list\"\n";
	echo "openqrm_boot_services=\"$boot_service_list\"\n";

}

function get_parameter_array($resource_id) {
	global $RESOURCE_INFO_TABLE;
    $db = openqrm_get_db_connection();
	$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
	return $resource_array;
}

function get_list() {
	global $RESOURCE_INFO_TABLE;
	global $event;
	$resource_list = array();
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_id, resource_ip, resource_state from $RESOURCE_INFO_TABLE");
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$resource_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $resource_list;
}



function update_info($resource_id, $resource_fields) {
	global $RESOURCE_INFO_TABLE;
	global $event;
	if (! is_array($resource_fields)) {
		$event->log("update_info", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Unable to update resource $resource_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($resource_fields["resource_id"]);
	$result = $db->AutoExecute($RESOURCE_INFO_TABLE, $resource_fields, 'UPDATE', "resource_id = $resource_id");
	if (! $result) {
		$event->log("update_info", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Failed updating resource $resource_id", "", "", 0, 0, 0);
	}
}

function update_status($resource_id, $resource_state, $resource_event) {
	global $RESOURCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$query = "update $RESOURCE_INFO_TABLE set
			resource_state='$resource_state',
			resource_event='$resource_event'
			where resource_id=$resource_id";
	$rs = $db->Execute("$query");
}



// function to send a command to a resource by resource_ip
function send_command($resource_ip, $resource_command) {
	global $OPENQRM_EXEC_PORT;
	global $event;
	$fp = fsockopen($resource_ip, $OPENQRM_EXEC_PORT, $errno, $errstr, 30);
	if(!$fp) {
		$event->log("send_command", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Could not send the command to resource $resource_ip", "", "", 0, 0, 0);
		$event->log("send_command", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "$errstr ($errno)", "", "", 0, 0, 0);
	} else {
		fputs($fp,"$resource_command");
		fclose($fp);
	}
}



// returns the number of managed resource
function get_count($which) {
	global $RESOURCE_INFO_TABLE;
	global $event;
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
		$event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}


// generates a mac address
function generate_mac() {
	$CMD="(date; cat /proc/interrupts) | md5sum | sed -r 's/^(.{10}).*\$/\\1/; s/([0-9a-f]{2})/\\1:/g; s/:\$//;' | tr '[:lower:]:' '[:upper:]-' | sed -e 's/-/:/g'";
	$GEN_MAC=exec($CMD);
	$GEN_MAC="00:".$GEN_MAC;
	$this->mac = $GEN_MAC;
}



// check when resources last send their statistics
// update state in case of timeout
function check_all_states() {
	global $RESOURCE_INFO_TABLE;
	global $RESOURCE_TIME_OUT;
	global $RootDir;
	global $event;
	$resource_list = array();
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_id, resource_lastgood, resource_state from $RESOURCE_INFO_TABLE");
	if (!$rs)
		$event->log("check_all_states", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$resource_id=$rs->fields['resource_id'];
		$resource_lastgood=$rs->fields['resource_lastgood'];
		$resource_state=$rs->fields['resource_state'];
		$check_time=$_SERVER['REQUEST_TIME'];

		// resolve errors for all active resources
		if (("$resource_state" == "active") && ($resource_id != 0)) {
			if (($check_time - $resource_lastgood) < $RESOURCE_TIME_OUT) {
				// resolve error event
				$event->resolve_by_resource("check_all_states", $resource_id);
			}
		}

		// check for statistics (errors) for all resources which are not offline
		// exclude manual added resources from the check !
		if (("$resource_state" != "off") && ("$resource_lastgood" != "-1")) {
			if (($check_time - $resource_lastgood) > $RESOURCE_TIME_OUT) {
				$resource_fields=array();
				$resource_fields["resource_state"]="error";
				$resource_error = new resource();
				$resource_error->update_info($resource_id, $resource_fields);
				// log error event
				$event->log("check_all_states", $_SERVER['REQUEST_TIME'], 1, "resource.class.php", "Resource $resource_id is in error state", "", "", 0, 0, $resource_id);

				// check for plugin which may want to handle the error event
				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				foreach ($enabled_plugins as $index => $plugin_name) {
					$plugin_ha_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-ha-hook.php";
					if (file_exists($plugin_ha_hook)) {
						$event->log("check_all_states", $_SERVER['REQUEST_TIME'], 1, "resource.class.php", "Found $plugin_name handling the resource error.", "", "", 0, 0, $resource_id);
						require_once "$plugin_ha_hook";
						$ha_function="openqrm_"."$plugin_name"."_ha_hook";
						$ha_function($resource_id);
					}
				}
			}
		}

		$rs->MoveNext();
	}
}


// displays the resource-overview
function display_overview($offset, $limit, $sort, $order) {
	global $RESOURCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $RESOURCE_INFO_TABLE order by $sort $order", $limit, $offset);
	$resource_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($resource_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $resource_array;
}





// ---------------------------------------------------------------------------------

}

?>

