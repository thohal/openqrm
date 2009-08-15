<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


// This class represents a collectd user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$COLLECTD_CONFIG_TABLE="collectd_config";
global $COLLECTD_CONFIG_TABLE;
$event = new event();
global $event;

class collectdconfig {

var $id = '';
var $key = '';
var $value = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a collectdconfig object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $name) {
	global $COLLECTD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$collectdconfig_array = &$db->Execute("select * from $COLLECTD_CONFIG_TABLE where cc_id=$id");
	} else if ("$name" != "") {
		$collectdconfig_array = &$db->Execute("select * from $COLLECTD_CONFIG_TABLE where cc_key='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "collectdconfig.class.php", "Could not create instance of collectdconfig without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($collectdconfig_array as $index => $collectdconfig) {
		$this->id = $collectdconfig["cc_id"];
		$this->key = $collectdconfig["cc_key"];
		$this->value = $collectdconfig["cc_value"];
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
// general collectdconfig methods
// ---------------------------------------------------------------------------------




// checks if given collectdconfig id is free in the db
function is_id_free($collectdconfig_id) {
	global $COLLECTD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select cc_id from $COLLECTD_CONFIG_TABLE where cc_id=$collectdconfig_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "collectdconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds collectdconfig to the database
function add($collectdconfig_fields) {
	global $COLLECTD_CONFIG_TABLE;
	global $event;
	if (!is_array($collectdconfig_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "collectdconfig.class.php", "coulduser_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($COLLECTD_CONFIG_TABLE, $collectdconfig_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "collectdconfig.class.php", "Failed adding new collectdconfig to database", "", "", 0, 0, 0);
	}
}



// removes collectdconfig from the database
function remove($collectdconfig_id) {
	global $COLLECTD_CONFIG_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $COLLECTD_CONFIG_TABLE where cc_id=$collectdconfig_id");
}

// removes collectdconfig from the database by key
function remove_by_name($collectdconfig_key) {
	global $COLLECTD_CONFIG_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $COLLECTD_CONFIG_TABLE where cc_key='$collectdconfig_key'");
}


// returns collectdconfig value by collectdconfig_id
function get_value($collectdconfig_id) {
	global $COLLECTD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$collectdconfig_set = &$db->Execute("select cc_value from $COLLECTD_CONFIG_TABLE where cc_id=$collectdconfig_id");
	if (!$collectdconfig_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "collectdconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$collectdconfig_set->EOF) {
			return $collectdconfig_set->fields["cc_value"];
		} else {
			return "";
		}
	}
}


// sets a  collectdconfig value by collectdconfig_id
function set_value($collectdconfig_id, $collectdconfig_value) {
	global $COLLECTD_CONFIG_TABLE;
	global $event;
	
	$db=openqrm_get_db_connection();
	$collectdconfig_set = &$db->Execute("update $COLLECTD_CONFIG_TABLE set cc_value=\"$collectdconfig_value\" where cc_id=$collectdconfig_id");
	if (!$collectdconfig_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "collectdconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// returns the number of collectdconfigs for an collectdconfig type
function get_count() {
	global $COLLECTD_CONFIG_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cc_id) as num from $COLLECTD_CONFIG_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all collectdconfig names
function get_list() {
	global $COLLECTD_CONFIG_TABLE;
	$query = "select cc_id, cc_value from $COLLECTD_CONFIG_TABLE";
	$collectdconfig_name_array = array();
	$collectdconfig_name_array = openqrm_db_get_result_double ($query);
	return $collectdconfig_name_array;
}


// returns a list of all collectdconfig ids
function get_all_ids() {
	global $COLLECTD_CONFIG_TABLE;
	global $event;
	$collectdconfig_list = array();
	$query = "select cc_id from $COLLECTD_CONFIG_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "collectdconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$collectdconfig_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $collectdconfig_list;

}




// displays the collectdconfig-overview
function display_overview($offset, $limit, $sort, $order) {
	global $COLLECTD_CONFIG_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $COLLECTD_CONFIG_TABLE order by $sort $order", $limit, $offset);
	$collectdconfig_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "collectdconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($collectdconfig_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $collectdconfig_array;
}









// ---------------------------------------------------------------------------------

}

