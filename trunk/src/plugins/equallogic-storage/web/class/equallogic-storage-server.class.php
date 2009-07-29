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


// This class represents a EqualLogic storage server in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";


$EQUALLOGIC_STORAGE_SERVER_TABLE="equallogic_storage_servers";
global $EQUALLOGIC_STORAGE_SERVER_TABLE;
$event = new event();
global $event;

class equallogic_storage {

	var $id = '';
	var $storage_id = '';
	var $storage_name = '';
	var $storage_user = '';
	var $storage_password = '';
	var $storage_comment = '';

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function equallogic_storage() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $EQUALLOGIC_STORAGE_SERVER_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}



	// ---------------------------------------------------------------------------------
	// methods to create an instance of a equallogic_storage object filled from the db
	// ---------------------------------------------------------------------------------

	// returns an appliance from the db selected by id or storage_id
	function get_instance($id, $eq_storage_id) {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$equallogic_storage_array = &$db->Execute("select * from $EQUALLOGIC_STORAGE_SERVER_TABLE where eq_id=$id");
		} else if ("$eq_storage_id" != "") {
            $equallogic_storage_array = &$db->Execute("select * from $EQUALLOGIC_STORAGE_SERVER_TABLE where eq_storage_id=$eq_storage_id");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "coulduser.class.php", "Could not create instance of equalogic_storage without data", "", "", 0, 0, 0);
			return;
		}

		foreach ($equallogic_storage_array as $index => $equallogic_storage) {
			$this->id = $equallogic_storage["eq_id"];
			$this->storage_id = $equallogic_storage["eq_storage_id"];
			$this->storage_name = $equallogic_storage["eq_storage_name"];
			$this->storage_user = $equallogic_storage["eq_storage_user"];
			$this->storage_password = $equallogic_storage["eq_storage_password"];
			$this->storage_comment = $equallogic_storage["eq_storage_comment"];
		}
		return $this;
	}


	// returns an appliance from the db selected by id
	function get_instance_by_id($id) {
		$this->get_instance($id, "");
		return $this;
	}

	// returns an appliance from the db selected by cu_id
	function get_instance_by_storage_id($storage_id) {
		$this->get_instance("", $storage_id);
		return $this;
	}


	// ---------------------------------------------------------------------------------
	// general equallogic_storage methods
	// ---------------------------------------------------------------------------------




	// checks if given equallogic_storage id is free in the db
	function is_id_free($equallogic_storage_id) {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$rs = &$db->Execute("select eq_id from $EQUALLOGIC_STORAGE_SERVER_TABLE where eq_id=$equallogic_storage_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "equallogic_storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// adds equallogic_storage to the database
	function add($equallogic_storage_fields) {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		global $event;
		if (!is_array($equallogic_storage_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "equallogic_storage.class.php", "equallogic_storage_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($EQUALLOGIC_STORAGE_SERVER_TABLE, $equallogic_storage_fields, 'INSERT');
		if (! $result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "equallogic_storage.class.php", "Failed adding new equallogic_storage to database", "", "", 0, 0, 0);
		}
	}



	// removes equallogic_storage from the database
	function remove($equallogic_storage_id) {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $EQUALLOGIC_STORAGE_SERVER_TABLE where eq_id=$equallogic_storage_id");
	}

	// removes equallogic_storage from the database by equallogic_storage_name
	function remove_by_cu_id($storage_id) {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $EQUALLOGIC_STORAGE_SERVER_TABLE where eq_storage_id='$storage_id'");
	}


	// updates equallogic_storage for a cloud user
	function update($eq_id, $eq_fields) {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		global $event;
		if ($cl_id < 0 || ! is_array($eq_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "equallogic_storage.class.php", "Unable to update EqualLogic Storage server $eq_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($eq_fields["eq_id"]);
		$result = $db->AutoExecute($this->_db_table, $eq_fields, 'UPDATE', "eq_id = $eq_id");
    	$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "equallogic_storage.class.php", "!!! updating $this->_db_table", "", "", 0, 0, 0);
	if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "equallogic_storage.class.php", "Failed updating EqualLogic Storage server $eq_id", "", "", 0, 0, 0);
		}
	}




	// returns the number of equallogic_storages
	function get_count() {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(eq_id) as num from $EQUALLOGIC_STORAGE_SERVER_TABLE");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all equallogic_storage names
	function get_list() {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		$query = "select eq_id, eq_storage_id from $EQUALLOGIC_STORAGE_SERVER_TABLE";
		$equallogic_storage_name_array = array();
		$equallogic_storage_name_array = openqrm_db_get_result_double ($query);
		return $equallogic_storage_name_array;
	}


	// returns a list of all equallogic_storage ids
	function get_all_ids() {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		global $event;
		$equallogic_storage_list = array();
		$query = "select eq_id from $EQUALLOGIC_STORAGE_SERVER_TABLE";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "equallogic_storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$equallogic_storage_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $equallogic_storage_list;

	}




	// displays the equallogic_storage-overview
	function display_overview($offset, $limit, $sort, $order) {
		global $EQUALLOGIC_STORAGE_SERVER_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $EQUALLOGIC_STORAGE_SERVER_TABLE order by $sort $order", $limit, $offset);
		$equallogic_storage_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "equallogic_storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($equallogic_storage_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $equallogic_storage_array;
	}









// ---------------------------------------------------------------------------------

}

?>


