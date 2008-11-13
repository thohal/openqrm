<?php
/**
 * @copyright Copyright (c) 2008, openqrm
 * @license see openqrm licence
 * @package base
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @version 1.1 added documentation
 */

		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		require_once "$RootDir/include/openqrm-database-functions.php";
		require_once "$RootDir/class/event.class.php";


class storage
{

/**
* storage id
* @access protected
* @var int
*/
var $id = '';
/**
* storage name
* @access protected
* @var string
*/
var $name = '';
/**
* resource id used by storage
* @access protected
* @var int
*/
var $resource_id = '';
/**
* storage type
* @access protected
* @var string
*/
var $type = '';
/**
* state of storage
* @access protected
* @var string
*/
var $state = '';
/**
* comment for storage
* @access protected
* @var string
*/
var $comment = '';
/**
* storage capabilities
* @access protected
* @var string
*/
var $capabilities = '';

/**
* name of database table
* @access protected
* @var string
*/
var $_db_table;

/**
* event object
* @access protected
* @var object
*/
var $_event;


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function storage() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $STORAGE_INFO_TABLE;
		$this->_db_table = $STORAGE_INFO_TABLE;
		$this->_event = new event();
	}

	//--------------------------------------------------
	/**
	* get an instance of a storage object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$storage_array = &$db->Execute("select * from $this->_db_table where storage_id=$id");
		} else if ("$name" != "") {
			$storage_array = &$db->Execute("select * from $this->_db_table where storage_name='$name'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", "Could not create instance of storage without data", "", "", 0, 0, 0);
			exit(-1);
		}
		foreach ($storage_array as $index => $storage) {
			$this->id = $storage["storage_id"];
			$this->name = $storage["storage_name"];
			$this->resource_id = $storage["storage_resource_id"];
			$this->type = $storage["storage_type"];
			$this->state = $storage["storage_state"];
			$this->comment = $storage["storage_comment"];
			$this->capabilities = $storage["storage_capabilities"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of a storage by id
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_id($id) {
		$this->get_instance($id, "");
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of a storage by name
	* @access public
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_name($name) {
		$this->get_instance("", $name);
		return $this;
	}

	//--------------------------------------------------
	/**
	* add a new storage
	* @access public
	* @param array $storage_fields
	*/
	//--------------------------------------------------
	function add($storage_fields) {
		if (!is_array($storage_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", "Storage_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $storage_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", "Failed adding new storage to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update a storage
	* <code>
	* $fields = array();
	* $fields['storage_name'] = 'somename';
	* $fields['storage_type'] = 1;
	* $fields['storage_capabilities'] = 'sometext';
	* $fields['storage_comment'] = 'sometext';
	* $fields['storage_resource_id'] = 1;
	* $storage = new storage();
	* $storage->update(1, $fields);
	* </code>
	* @access public
	* @param int $storage_id
	* @param array $storage_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($storage_id, $storage_fields) {
		if ($storage_id < 0 || ! is_array($storage_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", "Unable to update storage $storage_id", "", "", 0, 0, 0);
		}
		$db=openqrm_get_db_connection();
		unset($storage_fields["storage_id"]);
		$result = $db->AutoExecute($this->_db_table, $storage_fields, 'UPDATE', "storage_id = $storage_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", "Failed updating storage $storage_id", "", "", 0, 0, 0);
			return false;
		} else {
			return true;
		}
	}

	//--------------------------------------------------
	/**
	* remove a storage by id
	* @access public
	* @param int $storage_id
	*/
	//--------------------------------------------------
	function remove($storage_id) {
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where storage_id=$storage_id");
	}

	//--------------------------------------------------
	/**
	* remove a storage by name
	* @access public
	* @param string $storage_name
	*/
	//--------------------------------------------------
	function remove_by_name($storage_name) {
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where storage_name='$storage_name'");
	}

	//--------------------------------------------------
	/**
	* get a storage name by id
	* @access public
	* @param int $storage_id
	*/
	//--------------------------------------------------
	function get_name($storage_id) {
		$db=openqrm_get_db_connection();
		$storage_set = &$db->Execute("select storage_name from $this->_db_table where storage_id=$storage_id");
		if (!$storage_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$storage_set->EOF) {
				return $storage_set->fields["storage_name"];
			} else {
				return "idle";
			}
		}
	}

	//--------------------------------------------------
	/**
	* get capabilities string by storage_id
	* @access public
	* @param int $storage_id
	* @return string
	*/
	//--------------------------------------------------
	function get_capabilities($storage_id) {
		$db=openqrm_get_db_connection();
		$storage_set = &$db->Execute("select storage_capabilities from $this->_db_table where storage_id=$storage_id");
		if (!$storage_set) {
			$this->_event->log("get_capabilities", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if ((!$storage_set->EOF) && ($storage_set->fields["storage_capabilities"]!=""))  {
				return $storage_set->fields["storage_capabilities"];
			} else {
				return "0";
			}
		}
	}

	//--------------------------------------------------
	/**
	* get number of storages
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(storage_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}

	//--------------------------------------------------
	/**
	* get an array of all storage names
	* <code>
	* $storage = new storage();
	* $arr = $storage->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select storage_id, storage_name from $this->_db_table";
		$storage_name_array = array();
		$storage_name_array = openqrm_db_get_result_double ($query);
		return $storage_name_array;
	}

	//--------------------------------------------------
	/**
	* get an array of storages
	* @access public
	* @param int $offset
	* @param int $limit
	* @param string $sort
	* @param enum $order [ASC/DESC]
	* @return array
	*/
	//--------------------------------------------------
	function display_overview($offset, $limit, $sort, $order) {
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$storage_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "storage.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($storage_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}		
		return $storage_array;
	}

}
?>
