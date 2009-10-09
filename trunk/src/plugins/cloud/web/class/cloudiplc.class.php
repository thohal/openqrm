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


// This class represents a cloud image-private-life-cycle object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE="cloud_iplc";
global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudiplc {

var $id = '';
var $appliance_id = '';
var $cu_id = '';
var $state = '';
var $start_private = '';
var $_db_table;
var $_base_dir;
var $_event;


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudiplc() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}



// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudiplc object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or appliance_id
function get_instance($id, $appliance_id) {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudiplc_array = &$db->Execute("select * from $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE where cp_id=$id");
	} else if ("$appliance_id" != "") {
		$cloudiplc_array = &$db->Execute("select * from $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE where cp_appliance_id=$appliance_id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudiplc.class.php", "Could not create instance of cloudiplc without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudiplc_array as $index => $cloudiplc) {
		$this->id = $cloudiplc["cp_id"];
		$this->appliance_id = $cloudiplc["cp_appliance_id"];
		$this->cu_id = $cloudiplc["cp_cu_id"];
		$this->state = $cloudiplc["cp_state"];
		$this->start_private = $cloudiplc["cp_start_private"];
	}
	return $this;
}

// returns an cloudiplc from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an cloudiplc from the db selected by the appliance_id
function get_instance_by_appliance_id($appliance_id) {
	$this->get_instance("", $appliance_id);
	return $this;
}

// ---------------------------------------------------------------------------------
// general cloudiplc methods
// ---------------------------------------------------------------------------------




// checks if given cloudiplc id is free in the db
function is_id_free($cloudiplc_id) {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select cp_id from $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE where cp_id=$cloudiplc_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudiplc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudiplc to the database
function add($cloudiplc_fields) {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	global $event;
	if (!is_array($cloudiplc_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudiplc.class.php", "cloudiplc_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE, $cloudiplc_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudiplc.class.php", "Failed adding new cloudiplc to database", "", "", 0, 0, 0);
	}
}



// removes cloudiplc from the database
function remove($cloudiplc_id) {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE where cp_id=$cloudiplc_id");
}



// sets the state of a cloudiplc
function set_state($cloudiplc_id, $state_str) {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	global $event;
	$cloudiplc_state = 0;
	switch ($state_str) {
		case "remove":
			$cloudiplc_state = 0;
			break;
		case "pause":
			$cloudiplc_state = 1;
			break;
		case "start_private":
			$cloudiplc_state = 2;
			break;
		case "cloning":
			$cloudiplc_state = 3;
			break;
		case "end_private":
			$cloudiplc_state = 4;
			break;
		case "unpause":
			$cloudiplc_state = 5;
			break;
	}
	$db=openqrm_get_db_connection();
	$cloudiplc_set = &$db->Execute("update $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE set cp_state=$cloudiplc_state where cp_id=$cloudiplc_id");
	if (!$cloudiplc_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudiplc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// sets the resource of a cloudiplc
function set_resource($cloudiplc_id, $resource_id) {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudiplc_set = &$db->Execute("update $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE set cp_resource_id=$resource_id where cp_id=$cloudiplc_id");
	if (!$cloudiplc_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudiplc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// returns the number of cloudiplcs for an cloudiplc type
function get_count() {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cp_id) as num from $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudiplc names
function get_list() {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	$query = "select cp_id, cp_appliance_id from $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE";
	$cloudiplc_name_array = array();
	$cloudiplc_name_array = openqrm_db_get_result_double ($query);
	return $cloudiplc_name_array;
}


// returns a list of all cloudiplc ids
function get_all_ids() {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	global $event;
	$cloudiplc_list = array();
	$query = "select cp_id from $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudiplc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudiplc_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudiplc_list;

}




// displays the cloudiplc-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_IMAGE_PRIVATE_LIVE_CYCLE_TABLE order by $sort $order", $limit, $offset);
	$cloudiplc_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudiplc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudiplc_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudiplc_array;
}









// ---------------------------------------------------------------------------------

}


?>
