<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/openqrm/base/include/openqrm-database-functions.php');
		
class User
{
/**
* Nickname
* @access public
* @var string
*/
var $name = '';
/**
* Password
* @access public
* @var string
*/
var $password = '';
/**
* Gender
* @access public
* @var string
*/
var $gender = '';
/**
* Firstname
* @access public
* @var string
*/
var $first_name = '';
/**
* Lastname
* @access public
* @var string
*/
var $last_name = '';
/**
* Department
* @access public
* @var string
*/
var $department = '';
/**
* Office
* @access public
* @var string
*/
var $office = '';
/**
* Role (Group)
* @access public
* @var string
*/
var $role = '';
/**
* Last update
* @access public
* @var string
*/
var $last_update_time = '';
/**
* Description
* @access public
* @var string
*/
var $description = '';
/**
* Capabilities
* @access public
* @var string
*/
var $capabilities = '';
/**
* State
* @access public
* @var string
*/
var $state = '';

/**
* Internal use only
* @access private
* @var string
*/
var $_user_table = '';
/**
* Internal use only
* @access private
* @var string
*/
var $_role_table = '';

    function User() {
		global $USER_INFO_TABLE;
		$this->_role_table = 'role_info';
		$this->_user_table = $USER_INFO_TABLE;
	}
	
    function get_user_data($name) {
		$this->name = array('value'=>$name, 'label'=>'Login');
		$this->get_id();
		$this->get_password();
		$this->get_gender();
		$this->get_first_name();
		$this->get_last_name();
		$this->get_description();
		$this->get_department();
		$this->get_office();
		$this->get_capabilities();
		$this->get_state();
		$this->get_role();		
	}
	//-----------------------------------------------------------------------------------
    function get_id() {
		$query = "
			SELECT user_id as `Userid`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('id', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_password() {
		$query = "
			SELECT user_password as `Password`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('password', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_gender() {
		$query = "
			SELECT user_gender as `Gender`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('gender', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_first_name() {
		$query = "
			SELECT user_first_name as `Firstname`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('first_name', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_last_name() {
		$query = "
			SELECT user_last_name as `Lastname`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('last_name', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_description() {
		$query = "
			SELECT user_description as `Description`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('description', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_department() {
		$query = "
			SELECT user_department as `Department`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('department', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_office() {
		$query = "
			SELECT user_office as `Office`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('office', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_capabilities() {
		$query = "
			SELECT user_capabilities as `Capabilities`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('capabilities', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_state() {
		$query = "
			SELECT user_state as `State`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('state', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_role() {
		$query = "
			SELECT user_role as `Role`
			FROM $this->_user_table
			WHERE user_name = '".$this->name['value']."'
			LIMIT 1
		";
		$this->_set_var('role', $query);
	}
	//-----------------------------------------------------------------------------------
    function get_gender_list() {
		$ar_Return = array();
		$ar_Return[] = array("value"=>'f', "label"=>'female',);
		$ar_Return[] = array("value"=>'m', "label"=>'male',);
		return $ar_Return;
	}
	//-----------------------------------------------------------------------------------
    function get_role_list() {
		$ar_Return = array();
		$query = "
			SELECT role_id, role_name
			FROM $this->_role_table
		";
		$result = $this->_get_db($query);
		for ($i=0; $i<count($result); $i++) {
			$ar_Return[] = array("value"=>$result[$i][0]["value"], "label"=>$result[$i][1]["value"]);
		}
		return $ar_Return;
	}	

	//-----------------------------------------------------------------------------------
	function _get_db($query) {

	return openqrm_db_get_result($query);
	}
	
	
		//-----------------------------------------------------------------------------------
	function _set_var ($var, $query) {
		$this->$var = openqrm_db_get_result_single($query);
	}
	
	
	
	
}
?>