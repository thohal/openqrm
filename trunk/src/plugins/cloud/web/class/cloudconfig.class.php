<?php

// This class represents a cloud user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$CLOUD_CONFIG_TABLE="cloud_config";
global $CLOUD_CONFIG_TABLE;
$event = new event();
global $event;

class cloudconfig {

var $id = '';
var $key = '';
var $value = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudconfig object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $name) {
	global $CLOUD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudconfig_array = &$db->Execute("select * from $CLOUD_CONFIG_TABLE where cc_id=$id");
	} else if ("$name" != "") {
		$cloudconfig_array = &$db->Execute("select * from $CLOUD_CONFIG_TABLE where cc_key='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudconfig.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudconfig_array as $index => $cloudconfig) {
		$this->id = $cloudconfig["cc_id"];
		$this->key = $cloudconfig["cc_key"];
		$this->value = $cloudconfig["cc_value"];
	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an appliance from the db selected by key
function get_instance_by_key($name) {
	$this->get_instance("", $name);
	return $this;
}


// ---------------------------------------------------------------------------------
// general cloudconfig methods
// ---------------------------------------------------------------------------------




// checks if given cloudconfig id is free in the db
function is_id_free($cloudconfig_id) {
	global $CLOUD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select cc_id from $CLOUD_CONFIG_TABLE where cc_id=$cloudconfig_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudconfig to the database
function add($cloudconfig_fields) {
	global $CLOUD_CONFIG_TABLE;
	global $event;
	if (!is_array($cloudconfig_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudconfig.class.php", "cloudconfig_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_CONFIG_TABLE, $cloudconfig_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudconfig.class.php", "Failed adding new cloudconfig to database", "", "", 0, 0, 0);
	}
}



// removes cloudconfig from the database
function remove($cloudconfig_id) {
	global $CLOUD_CONFIG_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_CONFIG_TABLE where cc_id=$cloudconfig_id");
}

// removes cloudconfig from the database by key
function remove_by_name($cloudconfig_key) {
	global $CLOUD_CONFIG_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_CONFIG_TABLE where cc_key='$cloudconfig_key'");
}


// returns cloudconfig value by cloudconfig_id
function get_value($cloudconfig_id) {
	global $CLOUD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudconfig_set = &$db->Execute("select cc_value from $CLOUD_CONFIG_TABLE where cc_id=$cloudconfig_id");
	if (!$cloudconfig_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$cloudconfig_set->EOF) {
			return $cloudconfig_set->fields["cc_value"];
		} else {
			return "";
		}
	}
}


// sets a  cloudconfig value by cloudconfig_id
function set_value($cloudconfig_id, $cloudconfig_value) {
	global $CLOUD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudconfig_set = &$db->Execute("update $CLOUD_CONFIG_TABLE set cc_value=\"$cloudconfig_value\" where cc_id=$cloudconfig_id");
	if (!$cloudconfig_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// returns the number of cloudconfigs for an cloudconfig type
function get_count() {
	global $CLOUD_CONFIG_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cc_id) as num from $CLOUD_CONFIG_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudconfig names
function get_list() {
	global $CLOUD_CONFIG_TABLE;
	$query = "select cc_id, cc_value from $CLOUD_CONFIG_TABLE";
	$cloudconfig_name_array = array();
	$cloudconfig_name_array = openqrm_db_get_result_double ($query);
	return $cloudconfig_name_array;
}


// returns a list of all cloudconfig ids
function get_all_ids() {
	global $CLOUD_CONFIG_TABLE;
	global $event;
	$cloudconfig_list = array();
	$query = "select cc_id from $CLOUD_CONFIG_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudconfig_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudconfig_list;

}




// displays the cloudconfig-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_CONFIG_TABLE order by $sort $order", $limit, $offset);
	$cloudconfig_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudconfig_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $cloudconfig_array;
}









// ---------------------------------------------------------------------------------

}

?>
