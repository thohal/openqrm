<?php

// This class represents a applicance managed by openQRM
// The applicance abstrations consists of the combination of 
// - 1 boot-image (kernel.class)
// - 1 (or more) server-filesystem/rootfs (image.class)
// - requirements (cpu-number, cpu-speed, memory needs, etc)
// - configuration (clustered, high-available, deployment type, etc)
// - available and required resources (resource.class)


$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";

global $APPLIANCE_INFO_TABLE;
$event = new event();
global $event;

class appliance {

var $id = '';
var $name = '';
var $kernelid = '';
var $imageid = '';
var $starttime = '';
var $stoptime = '';
var $cpunumber = '';
var $cpuspeed = '';
var $cpumodel = '';
var $memtotal = '';
var $swaptotal = '';
var $capabilities = '';
var $cluster = '';
var $ssi = '';
var $resources = '';
var $highavailable = '';
var $virtual = '';
var $virtualization = '';
var $virtualization_host = '';
var $state = '';
var $comment = '';
var $event = '';



// ---------------------------------------------------------------------------------
// methods to create an instance of an appliance object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $name) {
	global $APPLIANCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$appliance_array = &$db->Execute("select * from $APPLIANCE_INFO_TABLE where appliance_id=$id");
	} else if ("$name" != "") {
		$appliance_array = &$db->Execute("select * from $APPLIANCE_INFO_TABLE where appliance_name='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		exit(-1);
	}
	foreach ($appliance_array as $index => $appliance) {
		$this->id = $appliance["appliance_id"];
		$this->name = $appliance["appliance_name"];
		$this->kernelid = $appliance["appliance_kernelid"];
		$this->imageid = $appliance["appliance_imageid"];
		$this->starttime = $appliance["appliance_starttime"];
		$this->stoptime = $appliance["appliance_stoptime"];
		$this->cpunumber = $appliance["appliance_cpunumber"];
		$this->cpuspeed = $appliance["appliance_cpuspeed"];
		$this->cpumodel = $appliance["appliance_cpumodel"];
		$this->memtotal = $appliance["appliance_memtotal"];
		$this->swaptotal = $appliance["appliance_swaptotal"];
		$this->capabilities = $appliance["appliance_capabilities"];
		$this->cluster = $appliance["appliance_cluster"];
		$this->ssi = $appliance["appliance_ssi"];
		$this->resources = $appliance["appliance_resources"];
		$this->highavailable = $appliance["appliance_highavailable"];
		$this->virtual = $appliance["appliance_virtual"];
		$this->virtualization = $appliance["appliance_virtualization"];
		$this->virtualization_host = $appliance["appliance_virtualization_host"];
		$this->state = $appliance["appliance_state"];
		$this->comment = $appliance["appliance_comment"];
		$this->event = $appliance["appliance_event"];
	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an appliance from the db selected by iname
function get_instance_by_name($name) {
	$this->get_instance("", $name);
	return $this;
}


// ---------------------------------------------------------------------------------
// general appliance methods
// ---------------------------------------------------------------------------------




// checks if given appliance id is free in the db
function is_id_free($appliance_id) {
	global $APPLIANCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select appliance_id from $APPLIANCE_INFO_TABLE where appliance_id=$appliance_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds appliance to the database
function add($appliance_fields) {
	global $APPLIANCE_INFO_TABLE;
	global $event;
	if (!is_array($appliance_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Appliance_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($APPLIANCE_INFO_TABLE, $appliance_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Failed adding new appliance to database", "", "", 0, 0, 0);
	}
}



// updates appliance in the database
function update($appliance_id, $appliance_fields) {
	global $APPLIANCE_INFO_TABLE;
	global $event;
	if ($appliance_id < 0 || ! is_array($appliance_fields)) {
		$event->log("update", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Unable to update appliance $appliance_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($appliance_fields["appliance_id"]);
	$result = $db->AutoExecute($APPLIANCE_INFO_TABLE, $appliance_fields, 'UPDATE', "appliance_id = $appliance_id");
	if (! $result) {
		$event->log("update", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Failed updating appliance $appliance_id", "", "", 0, 0, 0);
	}
}

// removes appliance from the database
function remove($appliance_id) {
	global $APPLIANCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $APPLIANCE_INFO_TABLE where appliance_id=$appliance_id");
}

// removes appliance from the database by appliance_name
function remove_by_name($appliance_name) {
	global $APPLIANCE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $APPLIANCE_INFO_TABLE where appliance_name='$appliance_name'");
}



// starts an appliance -> assigns it to a resource
function start() {
	$resource = new resource();
	$resource->get_instance_by_id($this->resources);
	$kernel = new kernel();
	$kernel->get_instance_by_id($this->kernelid);
	$image = new image();
	$image->get_instance_by_id($this->imageid);
	// assign + reboot resource
	$resource->assign($resource->id, $kernel->id, $kernel->name, $image->id, $image->name);
	$resource->send_command("$resource->ip", "reboot");
}


// stops an appliance -> de-assigns it to idle
function stop() {
	$resource = new resource();
	$resource->get_instance_by_id($this->resources);
	$resource->assign($resource->id, "1", "default", "1", "idle");
	$resource->send_command("$resource->ip", "reboot");
}



// returns appliance name by appliance_id
function get_name($appliance_id) {
	global $APPLIANCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$appliance_set = &$db->Execute("select appliance_name from $APPLIANCE_INFO_TABLE where appliance_id=$appliance_id");
	if (!$appliance_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$appliance_set->EOF) {
			return $appliance_set->fields["appliance_name"];
		} else {
			return "idle";
		}
	}
}

// returns capabilities string by appliance_id
function get_capabilities($appliance_id) {
	global $APPLIANCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$appliance_set = &$db->Execute("select appliance_capabilities from $APPLIANCE_INFO_TABLE where appliance_id=$appliance_id");
	if (!$appliance_set) {
		$event->log("get_capabilities", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if ((!$appliance_set->EOF) && ($appliance_set->fields["appliance_capabilities"]!=""))  {
			return $appliance_set->fields["appliance_capabilities"];
		} else {
			return "0";
		}
	}
}

// returns the number of appliances for an appliance type
function get_count() {
	global $APPLIANCE_INFO_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(appliance_id) as num from $APPLIANCE_INFO_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all appliance names
function get_list() {
	global $APPLIANCE_INFO_TABLE;
	$query = "select appliance_id, appliance_name from $APPLIANCE_INFO_TABLE";
	$appliance_name_array = array();
	$appliance_name_array = openqrm_db_get_result_double ($query);
	return $appliance_name_array;
}



// displays the appliance-overview
function display_overview($start, $count) {
	global $APPLIANCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $APPLIANCE_INFO_TABLE where appliance_id>=$start order by appliance_id ASC", $count);
	$appliance_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($appliance_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $appliance_array;
}









// ---------------------------------------------------------------------------------

}

?>