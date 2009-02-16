<?php

// This class represents a cloudappliance object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$CLOUD_APPLIANCE_TABLE="cloud_appliance";
global $CLOUD_APPLIANCE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudappliance {

var $id = '';
var $appliance_id = '';
var $cr_id = '';
var $cmd = '';
	// cmd = 0  -> noop
	// cmd = 1	-> start
	// cmd = 2	-> stop
	// cmd = 3	-> restart
var $state = '';
	// state = 0	-> paused
	// state = 1	-> active


// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudappliance object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $appliance_id) {
	global $CLOUD_APPLIANCE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudappliance_array = &$db->Execute("select * from $CLOUD_APPLIANCE_TABLE where ca_id=$id");
	} else if ("$appliance_id" != "") {
		$cloudappliance_array = &$db->Execute("select * from $CLOUD_APPLIANCE_TABLE where ca_appliance_id=$appliance_id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudappliance.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudappliance_array as $index => $cloudappliance) {
		$this->id = $cloudappliance["ca_id"];
		$this->appliance_id = $cloudappliance["ca_appliance_id"];
		$this->cr_id = $cloudappliance["ca_cr_id"];
		$this->cmd = $cloudappliance["ca_cmd"];
		$this->state = $cloudappliance["ca_state"];
	}
	return $this;
}

// returns an cloudappliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an cloudappliance from the db selected by the appliance_id
function get_instance_by_appliance_id($appliance_id) {
	$this->get_instance("", $appliance_id);
	return $this;
}

// ---------------------------------------------------------------------------------
// general cloudappliance methods
// ---------------------------------------------------------------------------------




// checks if given cloudappliance id is free in the db
function is_id_free($cloudappliance_id) {
	global $CLOUD_APPLIANCE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select ca_id from $CLOUD_APPLIANCE_TABLE where ca_id=$cloudappliance_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudappliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudappliance to the database
function add($cloudappliance_fields) {
	global $CLOUD_APPLIANCE_TABLE;
	global $event;
	if (!is_array($cloudappliance_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudappliance.class.php", "cloudappliance_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_APPLIANCE_TABLE, $cloudappliance_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudappliance.class.php", "Failed adding new cloudappliance to database", "", "", 0, 0, 0);
	}
}



// removes cloudappliance from the database
function remove($cloudappliance_id) {
	global $CLOUD_APPLIANCE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_APPLIANCE_TABLE where ca_id=$cloudappliance_id");
}



// sets the state of a cloudappliance
function set_state($cloudappliance_id, $state_str) {
	global $CLOUD_APPLIANCE_TABLE;
	global $event;
	$cloudappliance_state = 0;
	switch ($state_str) {
		case "paused":
			$cloudappliance_state = 0;
			break;
		case "active":
			$cloudappliance_state = 1;
			break;
	}
	$db=openqrm_get_db_connection();
	$cloudappliance_set = &$db->Execute("update $CLOUD_APPLIANCE_TABLE set ca_state=$cloudappliance_state where ca_id=$cloudappliance_id");
	if (!$cloudappliance_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudappliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// sets the cmd of a cloudappliance
function set_cmd($cloudappliance_id, $cmd_str) {
	global $CLOUD_APPLIANCE_TABLE;
	global $event;
	$cloudappliance_cmd = 0;
	switch ($cmd_str) {
		case "noop":
			$cloudappliance_cmd = 0;
			break;
		case "start":
			$cloudappliance_cmd = 1;
			break;
		case "stop":
			$cloudappliance_cmd = 2;
			break;
		case "restart":
			$cloudappliance_cmd = 3;
			break;
	}
	$db=openqrm_get_db_connection();
	$cloudappliance_set = &$db->Execute("update $CLOUD_APPLIANCE_TABLE set ca_cmd=$cloudappliance_cmd where ca_id=$cloudappliance_id");
	if (!$cloudappliance_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudappliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}




// returns the number of cloudappliances for an cloudappliance type
function get_count() {
	global $CLOUD_APPLIANCE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(ca_id) as num from $CLOUD_APPLIANCE_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudappliance names
function get_list() {
	global $CLOUD_APPLIANCE_TABLE;
	$query = "select ca_id, ca_cr_id from $CLOUD_APPLIANCE_TABLE";
	$cloudappliance_name_array = array();
	$cloudappliance_name_array = openqrm_db_get_result_double ($query);
	return $cloudappliance_name_array;
}


// returns a list of all cloudappliance ids
function get_all_ids() {
	global $CLOUD_APPLIANCE_TABLE;
	global $event;
	$cloudappliance_list = array();
	$query = "select ca_id from $CLOUD_APPLIANCE_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudappliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudappliance_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudappliance_list;

}




// displays the cloudappliance-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_APPLIANCE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_APPLIANCE_TABLE order by $sort $order", $limit, $offset);
	$cloudappliance_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudappliance.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudappliance_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $cloudappliance_array;
}









// ---------------------------------------------------------------------------------

}

