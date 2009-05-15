<?php

// This class represents a localstoragestate object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$LOCAL_STORAGE_STATE_TABLE="local_storage_state";
global $LOCAL_STORAGE_STATE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class localstoragestate {

var $id = '';
var $appliance_id = '';
var $token = '';
var $state = '';
	// state = 0	-> paused
	// state = 1	-> active

//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function localstoragestate() {
    $this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
    global $LOCAL_STORAGE_STATE_TABLE, $OPENQRM_SERVER_BASE_DIR;
    $this->_event = new event();
    $this->_db_table = $LOCAL_STORAGE_STATE_TABLE;
    $this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
}





// ---------------------------------------------------------------------------------
// methods to create an instance of a localstoragestate object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $appliance_id, $token) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$localstoragestate_array = &$db->Execute("select * from $LOCAL_STORAGE_STATE_TABLE where ls_id=$id");
	} else if ("$appliance_id" != "") {
		$localstoragestate_array = &$db->Execute("select * from $LOCAL_STORAGE_STATE_TABLE where ls_appliance_id=$appliance_id");
	} else if ("$token" != "") {
		$localstoragestate_array = &$db->Execute("select * from $LOCAL_STORAGE_STATE_TABLE where ls_token='$token'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}
	foreach ($localstoragestate_array as $index => $localstoragestate) {
		$this->id = $localstoragestate["ls_id"];
		$this->appliance_id = $localstoragestate["ls_appliance_id"];
		$this->token = $localstoragestate["ls_token"];
		$this->state = $localstoragestate["ls_state"];
	}
	return $this;
}

// returns an localstoragestate from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "");
	return $this;
}

// returns an localstoragestate from the db selected by the appliance_id
function get_instance_by_appliance_id($appliance_id) {
	$this->get_instance("", $appliance_id, "");
	return $this;
}

// returns an localstoragestate from the db selected by the token
function get_instance_by_token($token) {
	$this->get_instance("", "", $token);
	return $this;
}

// ---------------------------------------------------------------------------------
// general localstoragestate methods
// ---------------------------------------------------------------------------------




// checks if given localstoragestate id is free in the db
function is_id_free($localstoragestate_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select ls_id from $LOCAL_STORAGE_STATE_TABLE where ls_id=$localstoragestate_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds localstoragestate to the database
function add($localstoragestate_fields) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	if (!is_array($localstoragestate_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", "localstoragestate_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($LOCAL_STORAGE_STATE_TABLE, $localstoragestate_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", "Failed adding new localstoragestate to database", "", "", 0, 0, 0);
	}
}



// removes localstoragestate from the database
function remove($localstoragestate_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $LOCAL_STORAGE_STATE_TABLE where ls_id=$localstoragestate_id");
}



// sets the state of a localstoragestate
function set_state($localstoragestate_id, $state_str) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$localstoragestate_state = 0;
	switch ($state_str) {
		case "grab":
			$localstoragestate_state = 0;
			break;
		case "restore":
			$localstoragestate_state = 1;
			break;
	}
	$db=openqrm_get_db_connection();
	$localstoragestate_set = &$db->Execute("update $LOCAL_STORAGE_STATE_TABLE set ls_state=$localstoragestate_state where ls_id=$localstoragestate_id");
	if (!$localstoragestate_set) {
		$event->log("set_state", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}



// returns the number of localstoragestates for an localstoragestate type
function get_count() {
	global $LOCAL_STORAGE_STATE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(ls_id) as num from $LOCAL_STORAGE_STATE_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all localstoragestate names
function get_list() {
	global $LOCAL_STORAGE_STATE_TABLE;
	$query = "select ls_id, ls_appliance_id from $LOCAL_STORAGE_STATE_TABLE";
	$localstoragestate_name_array = array();
	$localstoragestate_name_array = openqrm_db_get_result_double ($query);
	return $localstoragestate_name_array;
}


// returns a list of all localstoragestate ids
function get_all_ids() {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$localstoragestate_list = array();
	$query = "select ls_id from $LOCAL_STORAGE_STATE_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$localstoragestate_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $localstoragestate_list;

}




// displays the localstoragestate-overview
function display_overview($offset, $limit, $sort, $order) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $LOCAL_STORAGE_STATE_TABLE order by $sort $order", $limit, $offset);
	$localstoragestate_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($localstoragestate_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $localstoragestate_array;
}



// ---------------------------------------------------------------------------------

}

?>

