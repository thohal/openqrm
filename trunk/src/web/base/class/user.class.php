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
		$this->password			= $result[0][11]['value'];
		
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
		$query = " 
			SELECT 
				user_id,
				user_gender,
				user_first_name,
				user_last_name,
				user_description,
				user_department,
				user_office,
				user_capabilities,
				user_state,
				user_role,
				user_last_update_time,
				user_password
			FROM $this->_user_table				
			WHERE user_name = '$this->name'
		";
		return $query;
	}
	//-----------------------------------------------------------------------------------
	function query_insert(){
		global $USER_INFO_TABLE;
		$this->id = openqrm_db_get_free_id('user_id', $this->_user_table);
		$query = "
			INSERT INTO 
				$USER_INFO_TABLE (
					user_id,
					user_name,
					user_password,
					user_gender,
					user_first_name,
					user_last_name,
					user_department,
					user_office,
					user_role,
					user_last_update_time,
					user_description,
					user_capabilities,
					user_state
				)
			VALUES (
					'$this->id',
					'$this->name',
					'$this->password',
					'$this->gender',
					'$this->first_name',
					'$this->last_name',
					'$this->department',
					'$this->office',
					'$this->role',
					'$this->last_update_time',
					'$this->description',
					'$this->capabilities',
					'$this->state.'
				)
		";
		
		$this->change_htpasswd('insert');
		return openqrm_db_get_result($query);
	}
	//-----------------------------------------------------------------------------------
	function query_update(){
		global $USER_INFO_TABLE;
		$user_fields = array();
		if($this->password != '') {
			$user_fields['user_password']=$this->password;
			$this->change_htpasswd('update');
		}
		$user_fields['user_gender']=$this->gender;
		$user_fields['user_first_name']=$this->first_name;
		$user_fields['user_last_name']=$this->last_name;
		$user_fields['user_department']=$this->department;
		$user_fields['user_office']=$this->office;
		$user_fields['user_role']=$this->role;
		$user_fields['user_last_update_time']=$this->last_update_time;
		$user_fields['user_description']=$this->description;
		$user_fields['user_capabilities']=$this->capabilities;
		$user_fields['user_state']=$this->state;
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($USER_INFO_TABLE, $user_fields, 'UPDATE', "user_name = '$this->name'");
		if (! $result) {
			$event->log("query_update", $_SERVER['REQUEST_TIME'], 2, "user.class.php", "Failed updating user", "", "", 0, 0, 0);
		}
	}
	//-----------------------------------------------------------------------------------
	function query_delete(){
		$query = "
			DELETE FROM $this->_user_table
			WHERE user_name = '".$this->name."'
		";
		$this->change_htpasswd('delete');
		return openqrm_db_get_result($query);
	}
	//-----------------------------------------------------------------------------------
    function check_user_exists() {
		$query = "
			SELECT user_name
			FROM $this->_user_table
			WHERE user_name = '".$this->name."'
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
    function check_string_name($name) {
		if (ereg("^[A-Za-z0-9]*$", $name) === false) {
			return '[A-Za-z0-9]';
		} else {
			return '';
		}
	}
	//-----------------------------------------------------------------------------------
    function check_string_password($pass) {
		if (ereg("^[A-Za-z0-9_-]*$", $pass) === false) {
			return '[A-Za-z0-9_-]';
		} else {
			return '';
		}
	}
	//-----------------------------------------------------------------------------------
	/**
	* Change htpassswd
	* @access private
	* @param $mode [update, delete, insert] 
	*/
    function change_htpasswd($mode = 'update') {
	global $RootDir;

		$ar_values = array();
		
		$handle = fopen ($RootDir.'/.htpasswd', "r");
		while (!feof($handle)) {
			$tmp = explode(':', fgets($handle, 4096));
			if($tmp[0] != '') {
				$ar_values[$tmp[0]] = $tmp[1];
			}
		}
		fclose ($handle);

		$handle = fopen ($RootDir.'/.htpasswd', "w+");
		
		if($mode == 'insert') {
			foreach($ar_values as $key => $value) {
				fputs($handle, "$key:$value");
			}
			fputs($handle, $this->name.':'.crypt($this->password)."\n");
		}
		if($mode == 'update') {
			foreach($ar_values as $key => $value) {
				if($key == $this->name) { 
					fputs($handle, $this->name.':'.crypt($this->password)."\n"); 
				} else {
					fputs($handle, "$key:$value");
				}
			}
		}
		if($mode == 'delete') {
			foreach($ar_values as $key => $value) {
				if($key != $this->name) { 
					fputs($handle, "$key:$value");
				}
			}
		}
		fclose ($handle);
	}	
	//-----------------------------------------------------------------------------------
    function get_users() {
		$query = '
			SELECT 
				user_name,
				user_id,
				user_first_name,
				user_last_name,
				role_name
			FROM '.$this->_user_table.', '.$this->_role_table.'
			WHERE user_role = role_id
			ORDER BY user_name
		';
		$ar_db = openqrm_db_get_result($query);
		$ar_headline = array();
		$ar_headline[] = array('Login', 'ID', 'First Name', 'Last Name', 'Role');		
		$result = array_merge($ar_headline, $ar_db);
		
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