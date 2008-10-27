<?php

// This class represents a puppet user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$PUPPET_CONFIG_TABLE="puppet_config";
global $PUPPET_CONFIG_TABLE;
$event = new event();
global $event;

class puppetconfig {

var $id = '';
var $key = '';
var $value = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a puppetconfig object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $name) {
	global $PUPPET_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$puppetconfig_array = &$db->Execute("select * from $PUPPET_CONFIG_TABLE where cc_id=$id");
	} else if ("$name" != "") {
		$puppetconfig_array = &$db->Execute("select * from $PUPPET_CONFIG_TABLE where cc_key='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "coulduser.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		exit(-1);
	}

	foreach ($puppetconfig_array as $index => $puppetconfig) {
		$this->id = $puppetconfig["cc_id"];
		$this->key = $puppetconfig["cc_key"];
		$this->value = $puppetconfig["cc_value"];
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
// general puppetconfig methods
// ---------------------------------------------------------------------------------




// checks if given puppetconfig id is free in the db
function is_id_free($puppetconfig_id) {
	global $PUPPET_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select cc_id from $PUPPET_CONFIG_TABLE where cc_id=$puppetconfig_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds puppetconfig to the database
function add($puppetconfig_fields) {
	global $PUPPET_CONFIG_TABLE;
	global $event;
	if (!is_array($puppetconfig_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", "coulduser_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($PUPPET_CONFIG_TABLE, $puppetconfig_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", "Failed adding new puppetconfig to database", "", "", 0, 0, 0);
	}
}



// removes puppetconfig from the database
function remove($puppetconfig_id) {
	global $PUPPET_CONFIG_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $PUPPET_CONFIG_TABLE where cc_id=$puppetconfig_id");
}

// removes puppetconfig from the database by key
function remove_by_name($puppetconfig_key) {
	global $PUPPET_CONFIG_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $PUPPET_CONFIG_TABLE where cc_key='$puppetconfig_key'");
}


// returns puppetconfig value by puppetconfig_id
function get_value($puppetconfig_id) {
	global $PUPPET_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$puppetconfig_set = &$db->Execute("select cc_value from $PUPPET_CONFIG_TABLE where cc_id=$puppetconfig_id");
	if (!$puppetconfig_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$puppetconfig_set->EOF) {
			return $puppetconfig_set->fields["cc_value"];
		} else {
			return "";
		}
	}
}


// sets a  puppetconfig value by puppetconfig_id
function set_value($puppetconfig_id, $puppetconfig_value) {
	global $PUPPET_CONFIG_TABLE;
	global $event;
	
	echo "update $PUPPET_CONFIG_TABLE set cc_value=\"$puppetconfig_value\" where cc_id=$puppetconfig_id <br>";
	$db=openqrm_get_db_connection();
	$puppetconfig_set = &$db->Execute("update $PUPPET_CONFIG_TABLE set cc_value=\"$puppetconfig_value\" where cc_id=$puppetconfig_id");
	if (!$puppetconfig_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// returns the number of puppetconfigs for an puppetconfig type
function get_count() {
	global $PUPPET_CONFIG_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cc_id) as num from $PUPPET_CONFIG_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all puppetconfig names
function get_list() {
	global $PUPPET_CONFIG_TABLE;
	$query = "select cc_id, cc_value from $PUPPET_CONFIG_TABLE";
	$puppetconfig_name_array = array();
	$puppetconfig_name_array = openqrm_db_get_result_double ($query);
	return $puppetconfig_name_array;
}


// returns a list of all puppetconfig ids
function get_all_ids() {
	global $PUPPET_CONFIG_TABLE;
	global $event;
	$puppetconfig_list = array();
	$query = "select cc_id from $PUPPET_CONFIG_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$puppetconfig_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $puppetconfig_list;

}




// displays the puppetconfig-overview
function display_overview($offset, $limit, $sort, $order) {
	global $PUPPET_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $PUPPET_CONFIG_TABLE order by $sort $order", $limit, $offset);
	$puppetconfig_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($puppetconfig_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $puppetconfig_array;
}









// ---------------------------------------------------------------------------------

}

