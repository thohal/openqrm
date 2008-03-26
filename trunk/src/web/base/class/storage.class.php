<?php

// This class represents a storage server

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
global $STORAGE_INFO_TABLE;

class storage {

var $id = '';
var $name = '';
var $resource_id = '';
var $deployment_type = '';
var $state = '';
var $comment = '';
var $capabilities = '';



// ---------------------------------------------------------------------------------
// methods to create an instance of a storage object filled from the db
// ---------------------------------------------------------------------------------

// returns a storage from the db selected by id or name
function get_instance($id, $name) {
	global $STORAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$storage_array = &$db->Execute("select * from $STORAGE_INFO_TABLE where storage_id=$id");
	} else if ("$name" != "") {
		$storage_array = &$db->Execute("select * from $STORAGE_INFO_TABLE where storage_name='$name'");
	} else {
		echo "ERROR: Could not create instance of storage without data";
		exit(-1);
	}
	foreach ($storage_array as $index => $storage) {
		$this->id = $storage["storage_id"];
		$this->name = $storage["storage_name"];
		$this->resource_id = $storage["storage_resource_id"];
		$this->deployment_type = $storage["storage_deployment_type"];
		$this->state = $storage["storage_state"];
		$this->comment = $storage["storage_comment"];
		$this->capabilities = $storage["storage_capabilities"];
	}
	return $this;
}

// returns a storage from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns a storage from the db selected by iname
function get_instance_by_name($name) {
	$this->get_instance("", $name);
	return $this;
}


// ---------------------------------------------------------------------------------
// general storage methods
// ---------------------------------------------------------------------------------




// checks if given storage id is free in the db
function is_id_free($storage_id) {
	global $STORAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select storage_id from $STORAGE_INFO_TABLE where storage_id=$storage_id");
	if (!$rs)
		print $db->ErrorMsg();
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds storage to the database
function add($storage_fields) {
	global $STORAGE_INFO_TABLE;
	if (!is_array($storage_fields)) {
		print("storage_field not well defined");
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($STORAGE_INFO_TABLE, $storage_fields, 'INSERT');
	if (! $result) {
		print("Failed adding new storage to database");
	}
}



// updates storage in the database
function update($storage_id, $storage_fields) {
	global $STORAGE_INFO_TABLE;
	if ($storage_id < 0 || ! is_array($storage_fields)) {
		print("Unable to update storage $storage_id");
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($storage_fields["storage_id"]);
	$result = $db->AutoExecute($STORAGE_INFO_TABLE, $storage_fields, 'UPDATE', "storage_id = $storage_id");
	if (! $result) {
		print("Failed updating storage $storage_id");
	}
}

// removes storage from the database
function remove($storage_id) {
	global $STORAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $STORAGE_INFO_TABLE where storage_id=$storage_id");
}

// removes storage from the database by storage_name
function remove_by_name($storage_name) {
	global $STORAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $STORAGE_INFO_TABLE where storage_name='$storage_name'");
}

// returns storage name by storage_id
function get_name($storage_id) {
	global $STORAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$storage_set = &$db->Execute("select storage_name from $STORAGE_INFO_TABLE where storage_id=$storage_id");
	if (!$storage_set) {
		print $db->ErrorMsg();
	} else {
		if (!$storage_set->EOF) {
			return $storage_set->fields["storage_name"];
		} else {
			return "idle";
		}
	}
}

// returns capabilities string by storage_id
function get_capabilities($storage_id) {
	global $STORAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$storage_set = &$db->Execute("select storage_capabilities from $STORAGE_INFO_TABLE where storage_id=$storage_id");
	if (!$storage_set) {
		print $db->ErrorMsg();
	} else {
		if ((!$storage_set->EOF) && ($storage_set->fields["storage_capabilities"]!=""))  {
			return $storage_set->fields["storage_capabilities"];
		} else {
			return "0";
		}
	}
}

// returns the number of storages for an storage type
function get_count() {
	global $STORAGE_INFO_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(storage_id) as num from $STORAGE_INFO_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all storage names
function get_list() {
	global $STORAGE_INFO_TABLE;
	$query = "select storage_id, storage_name from $STORAGE_INFO_TABLE";
	$storage_name_array = array();
	$storage_name_array = openqrm_db_get_result_double ($query);
	return $storage_name_array;
}



// displays the storage-overview
function display_overview($start, $count) {
	global $STORAGE_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $STORAGE_INFO_TABLE where storage_id>=$start order by storage_id ASC", $count);
	$storage_array = array();
	if (!$recordSet) {
		print $db->ErrorMsg();
	} else {
		while (!$recordSet->EOF) {
			array_push($storage_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $storage_array;
}









// ---------------------------------------------------------------------------------

}

?>