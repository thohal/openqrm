<?php

// This class represents boot-image (kernel) 
// A Kernel can be used to deploy an (server-)image (image.class)
// to a resource (resource.class) via an appliance (appliance.class)

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
global $KERNEL_INFO_TABLE;

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
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$kernel_array = &$db->Execute("select * from $KERNEL_INFO_TABLE where kernel_id=$id");
	} else if ("$name" != "") {
		$kernel_array = &$db->Execute("select * from $KERNEL_INFO_TABLE where kernel_name=$name");
	} else {
		echo "ERROR: Could not create instance of kernel without data";
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
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select kernel_id from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
	if (!$rs)
		print $db->ErrorMsg();
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds kernel to the database
function add($kernel_id, $kernel_name, $kernel_version) {
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("insert into $KERNEL_INFO_TABLE (kernel_id, kernel_name, kernel_version) values ($kernel_id, '$kernel_name', '$kernel_version')");
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
	$db=openqrm_get_db_connection();
	$kernel_set = &$db->Execute("select kernel_name from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
	if (!$kernel_set) {
		print $db->ErrorMsg();
	} else {
		if (!$kernel_set->EOF) {
			return $kernel_set->fields["kernel_name"];
		}
	}
}



// returns the number of available kernels
function get_count() {
	global $KERNEL_INFO_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(kernel_id) as num from $KERNEL_INFO_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
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
function display_overview($start, $count) {
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $KERNEL_INFO_TABLE where kernel_id>=$start order by kernel_id ASC", $count);
	$kernel_array = array();
	if (!$recordSet) {
		print $db->ErrorMsg();
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