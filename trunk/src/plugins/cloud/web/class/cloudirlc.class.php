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


// This class represents a cloudirlc-resize-life-cycle object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE="cloud_irlc";
global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudirlc {

var $id = '';
var $appliance_id = '';
var $state = '';
var $_db_table;
var $_base_dir;
var $_event;

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudirlc() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}



// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudirlc object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or appliance_id
function get_instance($id, $appliance_id) {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudirlc_array = &$db->Execute("select * from $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE where cd_id=$id");
	} else if ("$appliance_id" != "") {
		$cloudirlc_array = &$db->Execute("select * from $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE where cd_appliance_id=$appliance_id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudirlc.class.php", "Could not create instance of cloudirlc without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudirlc_array as $index => $cloudirlc) {
		$this->id = $cloudirlc["cd_id"];
		$this->appliance_id = $cloudirlc["cd_appliance_id"];
		$this->state = $cloudirlc["cd_state"];
	}
	return $this;
}

// returns an cloudirlc from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an cloudirlc from the db selected by the appliance_id
function get_instance_by_appliance_id($appliance_id) {
	$this->get_instance("", $appliance_id);
	return $this;
}

// ---------------------------------------------------------------------------------
// general cloudirlc methods
// ---------------------------------------------------------------------------------




// checks if given cloudirlc id is free in the db
function is_id_free($cloudirlc_id) {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select cd_id from $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE where cd_id=$cloudirlc_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudirlc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudirlc to the database
function add($cloudirlc_fields) {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	global $event;
	if (!is_array($cloudirlc_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudirlc.class.php", "cloudirlc_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE, $cloudirlc_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudirlc.class.php", "Failed adding new cloudirlc to database", "", "", 0, 0, 0);
	}
}



// removes cloudirlc from the database
function remove($cloudirlc_id) {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE where cd_id=$cloudirlc_id");
}



// sets the state of a cloudirlc
function set_state($cloudirlc_id, $state_str) {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	global $event;
	$cloudirlc_state = 0;
	switch ($state_str) {
		case "remove":
			$cloudirlc_state = 0;
			break;
		case "pause":
			$cloudirlc_state = 1;
			break;
		case "start_resize":
			$cloudirlc_state = 2;
			break;
		case "resizing":
			$cloudirlc_state = 3;
			break;
		case "end_resize":
			$cloudirlc_state = 4;
			break;
		case "unpause":
			$cloudirlc_state = 5;
			break;
	}
	$db=openqrm_get_db_connection();
	$cloudirlc_set = &$db->Execute("update $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE set cd_state=$cloudirlc_state where cd_id=$cloudirlc_id");
	if (!$cloudirlc_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudirlc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// sets the resource of a cloudirlc
function set_resource($cloudirlc_id, $resource_id) {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudirlc_set = &$db->Execute("update $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE set cd_resource_id=$resource_id where cd_id=$cloudirlc_id");
	if (!$cloudirlc_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudirlc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// returns the number of cloudirlcs for an cloudirlc type
function get_count() {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cd_id) as num from $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudirlc names
function get_list() {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	$query = "select cd_id, cd_appliance_id from $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE";
	$cloudirlc_name_array = array();
	$cloudirlc_name_array = openqrm_db_get_result_double ($query);
	return $cloudirlc_name_array;
}


// returns a list of all cloudirlc ids
function get_all_ids() {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	global $event;
	$cloudirlc_list = array();
	$query = "select cd_id from $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudirlc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudirlc_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudirlc_list;

}




// displays the cloudirlc-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_IMAGE_RESIZE_LIVE_CYCLE_TABLE order by $sort $order", $limit, $offset);
	$cloudirlc_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudirlc.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudirlc_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudirlc_array;
}









// ---------------------------------------------------------------------------------

}


?>
