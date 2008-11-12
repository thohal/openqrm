<?php

// This class represents a cloud ipgroup in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$CLOUD_IPGROUP_TABLE="cloud_ipgroups";
global $CLOUD_IPGROUP_TABLE;
$event = new event();
global $event;

class cloudipgroup {

var $ig_id = '';
var $ig_name = '';
var $ig_network = '';
var $ig_subnet = '';
var $ig_gateway = '';
var $ig_dns1 = '';
var $ig_dns2 = '';
var $ig_activeips = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudipgroup object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $name) {
	global $CLOUD_IPGROUP_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudipgroup_array = &$db->Execute("select * from $CLOUD_IPGROUP_TABLE where ig_id=$id");
	} else if ("$name" != "") {
		$cloudipgroup_array = &$db->Execute("select * from $CLOUD_IPGROUP_TABLE where ig_name='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudipgroup.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		exit(-1);
	}

	foreach ($cloudipgroup_array as $index => $cloudipgroup) {
		$this->ig_id = $cloudipgroup["ig_id"];
		$this->ig_name = $cloudipgroup["ig_name"];
		$this->ig_network = $cloudipgroup["ig_network"];
		$this->ig_subnet = $cloudipgroup["ig_subnet"];
		$this->ig_gateway = $cloudipgroup["ig_gateway"];
		$this->ig_dns1 = $cloudipgroup["ig_dns1"];
		$this->ig_dns2 = $cloudipgroup["ig_dns2"];
		$this->ig_activeips = $cloudipgroup["ig_activeips"];
	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an appliance from the db selected by iname
function get_instance_by_name($name) {
	$this->get_instance("", $name);
	return $this;
}


// ---------------------------------------------------------------------------------
// general cloudipgroup methods
// ---------------------------------------------------------------------------------




// checks if given cloudipgroup id is free in the db
function is_id_free($cloudipgroup_id) {
	global $CLOUD_IPGROUP_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select ig_id from $CLOUD_IPGROUP_TABLE where ig_id=$cloudipgroup_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudipgroup.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// checks if given cloudipgroup name is free in the db
function is_name_free($cloudipgroup_name) {
	global $CLOUD_IPGROUP_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select ig_id from $CLOUD_IPGROUP_TABLE where ig_name='$cloudipgroup_name'");
	if (!$rs)
		$event->log("is_name_free", $_SERVER['REQUEST_TIME'], 2, "cloudipgroup.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudipgroup to the database
function add($cloudipgroup_fields) {
	global $CLOUD_IPGROUP_TABLE;
	global $event;
	if (!is_array($cloudipgroup_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudipgroup.class.php", "cloudipgroup_fields not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_IPGROUP_TABLE, $cloudipgroup_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudipgroup.class.php", "Failed adding new cloudipgroup to database", "", "", 0, 0, 0);
	}
}



// removes cloudipgroup from the database
function remove($cloudipgroup_id) {
	global $CLOUD_IPGROUP_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_IPGROUP_TABLE where ig_id=$cloudipgroup_id");
}

// removes cloudipgroup from the database by cloudipgroup_name
function remove_by_name($cloudipgroup_name) {
	global $CLOUD_IPGROUP_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_IPGROUP_TABLE where ig_name='$cloudipgroup_name'");
}



// returns cloudipgroup name by cloudipgroup_id
function get_name($cloudipgroup_id) {
	global $CLOUD_IPGROUP_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudipgroup_set = &$db->Execute("select ig_name from $CLOUD_IPGROUP_TABLE where ig_id=$cloudipgroup_id");
	if (!$cloudipgroup_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudipgroup.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$cloudipgroup_set->EOF) {
			return $cloudipgroup_set->fields["ig_name"];
		} else {
			return "idle";
		}
	}
}


// returns the number of cloudipgroups for an cloudipgroup type
function get_count() {
	global $CLOUD_IPGROUP_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(ig_id) as num from $CLOUD_IPGROUP_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudipgroup names
function get_list() {
	global $CLOUD_IPGROUP_TABLE;
	$query = "select ig_id, ig_name from $CLOUD_IPGROUP_TABLE";
	$cloudipgroup_name_array = array();
	$cloudipgroup_name_array = openqrm_db_get_result_double ($query);
	return $cloudipgroup_name_array;
}


// returns a list of all cloudipgroup ids
function get_all_ids() {
	global $CLOUD_IPGROUP_TABLE;
	global $event;
	$cloudipgroup_list = array();
	$query = "select ig_id from $CLOUD_IPGROUP_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudipgroup.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudipgroup_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudipgroup_list;

}



// displays the cloudipgroup-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_IPGROUP_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_IPGROUP_TABLE order by $sort $order", $limit, $offset);
	$cloudipgroup_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudipgroup.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudipgroup_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $cloudipgroup_array;
}









// ---------------------------------------------------------------------------------

}

?>