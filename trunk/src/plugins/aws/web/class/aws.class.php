<?php
/**
 * @package openQRM
 */

	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
	require_once "$RootDir/include/openqrm-server-config.php";
	require_once "$RootDir/include/openqrm-database-functions.php";
	require_once "$RootDir/class/event.class.php";

/**
 * This class represents an aws object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class aws
{

/**
* aws id
* @access protected
* @var int
*/
var $id = '';
/**
* aws account_name
* @access protected
* @var string
*/
var $account_name = '';
/**
* aws account_number
* @access protected
* @var string
*/
var $account_number = '';
/**
* java_home
* @access protected
* @var string
*/
var $java_home = '';
/**
* ec2_home
* @access protected
* @var string
*/
var $ec2_home = '';
/**
* ami_home
* @access protected
* @var string
*/
var $ami_home = '';
/**
* ec2_private_key
* @access protected
* @var string
*/
var $ec2_private_key = '';
/**
* ec2_cert
* @access protected
* @var string
*/
var $ec2_cert = '';
/**
* ec2_url
* @access protected
* @var string
*/
var $ec2_region = '';

/**
* ec2_ssh_key
* @access protected
* @var string
*/
var $ec2_ssh_key= '';

/**
* access_key
* @access protected
* @var string
*/
var $access_key= '';

/**
* secret_access_key
* @access protected
* @var string
*/
var $secret_access_key= '';


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
	function aws() {
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
		$this->_db_table = "openqrm_aws";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an aws object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$aws_array = &$db->Execute("select * from $this->_db_table where aws_id=$id");
		} else if ("$name" != "") {
			$aws_array = &$db->Execute("select * from $this->_db_table where aws_account_name='$name'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "aws.class.php", "Could not create instance of aws without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($aws_array as $index => $aws) {
			$this->id = $aws["aws_id"];
			$this->account_name = $aws["aws_account_name"];
			$this->account_number = $aws["aws_account_number"];
			$this->java_home = $aws["aws_java_home"];
			$this->ec2_home = $aws["aws_ec2_home"];
			$this->ami_home = $aws["aws_ami_home"];
			$this->ec2_private_key = $aws["aws_ec2_private_key"];
			$this->ec2_cert = $aws["aws_ec2_cert"];
			$this->ec2_region = $aws["aws_ec2_region"];
			$this->ec2_ssh_key = $aws["aws_ec2_ssh_key"];
			$this->access_key = $aws["aws_access_key"];
			$this->secret_access_key = $aws["aws_secret_access_key"];

		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an aws by id
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
	* get an instance of an aws by name
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
	* add a new aws
	* @access public
	* @param array $aws_fields
	*/
	//--------------------------------------------------
	function add($aws_fields) {
		if (!is_array($aws_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "aws.class.php", "Image_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $aws_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "aws.class.php", "Failed adding new aws to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an aws
	* <code>
	* $fields = array();
	* $fields['aws_name'] = 'somename';
	* $fields['aws_uri'] = 'some-uri';
	* $aws = new aws();
	* $aws->update(1, $fields);
	* </code>
	* @access public
	* @param int $aws_id
	* @param array $aws_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($aws_id, $aws_fields) {
		if ($aws_id < 0 || ! is_array($aws_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "aws.class.php", "Unable to update aws $aws_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($aws_fields["aws_id"]);
		$result = $db->AutoExecute($this->_db_table, $aws_fields, 'UPDATE', "aws_id = $aws_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "aws.class.php", "Failed updating aws $aws_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an aws by id
	* @access public
	* @param int $aws_id
	*/
	//--------------------------------------------------
	function remove($aws_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where aws_id=$aws_id");
	}

	//--------------------------------------------------
	/**
	* remove an aws by name
	* @access public
	* @param string $aws_name
	*/
	//--------------------------------------------------
	function remove_by_name($aws_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where aws_account_name='$aws_name'");
	
	}

	//--------------------------------------------------
	/**
	* get aws name by id
	* @access public
	* @param int $aws_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($aws_id) {
		$db=openqrm_get_db_connection();
		$aws_set = &$db->Execute("select aws_account_name from $this->_db_table where aws_id=$aws_id");
		if (!$aws_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "aws.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$aws_set->EOF) {
				return $aws_set->fields["aws_name"];
			} else {
				return "idle";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all aws names
	* <code>
	* $aws = new aws();
	* $arr = $aws->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select aws_id, aws_account_name from $this->_db_table order by aws_id ASC";
		$aws_name_array = array();
		$aws_name_array = openqrm_db_get_result_double ($query);
		return $aws_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all aws ids
	* <code>
	* $aws = new aws();
	* $arr = $aws->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$aws_array = array();
		$query = "select aws_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "aws.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$aws_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $aws_array;
	}

	//--------------------------------------------------
	/**
	* get an array of awss
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
		$aws_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "aws.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($aws_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}		
		return $aws_array;
	}


}
?>
