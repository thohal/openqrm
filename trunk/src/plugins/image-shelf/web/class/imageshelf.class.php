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
 * This class represents an image-shelf location
 * It can be either local, nfs, ftp, http, https
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class imageshelf
{

/**
* imageshelf id
* @access protected
* @var int
*/
var $id = '';
/**
* imageshelf name
* @access protected
* @var string
*/
var $name = '';
/**
* username who created the location
* @access protected
* @var string
*/
var $username = '';
/**
* protocol to access the imageshelf
* @access protected
* @var string
*/
var $protocol = '';
/**
* uri for the location
* @access protected
* @var string
*/
var $uri = '';
/**
* username for accessing the imageshelf location
* @access protected
* @var string
*/
var $user = '';
/**
* password for accessing the imageshelf location
* @access protected
* @var string
*/
var $password = '';


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
	function imageshelf() {
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
		$this->_db_table = "image_shelf_locations";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an imageshelf object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$imageshelf_array = &$db->Execute("select * from $this->_db_table where imageshelf_id=$id");
		} else if ("$name" != "") {
			$imageshelf_array = &$db->Execute("select * from $this->_db_table where imageshelf_name='$name'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "imageshelf.class.php", "Could not create instance of imageshelf without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($imageshelf_array as $index => $imageshelf) {
			$this->id = $imageshelf["imageshelf_id"];
			$this->name = $imageshelf["imageshelf_name"];
			$this->username = $imageshelf["imageshelf_username"];
			$this->protocol = $imageshelf["imageshelf_protocol"];
			$this->uri = $imageshelf["imageshelf_uri"];
			$this->user = $imageshelf["imageshelf_user"];
			$this->password = $imageshelf["imageshelf_password"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an imageshelf by id
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
	* get an instance of an imageshelf by name
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_name($name) {
		$this->get_instance("", $name);
		return $this;
	}

	//--------------------------------------------------
	/**
	* add a new imageshelf
	* @access public
	* @param array $imageshelf_fields
	*/
	//--------------------------------------------------
	function add($imageshelf_fields) {
		if (!is_array($imageshelf_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "imageshelf.class.php", "Image_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $imageshelf_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "imageshelf.class.php", "Failed adding new imageshelf to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an imageshelf
	* <code>
	* $fields = array();
	* $fields['imageshelf_name'] = 'somename';
	* $fields['imageshelf_uri'] = 'some-uri';
	* $imageshelf = new imageshelf();
	* $imageshelf->update(1, $fields);
	* </code>
	* @access public
	* @param int $imageshelf_id
	* @param array $imageshelf_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($imageshelf_id, $imageshelf_fields) {
		if ($imageshelf_id < 0 || ! is_array($imageshelf_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "imageshelf.class.php", "Unable to update imageshelf $imageshelf_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($imageshelf_fields["imageshelf_id"]);
		$result = $db->AutoExecute($this->_db_table, $imageshelf_fields, 'UPDATE', "imageshelf_id = $imageshelf_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "imageshelf.class.php", "Failed updating imageshelf $imageshelf_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an imageshelf by id
	* @access public
	* @param int $imageshelf_id
	*/
	//--------------------------------------------------
	function remove($imageshelf_id) {
		// remove auth file
		$CMD="rm -f $this->_base_dir/openqrm/web/action/imageshelf-auth/iauth.$imageshelf_id";
		exec($CMD);
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where imageshelf_id=$imageshelf_id");
	}

	//--------------------------------------------------
	/**
	* remove an imageshelf by name
	* @access public
	* @param string $imageshelf_name
	*/
	//--------------------------------------------------
	function remove_by_name($imageshelf_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where imageshelf_name='$imageshelf_name'");
	
	}

	//--------------------------------------------------
	/**
	* get imageshelf name by id
	* @access public
	* @param int $imageshelf_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($imageshelf_id) {
		$db=openqrm_get_db_connection();
		$imageshelf_set = &$db->Execute("select imageshelf_name from $this->_db_table where imageshelf_id=$imageshelf_id");
		if (!$imageshelf_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "imageshelf.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$imageshelf_set->EOF) {
				return $imageshelf_set->fields["imageshelf_name"];
			} else {
				return "idle";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all imageshelf names
	* <code>
	* $imageshelf = new imageshelf();
	* $arr = $imageshelf->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select imageshelf_id, imageshelf_name from $this->_db_table order by imageshelf_id ASC";
		$imageshelf_name_array = array();
		$imageshelf_name_array = openqrm_db_get_result_double ($query);
		return $imageshelf_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all imageshelf ids
	* <code>
	* $imageshelf = new imageshelf();
	* $arr = $imageshelf->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$imageshelf_array = array();
		$query = "select imageshelf_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "imageshelf.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$imageshelf_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $imageshelf_array;
	}

	//--------------------------------------------------
	/**
	* get an array of imageshelfs
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
		$imageshelf_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "imageshelf.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($imageshelf_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}		
		return $imageshelf_array;
	}


}
?>
