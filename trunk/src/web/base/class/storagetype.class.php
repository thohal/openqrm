<?php

// This class represents a storagetype type

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $STORAGETYPE_INFO_TABLE;
$event = new event();
global $event;

class storagetype {

var $id = '';
var $name = '';
var $description = '';
var $mapping = '';



// ---------------------------------------------------------------------------------
// methods to create an instance of an storagetype object filled from the db
// ---------------------------------------------------------------------------------

// returns an storagetype from the db selected by id, type or name
function get_instance($id, $name) {
	global $STORAGETYPE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$storagetype_array = &$db->Execute("select * from $STORAGETYPE_INFO_TABLE where storagetype_id=$id");
	} else if ("$name" != "") {
		$storagetype_array = &$db->Execute("select * from $STORAGETYPE_INFO_TABLE where storagetype_name='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "storagetype.class.php", "Could not create instance of storagetype without data", "", "", 0, 0, 0);
		exit(-1);
	}
	foreach ($storagetype_array as $index => $storagetype) {
		$this->id = $storagetype["storagetype_id"];
		$this->name = $storagetype["storagetype_name"];
		$this->type = $storagetype["storagetype_description"];
		$this->mapping = $storagetype["storagetype_mapping"];
	}
	return $this;
}

// returns an storagetype from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an storagetype from the db selected by name
function get_instance_by_name($name) {
	$this->get_instance("", $name);
	return $this;
}


// ---------------------------------------------------------------------------------
// general storagetype methods
// ---------------------------------------------------------------------------------


// checks if given storagetype id is free in the db
function is_id_free($storagetype_id) {
	global $STORAGETYPE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select storagetype_id from $STORAGETYPE_INFO_TABLE where storagetype_id=$storagetype_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "storagetype.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds storagetype to the database
function add($storagetype_fields) {
	global $STORAGETYPE_INFO_TABLE;
	global $event;
	if (!is_array($storagetype_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "storagetype.class.php", "Deployment_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($STORAGETYPE_INFO_TABLE, $storagetype_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "storagetype.class.php", "Failed adding new storagetype to database", "", "", 0, 0, 0);
	}
}


// removes storagetype from the database
function remove($storagetype_id) {
	global $STORAGETYPE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $STORAGETYPE_INFO_TABLE where storagetype_id=$storagetype_id");
}

// removes storagetype from the database by storagetype_type
function remove_by_name($name) {
	global $STORAGETYPE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $STORAGETYPE_INFO_TABLE where storagetype_name='$name'");
}



// returns a list of all storagetype names
function get_list() {
	global $STORAGETYPE_INFO_TABLE;
	$query = "select storagetype_id, storagetype_name from $STORAGETYPE_INFO_TABLE";
	$storagetype_name_array = array();
	$storagetype_name_array = openqrm_db_get_result_double ($query);
	return $storagetype_name_array;
}





// ---------------------------------------------------------------------------------

}

?>