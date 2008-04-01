<?php

// This class represents a virtualization type

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $VIRTUALIZATION_INFO_TABLE;
$event = new event();
global $event;

class virtualization {

var $id = '';
var $name = '';
var $type = '';



// ---------------------------------------------------------------------------------
// methods to create an instance of an virtualization object filled from the db
// ---------------------------------------------------------------------------------

// returns an virtualization from the db selected by id, type or name
function get_instance($id, $name, $type) {
	global $VIRTUALIZATION_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$virtualization_array = &$db->Execute("select * from $VIRTUALIZATION_INFO_TABLE where virtualization_id=$id");
	} else if ("$name" != "") {
		$virtualization_array = &$db->Execute("select * from $VIRTUALIZATION_INFO_TABLE where virtualization_name='$name'");
	} else if ("$type" != "") {
		$virtualization_array = &$db->Execute("select * from $VIRTUALIZATION_INFO_TABLE where virtualization_type='$type'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "virtualization.class.php", "Could not create instance of virtualization without data", "", "", 0, 0, 0);
		exit(-1);
	}
	foreach ($virtualization_array as $index => $virtualization) {
		$this->id = $virtualization["virtualization_id"];

		$this->id = $virtualization["virtualization_id"];
		$this->name = $virtualization["virtualization_name"];
		$this->type = $virtualization["virtualization_type"];
	}
	return $this;
}

// returns an virtualization from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "");
	return $this;
}

// returns an virtualization from the db selected by name
function get_instance_by_name($name) {
	$this->get_instance("", $name, "");
	return $this;
}

// returns an virtualization from the db selected by type
function get_instance_by_type($type) {
	$this->get_instance("", "", $type);
	return $this;
}

// ---------------------------------------------------------------------------------
// general virtualization methods
// ---------------------------------------------------------------------------------


// checks if given virtualization id is free in the db
function is_id_free($virtualization_id) {
	global $VIRTUALIZATION_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select virtualization_id from $VIRTUALIZATION_INFO_TABLE where virtualization_id=$virtualization_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "virtualization.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds virtualization to the database
function add($virtualization_fields) {
	global $VIRTUALIZATION_INFO_TABLE;
	global $event;
	if (!is_array($virtualization_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "virtualization.class.php", "Deployment_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($VIRTUALIZATION_INFO_TABLE, $virtualization_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "virtualization.class.php", "Failed adding new virtualization to database", "", "", 0, 0, 0);
	}
}


// removes virtualization from the database
function remove($virtualization_id) {
	global $VIRTUALIZATION_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $VIRTUALIZATION_INFO_TABLE where virtualization_id=$virtualization_id");
}

// removes virtualization from the database by virtualization_type
function remove_by_type($type) {
	global $VIRTUALIZATION_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $VIRTUALIZATION_INFO_TABLE where virtualization_type='$type'");
}



// returns a list of all virtualization names
function get_list() {
	global $VIRTUALIZATION_INFO_TABLE;
	$query = "select virtualization_id, virtualization_name from $VIRTUALIZATION_INFO_TABLE";
	$virtualization_name_array = array();
	$virtualization_name_array = openqrm_db_get_result_double ($query);
	return $virtualization_name_array;
}





// ---------------------------------------------------------------------------------

}

?>