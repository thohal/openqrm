<?php

// This class represents a NetApp storage server in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";


$NETAPP_STORAGE_SERVER_TABLE="netapp_storage_servers";
global $NETAPP_STORAGE_SERVER_TABLE;
$event = new event();
global $event;

class netapp_storage {

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
	function netapp_storage() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $NETAPP_STORAGE_SERVER_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $NETAPP_STORAGE_SERVER_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}



	// ---------------------------------------------------------------------------------
	// methods to create an instance of a netapp_storage object filled from the db
	// ---------------------------------------------------------------------------------

	// returns an appliance from the db selected by id or storage_id
	function get_instance($id, $na_storage_id) {
		global $NETAPP_STORAGE_SERVER_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$netapp_storage_array = &$db->Execute("select * from $NETAPP_STORAGE_SERVER_TABLE where na_id=$id");
		} else if ("$na_storage_id" != "") {
            $netapp_storage_array = &$db->Execute("select * from $NETAPP_STORAGE_SERVER_TABLE where na_storage_id=$na_storage_id");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "coulduser.class.php", "Could not create instance of equalogic_storage without data", "", "", 0, 0, 0);
			return;
		}

		foreach ($netapp_storage_array as $index => $netapp_storage) {
			$this->id = $netapp_storage["na_id"];
			$this->storage_id = $netapp_storage["na_storage_id"];
			$this->storage_name = $netapp_storage["na_storage_name"];
			$this->storage_user = $netapp_storage["na_storage_user"];
			$this->storage_password = $netapp_storage["na_storage_password"];
			$this->storage_comment = $netapp_storage["na_storage_comment"];
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
	// general netapp_storage methods
	// ---------------------------------------------------------------------------------




	// checks if given netapp_storage id is free in the db
	function is_id_free($netapp_storage_id) {
		global $NETAPP_STORAGE_SERVER_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$rs = &$db->Execute("select na_id from $NETAPP_STORAGE_SERVER_TABLE where na_id=$netapp_storage_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "netapp_storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// adds netapp_storage to the database
	function add($netapp_storage_fields) {
		global $NETAPP_STORAGE_SERVER_TABLE;
		global $event;
		if (!is_array($netapp_storage_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "netapp_storage.class.php", "netapp_storage_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($NETAPP_STORAGE_SERVER_TABLE, $netapp_storage_fields, 'INSERT');
		if (! $result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "netapp_storage.class.php", "Failed adding new netapp_storage to database", "", "", 0, 0, 0);
		}
	}



	// removes netapp_storage from the database
	function remove($netapp_storage_id) {
		global $NETAPP_STORAGE_SERVER_TABLE;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $NETAPP_STORAGE_SERVER_TABLE where na_id=$netapp_storage_id");
	}

	// removes netapp_storage from the database by netapp_storage_name
	function remove_by_cu_id($storage_id) {
		global $NETAPP_STORAGE_SERVER_TABLE;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $NETAPP_STORAGE_SERVER_TABLE where na_storage_id='$storage_id'");
	}


	// updates netapp_storage for a cloud user
	function update($na_id, $na_fields) {
		global $NETAPP_STORAGE_SERVER_TABLE;
		global $event;
		if ($cl_id < 0 || ! is_array($na_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "netapp_storage.class.php", "Unable to update EqualLogic Storage server $na_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($na_fields["na_id"]);
		$result = $db->AutoExecute($this->_db_table, $na_fields, 'UPDATE', "na_id = $na_id");
    	$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "netapp_storage.class.php", "!!! updating $this->_db_table", "", "", 0, 0, 0);
	if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "netapp_storage.class.php", "Failed updating EqualLogic Storage server $na_id", "", "", 0, 0, 0);
		}
	}




	// returns the number of netapp_storages
	function get_count() {
		global $NETAPP_STORAGE_SERVER_TABLE;
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(na_id) as num from $NETAPP_STORAGE_SERVER_TABLE");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all netapp_storage names
	function get_list() {
		global $NETAPP_STORAGE_SERVER_TABLE;
		$query = "select na_id, na_storage_id from $NETAPP_STORAGE_SERVER_TABLE";
		$netapp_storage_name_array = array();
		$netapp_storage_name_array = openqrm_db_get_result_double ($query);
		return $netapp_storage_name_array;
	}


	// returns a list of all netapp_storage ids
	function get_all_ids() {
		global $NETAPP_STORAGE_SERVER_TABLE;
		global $event;
		$netapp_storage_list = array();
		$query = "select na_id from $NETAPP_STORAGE_SERVER_TABLE";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "netapp_storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$netapp_storage_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $netapp_storage_list;

	}




	// displays the netapp_storage-overview
	function display_overview($offset, $limit, $sort, $order) {
		global $NETAPP_STORAGE_SERVER_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $NETAPP_STORAGE_SERVER_TABLE order by $sort $order", $limit, $offset);
		$netapp_storage_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "netapp_storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($netapp_storage_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $netapp_storage_array;
	}









// ---------------------------------------------------------------------------------

}

?>


