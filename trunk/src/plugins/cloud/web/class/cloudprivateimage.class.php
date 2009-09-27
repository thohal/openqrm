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


// This class represents a simple relation for private images

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$CLOUD_PRIVATE_IMAGE_TABLE="cloud_private_image";
global $CLOUD_PRIVATE_IMAGE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudprivateimage {

    var $id = '';
    var $image_id = '';
    var $cu_id = '';
    var $comment = '';
    var $state = '';
    var $_db_table;
    var $_base_dir;
    var $_event;

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudprivateimage() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_PRIVATE_IMAGE_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_PRIVATE_IMAGE_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}



// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudprivateimage object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or image_id
function get_instance($id, $image_id) {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudprivateimage_array = &$db->Execute("select * from $CLOUD_PRIVATE_IMAGE_TABLE where co_id=$id");
	} else if ("$image_id" != "") {
		$cloudprivateimage_array = &$db->Execute("select * from $CLOUD_PRIVATE_IMAGE_TABLE where co_image_id=$image_id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", "Could not create instance of cloudprivateimage without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudprivateimage_array as $index => $cloudprivateimage) {
		$this->id = $cloudprivateimage["co_id"];
		$this->image_id = $cloudprivateimage["co_image_id"];
		$this->cu_id = $cloudprivateimage["co_cu_id"];
		$this->comment = $cloudprivateimage["co_comment"];
		$this->state = $cloudprivateimage["co_state"];
	}
	return $this;
}

// returns an cloudprivateimage from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an cloudprivateimage from the db selected by the image_id
function get_instance_by_image_id($image_id) {
	$this->get_instance("", $image_id);
	return $this;
}

// ---------------------------------------------------------------------------------
// general cloudprivateimage methods
// ---------------------------------------------------------------------------------




// checks if given cloudprivateimage id is free in the db
function is_id_free($cloudprivateimage_id) {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select co_id from $CLOUD_PRIVATE_IMAGE_TABLE where co_id=$cloudprivateimage_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudprivateimage to the database
function add($cloudprivateimage_fields) {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	global $event;
	if (!is_array($cloudprivateimage_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", "cloudprivateimage_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_PRIVATE_IMAGE_TABLE, $cloudprivateimage_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", "Failed adding new cloudprivateimage to database", "", "", 0, 0, 0);
	}
}



// removes cloudprivateimage from the database
function remove($cloudprivateimage_id) {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_PRIVATE_IMAGE_TABLE where co_id=$cloudprivateimage_id");
}



// updates a cloudprivateimage
function update($cloudprivateimage_id, $ci_fields) {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
    global $event;
    if ($cloudprivateimage_id < 0 || ! is_array($ci_fields)) {
        $this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", "Unable to update Cloudimage $cloudprivateimage_id", "", "", 0, 0, 0);
        return 1;
    }
    $db=openqrm_get_db_connection();
    unset($ci_fields["co_id"]);
    $result = $db->AutoExecute($CLOUD_PRIVATE_IMAGE_TABLE, $ci_fields, 'UPDATE', "co_id = $cloudprivateimage_id");
    if (! $result) {
        $this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", "Failed updating cloudprivateimage $cloudprivateimage_id", "", "", 0, 0, 0);
    }
}


// sets the state of a cloudprivateimage
function set_state($cloudprivateimage_id, $state_str) {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	global $event;
	$cloudprivateimage_state = 0;
	switch ($state_str) {
		case "remove":
			$cloudprivateimage_state = 0;
			break;
		case "active":
			$cloudprivateimage_state = 1;
			break;
	}
	$db=openqrm_get_db_connection();
	$cloudprivateimage_set = &$db->Execute("update $CLOUD_PRIVATE_IMAGE_TABLE set co_state=$cloudprivateimage_state where co_id=$cloudprivateimage_id");
	if (!$cloudprivateimage_set) {
		$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}



// returns the number of cloudprivateimages for an cloudprivateimage type
function get_count() {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(co_id) as num from $CLOUD_PRIVATE_IMAGE_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudprivateimage names
function get_list() {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	$query = "select co_id, co_image_id from $CLOUD_PRIVATE_IMAGE_TABLE";
	$cloudprivateimage_name_array = array();
	$cloudprivateimage_name_array = openqrm_db_get_result_double ($query);
	return $cloudprivateimage_name_array;
}


// returns a list of all cloudprivateimage ids
function get_all_ids() {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	global $event;
	$cloudprivateimage_list = array();
	$query = "select co_id from $CLOUD_PRIVATE_IMAGE_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudprivateimage_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudprivateimage_list;

}




// displays the cloudprivateimage-overview per clouduser
function display_overview_per_user($cu_id, $order) {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_PRIVATE_IMAGE_TABLE where co_cu_id=$cu_id order by co_cu_id $order", -1, 0);
	$cloudprivateimage_array = array();
	if (!$recordSet) {
		$event->log("display_overview_per_user", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudprivateimage_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudprivateimage_array;
}



// displays the cloudprivateimage-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_PRIVATE_IMAGE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_PRIVATE_IMAGE_TABLE order by $sort $order", $limit, $offset);
	$cloudprivateimage_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudprivateimage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudprivateimage_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudprivateimage_array;
}



// ---------------------------------------------------------------------------------

}


?>