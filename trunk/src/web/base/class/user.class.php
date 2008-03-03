<?php
		
class user
{
/**
* Id
* @access public
* @var int
*/
var $id;
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

	//-----------------------------------------------------------------------------------
    function user($name) {
		global $USER_INFO_TABLE;
		$this->name = $name;
		$this->_role_table = 'role_info';
		$this->_user_table = $USER_INFO_TABLE;
	}
	//-----------------------------------------------------------------------------------	
    function set_user_form() {

		$query = $this->query_select(); 
		$result = openqrm_db_get_result($query);

		$this->name 			= array('value'=>$this->name, 'label'=>'Login');
		$this->id 				= $result[0][0];
		$this->gender 			= $result[0][1];
		$this->first_name 		= $result[0][2];
		$this->last_name 		= $result[0][3];
		$this->description 		= $result[0][4];
		$this->department 		= $result[0][5];
		$this->office 			= $result[0][6];
		$this->capabilities		= $result[0][7];
		$this->state 			= $result[0][8];
		$this->role 			= $result[0][9];
		$this->last_update_time	= $result[0][10];
		$this->password	= array('value'=>'', 'label'=>$result[0][11]['label']);
	}
	//-----------------------------------------------------------------------------------	
	function set_user() {
		
		$query = $this->query_select(); 
		$result = openqrm_db_get_result($query);

		$this->id 				= $result[0][0]['value'];
		$this->gender 			= $result[0][1]['value'];
		$this->first_name 		= $result[0][2]['value'];
		$this->last_name 		= $result[0][3]['value'];
		$this->description 		= $result[0][4]['value'];
		$this->department 		= $result[0][5]['value'];
		$this->office 			= $result[0][6]['value'];
		$this->capabilities		= $result[0][7]['value'];
		$this->state 			= $result[0][8]['value'];
		$this->role 			= $result[0][9]['value'];
		$this->last_update_time	= $result[0][10]['value'];
		
		$this->get_role_name();
		$this->role = $this->role['label'];
	}
	//-----------------------------------------------------------------------------------
	function set_user_from_request() {
	
		$this->id	 			= $this->http_request('id');
		$this->password 		= $this->http_request('password');
		$this->gender 			= $this->http_request('gender');
		$this->first_name 		= $this->http_request('first_name');
		$this->last_name 		= $this->http_request('last_name');
		$this->department 		= $this->http_request('department');
		$this->office 			= $this->http_request('office');
		$this->role 			= $this->http_request('role');
		$this->last_update_time = $this->http_request('last_update_time');
		$this->description 		= $this->http_request('description');
		$this->capabilities 	= $this->http_request('capabilities');
		$this->state 			= $this->http_request('state');
		
	}
	//-----------------------------------------------------------------------------------
	function query_select(){
		$query = '
			SELECT 
				user_id as `Userid`,
				user_gender as `Gender`,
				user_first_name as `Firstname`,
				user_last_name as `Lastname`,
				user_description as `Description`,
				user_department as `Department`,
				user_office as `Office`,
				user_capabilities as `Capabilities`,
				user_state as `State`,
				user_role as `Role`,
				user_last_update_time as `Update`,
				user_password as `Password`
			FROM '.$this->_user_table.'				
			WHERE user_name = "'.$this->name.'"
			LIMIT 1
		';
		return $query;
	}
	//-----------------------------------------------------------------------------------
	function query_insert(){
		$this->id = openqrm_db_get_free_id('user_id', $this->_user_table);
		$query = '
			INSERT INTO 
				`user_info` (
					`user_id` ,
					`user_name` ,
					`user_password` ,
					`user_gender` ,
					`user_first_name` ,
					`user_last_name` ,
					`user_department` ,
					`user_office` ,
					`user_role` ,
					`user_last_update_time` ,
					`user_description` ,
					`user_capabilities` ,
					`user_state`
				)
			VALUES (
					"'.$this->id.'",
					"'.$this->name.'",
					"'.$this->password.'",
					"'.$this->gender.'",
					"'.$this->first_name.'",
					"'.$this->last_name.'",
					"'.$this->department.'",
					"'.$this->office.'",
					'.$this->role.',
					"'.$this->last_update_time.'",
					"'.$this->description.'",
					"'.$this->capabilities.'",
					"'.$this->state.'"
				)
		';
		return openqrm_db_get_result($query);
	}
	//-----------------------------------------------------------------------------------
	function query_update(){
	
		$strSet = '';
		if($this->password != '') {
			$strSet .= '`user_password` = "'.$this->password.'",';
		}
			$strSet .= '`user_gender` = "'.$this->gender.'",';
			$strSet .= '`user_first_name` = "'.$this->first_name.'",';
			$strSet .= '`user_last_name` = 	"'.$this->last_name.'",';
			$strSet .= '`user_department` = "'.$this->department.'",';
			$strSet .= '`user_office` = "'.$this->office.'",';
			$strSet .= '`user_role` = '.$this->role.',';
			$strSet .= '`user_last_update_time` = "'.$this->last_update_time.'",';
			$strSet .= '`user_description` = "'.$this->description.'",';
			$strSet .= '`user_capabilities` = "'.$this->capabilities.'",';
			$strSet .= '`user_state` = "'.$this->state.'"';
	
		$query = '
			UPDATE	`user_info`
			SET		'. $strSet .'				
			WHERE user_name = "'.$this->name.'"
				AND user_id = "'.$this->id.'"
			LIMIT 1
		';
		return openqrm_db_get_result($query);
	}
	//-----------------------------------------------------------------------------------
    function check_user_exists() {
		$query = "
			SELECT user_name as `Name`
			FROM $this->_user_table
			WHERE user_name = '".$this->name."'
			LIMIT 1
		";
		$result = openqrm_db_get_result_single($query);
		
		if($result['value'] != '') { return true; }
		else { return false; }
		
	}
	//-----------------------------------------------------------------------------------
    function get_gender_list() {
		$ar_Return = array();
		$ar_Return[] = array("value"=>'', "label"=>'',);
		$ar_Return[] = array("value"=>'f', "label"=>'female',);
		$ar_Return[] = array("value"=>'m', "label"=>'male',);
		return $ar_Return;
	}
	//-----------------------------------------------------------------------------------
    function get_role_name() {
		$query = "
			SELECT user_role, role_name
			FROM $this->_user_table, $this->_role_table
			WHERE user_name = '".$this->name."' 
				AND user_role = role_id
			LIMIT 1
		";
		$result = openqrm_db_get_result_double($query);
		$this->role = $result[0];
	}
	//-----------------------------------------------------------------------------------
    function get_role_list() {
		$query = "
			SELECT role_id, role_name
			FROM $this->_role_table
		";
		$result = openqrm_db_get_result_double($query);
		return $result;
	}
	
	//-----------------------------------------------------------------------------------
    function get_users() {
		$query = '
			SELECT 
				user_name,
				user_id,
				user_first_name,
				user_last_name,
				user_role
			FROM '.$this->_user_table.'
			ORDER BY user_name
		';
		$result = openqrm_db_get_result($query);
		return $result;
	}	
	//-----------------------------------------------------------------------------------	
	function http_request($arg) 
	{
		global $_REQUEST;
		if (isset($_REQUEST[$arg])) 
			return $_REQUEST[$arg];	
		else
			return '';
	}
	
}
?>