<?php

// This class represents a deployment type

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $DEPLOYMENT_INFO_TABLE;
$event = new event();
global $event;

class deployment {

var $id = '';
var $storagetype_id = '';
var $name = '';
var $type = '';



// ---------------------------------------------------------------------------------
// methods to create an instance of an deployment object filled from the db
// ---------------------------------------------------------------------------------

// returns an deployment from the db selected by id, type or name
function get_instance($id, $name, $type) {
	global $DEPLOYMENT_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$deployment_array = &$db->Execute("select * from $DEPLOYMENT_INFO_TABLE where deployment_id=$id");
	} else if ("$name" != "") {
		$deployment_array = &$db->Execute("select * from $DEPLOYMENT_INFO_TABLE where deployment_name='$name'");
	} else if ("$type" != "") {
		$deployment_array = &$db->Execute("select * from $DEPLOYMENT_INFO_TABLE where deployment_type='$type'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "deployment.class.php", "Could not create instance of deployment without data", "", "", 0, 0, 0);
		exit(-1);
	}
	foreach ($deployment_array as $index => $deployment) {
		$this->id = $deployment["deployment_id"];

		$this->id = $deployment["deployment_id"];
		$this->storagetype_id = $deployment["deployment_storagetype_id"];
		$this->name = $deployment["deployment_name"];
		$this->type = $deployment["deployment_type"];
	}
	return $this;
}

// returns an deployment from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "");
	return $this;
}

// returns an deployment from the db selected by name
function get_instance_by_name($name) {
	$this->get_instance("", $name, "");
	return $this;
}

// returns an deployment from the db selected by type
function get_instance_by_type($type) {
	$this->get_instance("", "", $type);
	return $this;
}

// ---------------------------------------------------------------------------------
// general deployment methods
// ---------------------------------------------------------------------------------


// checks if given deployment id is free in the db
function is_id_free($deployment_id) {
	global $DEPLOYMENT_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select deployment_id from $DEPLOYMENT_INFO_TABLE where deployment_id=$deployment_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "deployment.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds deployment to the database
function add($deployment_fields) {
	global $DEPLOYMENT_INFO_TABLE;
	global $event;
	if (!is_array($deployment_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "deployment.class.php", "Deployment_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($DEPLOYMENT_INFO_TABLE, $deployment_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "deployment.class.php", "Failed adding new deployment to database", "", "", 0, 0, 0);
	}
}


// removes deployment from the database
function remove($deployment_id) {
	global $DEPLOYMENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $DEPLOYMENT_INFO_TABLE where deployment_id=$deployment_id");
}

// removes deployment from the database by deployment_type
function remove_by_type($type) {
	global $DEPLOYMENT_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $DEPLOYMENT_INFO_TABLE where deployment_type='$type'");
}



// returns a list of all deployment names
function get_list() {
	global $DEPLOYMENT_INFO_TABLE;
	$query = "select deployment_id, deployment_name from $DEPLOYMENT_INFO_TABLE";
	$deployment_name_array = array();
	$deployment_name_array = openqrm_db_get_result_double ($query);
	return $deployment_name_array;
}





// ---------------------------------------------------------------------------------

}

?>