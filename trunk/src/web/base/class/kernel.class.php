<?php

// This class represents boot-image (kernel) 
// A Kernel can be used to deploy an (server-)image (image.class)
// to a resource (resource.class) via an appliance (appliance.class)

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $KERNEL_INFO_TABLE;
$event = new event();
global $event;

class kernel {

var $id = '';
var $name = '';
var $version = '';
var $capabilities = '';

// ---------------------------------------------------------------------------------
// methods to create an instance of a kernel object filled from the db
// ---------------------------------------------------------------------------------

// returns a kernel from the db selected by id or name
function get_instance($id, $name) {
	global $KERNEL_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$kernel_array = &$db->Execute("select * from $KERNEL_INFO_TABLE where kernel_id=$id");
	} else if ("$name" != "") {
		$kernel_array = &$db->Execute("select * from $KERNEL_INFO_TABLE where kernel_name='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Could not create instance of kernel without data", "", "", 0, 0, 0);
		exit(-1);
	}
	foreach ($kernel_array as $index => $kernel) {
		$this->id = $kernel["kernel_id"];
		$this->name = $kernel["kernel_name"];
		$this->version = $kernel["kernel_version"];
		$this->capabilities = $kernel["kernel_capabilities"];
	}
	return $this;
}

// returns a kernel from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns a kernel from the db selected by iname
function get_instance_by_name($name) {
	$this->get_instance("", $name);
	return $this;
}




// ---------------------------------------------------------------------------------
// general kernel methods
// ---------------------------------------------------------------------------------


// checks if given kernel id is free in the db
function is_id_free($kernel_id) {
	global $KERNEL_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select kernel_id from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds kernel to the database
function add($kernel_fields) {
	global $KERNEL_INFO_TABLE;
	global $event;
	if (!is_array($kernel_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Kernel_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($KERNEL_INFO_TABLE, $kernel_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Failed adding new kernel to database", "", "", 0, 0, 0);
	}
}

// updates kernel in the database
function update($kernel_id, $kernel_fields) {
	global $KERNEL_INFO_TABLE;
	global $event;
	if ($kernel_id < 0 || ! is_array($kernel_fields)) {
		$event->log("update", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Unable to update kernel $kernel_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($kernel_fields["kernel_id"]);
	$result = $db->AutoExecute($KERNEL_INFO_TABLE, $kernel_fields, 'UPDATE', "kernel_id = $kernel_id");
	if (! $result) {
		$event->log("update", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Failed updating kernel $kernel_id", "", "", 0, 0, 0);
	}
}

// removes kernel from the database
function remove($kernel_id) {
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
}

// removes kernel from the database by name
function remove_by_name($kernel_name) {
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $KERNEL_INFO_TABLE where kernel_name='$kernel_name'");
}


// returns kernel_name by kernel_id
function get_name($kernel_id) {
	global $KERNEL_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$kernel_set = &$db->Execute("select kernel_name from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
	if (!$kernel_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$kernel_set->EOF) {
			return $kernel_set->fields["kernel_name"];
		}
	}
}



// returns the number of available kernels
function get_count() {
	global $KERNEL_INFO_TABLE;
	global $event;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(kernel_id) as num from $KERNEL_INFO_TABLE");
	if (!$rs) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all kernel names
function get_list() {
	global $KERNEL_INFO_TABLE;
	$query = "select kernel_id, kernel_name from $KERNEL_INFO_TABLE";
	$kernel_name_array = array();
	$kernel_name_array = openqrm_db_get_result_double ($query);
	return $kernel_name_array;
}



// displays the kernel-overview
function display_overview($offset, $limit, $sort, $order) {
	global $KERNEL_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $KERNEL_INFO_TABLE order by $sort $order", $limit, $offset);
	$kernel_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($kernel_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $kernel_array;
}







// ---------------------------------------------------------------------------------

}

?>