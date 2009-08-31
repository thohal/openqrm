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


// This class represents a cloudimage object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$CLOUD_IMAGE_TABLE="cloud_image";
global $CLOUD_IMAGE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudimage {

var $id = '';
var $cr_id = '';
var $image_id = '';
var $appliance_id = '';
var $resource_id = '';
var $disk_size = '';
var $disk_rsize = '';
var $state = '';

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudimage() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_IMAGE_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_IMAGE_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudimage object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $image_id) {
	global $CLOUD_IMAGE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudimage_array = &$db->Execute("select * from $CLOUD_IMAGE_TABLE where ci_id=$id");
	} else if ("$image_id" != "") {
		$cloudimage_array = &$db->Execute("select * from $CLOUD_IMAGE_TABLE where ci_image_id=$image_id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", "Could not create instance of cloudimage without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudimage_array as $index => $cloudimage) {
		$this->id = $cloudimage["ci_id"];
		$this->cr_id = $cloudimage["ci_cr_id"];
		$this->image_id = $cloudimage["ci_image_id"];
		$this->appliance_id = $cloudimage["ci_appliance_id"];
		$this->resource_id = $cloudimage["ci_resource_id"];
		$this->disk_size = $cloudimage["ci_disk_size"];
		$this->disk_rsize = $cloudimage["ci_disk_rsize"];
		$this->state = $cloudimage["ci_state"];
	}
	return $this;
}

// returns an cloudimage from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an cloudimage from the db selected by the image_id
function get_instance_by_image_id($image_id) {
	$this->get_instance("", $image_id);
	return $this;
}

// ---------------------------------------------------------------------------------
// general cloudimage methods
// ---------------------------------------------------------------------------------




// checks if given cloudimage id is free in the db
function is_id_free($cloudimage_id) {
	global $CLOUD_IMAGE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select ci_id from $CLOUD_IMAGE_TABLE where ci_id=$cloudimage_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudimage to the database
function add($cloudimage_fields) {
	global $CLOUD_IMAGE_TABLE;
	global $event;
	if (!is_array($cloudimage_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", "cloudimage_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_IMAGE_TABLE, $cloudimage_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", "Failed adding new cloudimage to database", "", "", 0, 0, 0);
	}
}



// removes cloudimage from the database
function remove($cloudimage_id) {
	global $CLOUD_IMAGE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_IMAGE_TABLE where ci_id=$cloudimage_id");
}


// updates a cloudimage
function update($ci_id, $ci_fields) {
    global $CLOUD_IMAGE_TABLE;
    global $event;
    if ($ci_id < 0 || ! is_array($ci_fields)) {
        $this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", "Unable to update Cloudimage $ci_id", "", "", 0, 0, 0);
        return 1;
    }
    $db=openqrm_get_db_connection();
    unset($ci_fields["ci_id"]);
    $result = $db->AutoExecute($this->_db_table, $ci_fields, 'UPDATE', "ci_id = $ci_id");
    if (! $result) {
        $this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", "Failed updating Cloudimage $ci_id", "", "", 0, 0, 0);
    }
}



// sets the state of a cloudimage
function set_state($cloudimage_id, $state_str) {
	global $CLOUD_IMAGE_TABLE;
	global $event;
	$cloudimage_state = 0;
	switch ($state_str) {
		case "remove":
			$cloudimage_state = 0;
			break;
		case "active":
			$cloudimage_state = 1;
			break;
		case "resizing":
			$cloudimage_state = 2;
			break;
	}
	$db=openqrm_get_db_connection();
	$cloudimage_set = &$db->Execute("update $CLOUD_IMAGE_TABLE set ci_state=$cloudimage_state where ci_id=$cloudimage_id");
	if (!$cloudimage_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// sets the resource of a cloudimage
function set_resource($cloudimage_id, $resource_id) {
	global $CLOUD_IMAGE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudimage_set = &$db->Execute("update $CLOUD_IMAGE_TABLE set ci_resource_id=$resource_id where ci_id=$cloudimage_id");
	if (!$cloudimage_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// returns the number of cloudimages for an cloudimage type
function get_count() {
	global $CLOUD_IMAGE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(ci_id) as num from $CLOUD_IMAGE_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudimage names
function get_list() {
	global $CLOUD_IMAGE_TABLE;
	$query = "select ci_id, ci_cr_id from $CLOUD_IMAGE_TABLE";
	$cloudimage_name_array = array();
	$cloudimage_name_array = openqrm_db_get_result_double ($query);
	return $cloudimage_name_array;
}


// returns a list of all cloudimage ids
function get_all_ids() {
	global $CLOUD_IMAGE_TABLE;
	global $event;
	$cloudimage_list = array();
	$query = "select ci_id from $CLOUD_IMAGE_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudimage_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudimage_list;

}




// displays the cloudimage-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_IMAGE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_IMAGE_TABLE order by $sort $order", $limit, $offset);
	$cloudimage_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudimage_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}		
	return $cloudimage_array;
}









// ---------------------------------------------------------------------------------

}


?>