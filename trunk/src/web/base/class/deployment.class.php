<?php
/**
 * @package openQRM
 */

	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
	require_once "$RootDir/include/openqrm-database-functions.php";
	require_once "$RootDir/class/event.class.php";

/**
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @version 1.1 added documentation
 */
class deployment
{

/**
* deployment id
* @access protected
* @var int
*/
var $id = '';
/**
* deployment name
* @access protected
* @var string
*/
var $name = '';
/**
* deployment type
* @access protected
* @var string
*/
var $type = '';
/**
* deployment description
* @access protected
* @var string
*/
var $description = '';
/**
* deployment storagetype
* @access protected
* @var string
*/
var $storagetype = '';
/**
* deployment storagedescription
* @access protected
* @var string
*/
var $storagedescription = '';
/**
* deployment mapping
* @access protected
* @var string
*/
var $mapping = '';

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
	function deployment() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init deployment environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $DEPLOYMENT_INFO_TABLE;
		$this->_db_table = $DEPLOYMENT_INFO_TABLE;
		$this->_event = new event();
	}

	//--------------------------------------------------
	/**
	* get an instance of a deployment object from db
	* @access public
	* @param int $id
	* @param string $name
	* @param string $type
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name, $type) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$deployment_array = &$db->Execute("select * from $this->_db_table where deployment_id=$id");
		} else if ("$name" != "") {
			$deployment_array = &$db->Execute("select * from $this->_db_table where deployment_name='$name'");
		} else if ("$type" != "") {
			$deployment_array = &$db->Execute("select * from $this->_db_table where deployment_type='$type'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "deployment.class.php", "Could not create instance of deployment without data", "", "", 0, 0, 0);
			exit(-1);
		}
		foreach ($deployment_array as $index => $deployment) {
			$this->id = $deployment["deployment_id"];
			$this->name = $deployment["deployment_name"];
			$this->type = $deployment["deployment_type"];
			$this->description = $deployment["deployment_description"];
			$this->storagetype = $deployment["deployment_storagetype"];
			$this->storagedescription = $deployment["deployment_storagedescription"];
			$this->mapping = $deployment["deployment_mapping"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of a deployment object by id
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_id($id) {
		$this->get_instance($id, "", "");
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of a deployment object by name
	* @access public
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_name($name) {
		$this->get_instance("", $name, "");
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of a deployment object by type
	* @access public
	* @param string $type
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_type($type) {
		$this->get_instance("", "", $type);
		return $this;
	}

	//--------------------------------------------------
	/**
	* add a deployment
	* <code>
	* $fields = array();
	* $fields['deployment_name'] = 'somename';
	* $fields['deployment_type'] = 'sometext';
	* $fields['deployment_description'] = 'sometext';
	* $fields['deployment_storagetype'] = 'sometext';
	* $fields['deployment_storagedescription'] = 'sometext';
	* $fields['deployment_mapping'] = 'sometext';
	* $deployment = new deployment();
	* $deployment->add($fields);
	* </code>
	* @access public
	* @param array $deployment_fields
	* @return bool
	*/
	//--------------------------------------------------
	function add($deployment_fields) {
		if (!is_array($deployment_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "deployment.class.php", "Deployment_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $deployment_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "deployment.class.php", "Failed adding new deployment to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove deployment by id
	* @access public
	* @param int $deployment_id
	*/
	//--------------------------------------------------
	function remove($deployment_id) {
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where deployment_id=$deployment_id");
	}

	//--------------------------------------------------
	/**
	* remove deployment by deployment_type
	* @access public
	* @param int $deployment_id
	*/
	//--------------------------------------------------
	function remove_by_type($type) {
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where deployment_type='$type'");
	}

	//--------------------------------------------------
	/**
	* get an array of all deployment names
	* <code>
	* $deployment = new deployment();
	* $arr = $deployment->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select deployment_id, deployment_name from $this->_db_table";
		$deployment_name_array = array();
		$deployment_name_array = openqrm_db_get_result_double ($query);
		return $deployment_name_array;
	}

	//--------------------------------------------------
	/**
	* get an array of all deployment descriptions
	* <code>
	* $deployment = new deployment();
	* $arr = $deployment->get_description_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_description_list() {
		$query = "select deployment_id, deployment_description from $this->_db_table";
		$deployment_name_array = array();
		$deployment_name_array = openqrm_db_get_result_double ($query);
		return $deployment_name_array;
	}

	//--------------------------------------------------
	/**
	* get an array of all deployment storage descriptions
	* <code>
	* $deployment = new deployment();
	* $arr = $deployment->get_storagedescription_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_storagedescription_list() {
		$query = "select deployment_id, deployment_storagedescription from $this->_db_table";
		$deployment_name_array = array();
		$deployment_name_array = openqrm_db_get_result_double ($query);
		return $deployment_name_array;
	}

	//--------------------------------------------------
	/**
	* get an array of all deployment storagetypes
	* <code>
	* $deployment = new deployment();
	* $arr = $deployment->get_storagetype_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_storagetype_list() {
		$query = "select deployment_id, deployment_storagetype from $this->_db_table";
		$ar_Return = array();
		$ar_tmp = array();
		$ar_result = openqrm_db_get_result_double ($query);

		foreach($ar_result as $val) {
			if($val['label'] != 'none') {
				$ar_tmp[] = $val['label'];
			}
		}
		$ar_tmp = array_unique($ar_tmp);

		foreach($ar_tmp as $val) {
			$ar_Return[] = array('label' => $val, 'value' => $val);
		}
	
		return $ar_Return;
	}

	//--------------------------------------------------
	/**
	* get id by deployment storagetype
	* <code>
	* $deployment = new deployment();
	* $arr = $deployment->get_id_by_storagetype();
	* // $arr['value']
	* // $arr['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_id_by_storagetype($type) {
		$query = "select deployment_id from $this->_db_table where deployment_storagetype=\"$type\"";
		$ar_Return = array();
		$ar_Return = openqrm_db_get_result_double($query);
		return $ar_Return;
	}



	//--------------------------------------------------
	/**
	* parse deployment parameters
	* <code>
	* $deployment = new deployment();
	* $id = $deployment->parse_deployment_parameter("id", $paramstr);
	* $ip = $deployment->parse_deployment_parameter("ip", $paramstr);
	* $path = $deployment->parse_deployment_parameter("path", $paramstr);
	* </code>
	* @access public
	* @return string
	*/
	//--------------------------------------------------
	function parse_deployment_parameter($key, $paramstr) {
		$ip1=trim($paramstr);
		$ipos=strpos($ip1, ':');
		$ip_storage_id=substr($ip1, 0, $ipos);
		$ipr=substr($ip1, $ipos+1);
		$ipos1=strpos($ipr, ':');
		$ip_storage_ip=substr($ipr, 0, $ipos1);
		$ip_image_rootdevice=substr($ipr, $ipos1+1);
		switch ($key) {
			case "id":
				return $ip_storage_id;
				break;
			case "ip":
				return $ip_storage_ip;
				break;
			case "path":
				return $ip_image_rootdevice;
				break;
		}
	}



}
?>
