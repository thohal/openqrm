<?php
/**
 * @package openQRM
 */

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


	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
	require_once "$RootDir/include/openqrm-server-config.php";
	require_once "$RootDir/include/openqrm-database-functions.php";
	require_once "$RootDir/class/event.class.php";

/**
 * This class represents an linuxceo resource in auto install mode
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class linuxcoeresource
{

/**
* linuxcoeresource id
* @access protected
* @var int
*/
var $id = '';
/**
* linuxcoeresource resource_id
* @access protected
* @var string
*/
var $resource_id = '';
/**
* time when the resource entered the auto-install mode
* @access protected
* @var string
*/
var $install_time = '';
/**
* name of the auto-install profile
* @access protected
* @var string
*/
var $profile_name = '';


/**
* name of database table
* @access protected
* @var string
*/
var $_db_table;
/**
* path to openqrm basedir
* @access protected
* @var string
*/
var $_base_dir;
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
	function linuxcoeresource() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = "linuxcoe_resources";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an linuxcoeresource object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $resource_id) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$linuxcoeresource_array = &$db->Execute("select * from $this->_db_table where linuxcoe_id=$id");
		} else if ("$resource_id" != "") {
			$linuxcoeresource_array = &$db->Execute("select * from $this->_db_table where linuxcoe_resource_id=$id");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "linuxcoeresource.class.php", "Could not create instance of linuxcoeresource without data", "", "", 0, 0, 0);
			exit(-1);
		}
		foreach ($linuxcoeresource_array as $index => $linuxcoeresource) {
			$this->id = $linuxcoeresource["linuxcoe_id"];
			$this->resource_id = $linuxcoeresource["linuxcoe_resource_id"];
			$this->install_time = $linuxcoeresource["linuxcoe_install_time"];
			$this->profile_name = $linuxcoeresource["linuxcoe_profile_name"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an linuxcoeresource by id
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
	* get an instance of an linuxcoeresource by resource_id
	* @access public
	* @param int $resource_id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_resource_id($resource_id) {
		$this->get_instance("", $resource_id);
		return $this;
	}

	//--------------------------------------------------
	/**
	* add a new linuxcoeresource
	* @access public
	* @param array $linuxcoeresource_fields
	*/
	//--------------------------------------------------
	function add($linuxcoeresource_fields) {
		if (!is_array($linuxcoeresource_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "linuxcoeresource.class.php", "Image_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $linuxcoeresource_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "linuxcoeresource.class.php", "Failed adding new linuxcoeresource to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an linuxcoeresource
	* <code>
	* $fields = array();
	* $fields['linuxcoeresource_name'] = 'somename';
	* $fields['linuxcoeresource_uri'] = 'some-uri';
	* $linuxcoeresource = new linuxcoeresource();
	* $linuxcoeresource->update(1, $fields);
	* </code>
	* @access public
	* @param int $linuxcoeresource_id
	* @param array $linuxcoeresource_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($linuxcoeresource_id, $linuxcoeresource_fields) {
		if ($linuxcoeresource_id < 0 || ! is_array($linuxcoeresource_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "linuxcoeresource.class.php", "Unable to update linuxcoeresource $linuxcoeresource_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($linuxcoeresource_fields["linuxcoeresource_id"]);
		$result = $db->AutoExecute($this->_db_table, $linuxcoeresource_fields, 'UPDATE', "linuxcoeresource_id = $linuxcoeresource_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "linuxcoeresource.class.php", "Failed updating linuxcoeresource $linuxcoeresource_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an linuxcoeresource by id
	* @access public
	* @param int $linuxcoeresource_id
	*/
	//--------------------------------------------------
	function remove($linuxcoe_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where linuxcoe_id=$linuxcoe_id");
	}

	//--------------------------------------------------
	/**
	* remove an linuxcoeresource by resource_id
	* @access public
	* @param int $linuxcoe_resource_id
	*/
	//--------------------------------------------------
	function remove_by_resource_id($linuxcoe_resource_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where linuxcoe_resource_id='$linuxcoe_resource_id'");
	
	}



	//--------------------------------------------------
	/**
	* get an array of all linuxcoeresource names
	* <code>
	* $linuxcoeresource = new linuxcoeresource();
	* $arr = $linuxcoeresource->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select linuxcoe_id, linuxcoe_resource_id from $this->_db_table order by linuxcoe_id ASC";
		$linuxcoeresource_name_array = array();
		$linuxcoeresource_name_array = openqrm_db_get_result_double ($query);
		return $linuxcoeresource_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all linuxcoeresource ids
	* <code>
	* $linuxcoeresource = new linuxcoeresource();
	* $arr = $linuxcoeresource->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$linuxcoeresource_array = array();
		$query = "select linuxcoe_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "linuxcoeresource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$linuxcoeresource_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $linuxcoeresource_array;
	}

	//--------------------------------------------------
	/**
	* get an array of linuxcoeresources
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
		$linuxcoeresource_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "linuxcoeresource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($linuxcoeresource_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}		
		return $linuxcoeresource_array;
	}


}
?>
