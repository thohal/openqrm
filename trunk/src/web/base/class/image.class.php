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
 * This class represents a filesystem-image (rootfs) 
 * In combination with a kernel it can be deployed to a resource
 * via the appliance.class
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @version 1.1 added documentation
 */
class image
{

/**
* image id
* @access protected
* @var int
*/
var $id = '';
/**
* image name
* @access protected
* @var string
*/
var $name = '';
/**
* image version
* @access protected
* @var string
*/
var $version = '';
/**
* image type
* @access protected
* @var string
*/
var $type = '';
/**
* image rootdevice
* @access protected
* @var string
*/
var $rootdevice = '';
/**
* image root filesystem
* @access protected
* @var string
*/
var $rootfstype = '';
/**
* storage id
* @access protected
* @var int
*/
var $storageid = '';
/**
* deployment parameter
* @access protected
* @var string
*/
var $deployment_parameter = '';
/**
* image is shared?
* @access protected
* @var bool
*/
var $isshared = '';
/**
* image comment
* @access protected
* @var string
*/
var $comment = '';
/**
* image capabilities
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
	function image() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $IMAGE_INFO_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $IMAGE_INFO_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an image object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$image_array = &$db->Execute("select * from $this->_db_table where image_id=$id");
		} else if ("$name" != "") {
			$image_array = &$db->Execute("select * from $this->_db_table where image_name='$name'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Could not create instance of image without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($image_array as $index => $image) {
			$this->id = $image["image_id"];
			$this->name = $image["image_name"];
			$this->version = $image["image_version"];
			$this->type = $image["image_type"];
			$this->rootdevice = $image["image_rootdevice"];
			$this->rootfstype = $image["image_rootfstype"];
			$this->storageid = $image["image_storageid"];
			$this->deployment_parameter = $image["image_deployment_parameter"];
			$this->isshared = $image["image_isshared"];
			$this->comment = $image["image_comment"];
			$this->capabilities = $image["image_capabilities"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an image by id
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
	* get an instance of an image by name
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
	* add a new image
	* @access public
	* @param array $image_fields
	*/
	//--------------------------------------------------
	function add($image_fields) {
		if (!is_array($image_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Image_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $image_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Failed adding new image to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an image
	* <code>
	* $fields = array();
	* $fields['image_name'] = 'somename';
	* $fields['image_version'] = '1.1';
	* $fields['image_type'] = 1;
	* $fields['image_rootdevice'] = 1;
	* $fields['image_rootfstype'] = 1;
	* $fields['image_storageid'] = 1;
	* $fields['image_deployment_parameter'] = 1;
	* $fields['image_isshared'] = 1;
	* $fields['image_comment'] = 'sometext';
	* $fields['image_capabilities'] = 'sometext';
	* $image = new image();
	* $image->update(1, $fields);
	* </code>
	* @access public
	* @param int $image_id
	* @param array $image_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($image_id, $image_fields) {
		if ($image_id < 0 || ! is_array($image_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Unable to update image $image_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($image_fields["image_id"]);
		$result = $db->AutoExecute($this->_db_table, $image_fields, 'UPDATE', "image_id = $image_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Failed updating image $image_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an image by id
	* @access public
	* @param int $image_id
	*/
	//--------------------------------------------------
	function remove($image_id) {
		// remove auth file
		$CMD="rm -f $this->_base_dir/openqrm/web/action/image-auth/iauth.$image_id";
		exec($CMD);
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where image_id=$image_id");
	}

	//--------------------------------------------------
	/**
	* remove an image by name
	* @access public
	* @param string $image_name
	*/
	//--------------------------------------------------
	function remove_by_name($image_name) {
		// remove auth file
		$rem_image = new image();
		$rem_image->get_instance_by_name($image_name);
		$rem_image_id = $rem_image->id;
		$CMD="rm -f $this->_base_dir/openqrm/web/action/image-auth/iauth.$rem_image_id";
		exec($CMD);
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where image_name='$image_name'");
	
	}

	//--------------------------------------------------
	/**
	* get image name by id
	* @access public
	* @param int $image_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($image_id) {
		$db=openqrm_get_db_connection();
		$image_set = &$db->Execute("select image_name from $this->_db_table where image_id=$image_id");
		if (!$image_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$image_set->EOF) {
				return $image_set->fields["image_name"];
			} else {
				return "idle";
			}
		}
	}




	//--------------------------------------------------
	/**
	* set the deployment parameters of an image
	* @access public
	* @param string $key
	* @param string $value
	*/
	//--------------------------------------------------
	function set_deployment_parameters($key, $value) {
        $this->get_instance_by_id($this->id);
		$image_deployment_parameter = $this->deployment_parameter;
		$key=trim($key);
		if (strstr($image_deployment_parameter, $key)) {
			// change
			$cp1=trim($image_deployment_parameter);
			$cp2 = strstr($cp1, $key);
			$keystr="$key=\"";
			$endmark="\"";
			$cp3=str_replace($keystr, "", $cp2);
            $endpos=strpos($cp3, $endmark);
			$cp=substr($cp3, 0, $endpos);
			$new_image_deployment_parameter = str_replace("$key=\"$cp\"", "$key=\"$value\"", $image_deployment_parameter);
		} else {
			// add
			$new_image_deployment_parameter = "$image_deployment_parameter $key=\"$value\"";
		}
		$image_fields=array();
		$image_fields["image_deployment_parameter"]="$new_image_deployment_parameter";
		$this->update($this->id, $image_fields);

	}



	//--------------------------------------------------
	/**
	* gets a deployment parameter of an image
	* @access public
	* @param string $key
	* @return string $value
	*/
	//--------------------------------------------------
	function get_deployment_parameter($key) {

		$image_deployment_parameter = $this->deployment_parameter;
		$key=trim($key);
		if (strstr($image_deployment_parameter, $key)) {
			// change
			$cp1=trim($image_deployment_parameter);
			$cp2 = strstr($cp1, $key);
			$keystr="$key=\"";
			$endmark="\"";
			$cp3=str_replace($keystr, "", $cp2);
			$endpos=strpos($cp3, $endmark);
			$cp=substr($cp3, 0, $endpos);
			return $cp;
		} else {
			return "";
		}
	}






	//--------------------------------------------------
	/**
	* get image capabilities by id
	* @access public
	* @param int $image_id
	* @return string
	*/
	//--------------------------------------------------
	function get_capabilities($image_id) {
		$db=openqrm_get_db_connection();
		$image_set = &$db->Execute("select image_capabilities from $this->_db_table where image_id=$image_id");
		if (!$image_set) {
			$this->_event->log("get_capabilities", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if ((!$image_set->EOF) && ($image_set->fields["image_capabilities"]!=""))  {
				return $image_set->fields["image_capabilities"];
			} else {
				return "0";
			}
		}
	}

	//--------------------------------------------------
	/**
	* get number of images
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(image_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}

	//--------------------------------------------------
	/**
	* get an array of all image names
	* <code>
	* $image = new image();
	* $arr = $image->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select image_id, image_name from $this->_db_table order by image_id ASC";
		$image_name_array = array();
		$image_name_array = openqrm_db_get_result_double ($query);
		return $image_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all image ids
	* <code>
	* $image = new image();
	* $arr = $image->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$image_array = array();
		$query = "select image_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$image_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $image_array;
	}

	//--------------------------------------------------
	/**
	* get an array of images
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
		$recordSet = &$db->SelectLimit("select * from $this->_db_table where image_id > 1 order by $sort $order", $limit, $offset);
		$image_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($image_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}		
		return $image_array;
	}

	//--------------------------------------------------
	/**
	* generate a random password for images
	* @access public
	* @param int $length
	* @return string
	*/
	//--------------------------------------------------
	function generatePassword ($length) {
		// start with a blank password
		$password = "";
		// define possible characters
		$possible = "0123456789bcdfghjkmnpqrstvwxyz"; 
		// set up a counter
		$i = 0; 
		// add random characters to $password until $length is reached
		while ($i < $length) { 
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
			// we don't want this character if it's already in the password
			if (!strstr($password, $char)) { 
				$password .= $char;
				$i++;
			}
		}
	  // done!
	  return $password;
	}

	//--------------------------------------------------
	/**
	* set crypted root-password from string
	* @access public
	* @param int $id
	* @param string $passwd
	*/
	//--------------------------------------------------
	function set_root_password($id, $passwd) {
		$CMD="$this->_base_dir/openqrm/sbin/openqrm-crypt $passwd > $this->_base_dir/openqrm/web/action/image-auth/iauth.$id";
		exec($CMD);
	}

}
?>
