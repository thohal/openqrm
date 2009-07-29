<?php
/**
 * @package htmlobjects
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

//----------------------------------------------------------------------------------------
/**
 * Http
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
*/
//----------------------------------------------------------------------------------------

class http
{
/**
* regex pattern for httprequest (crosssitescripting)
* <code>
* $http = new http();
* $http->http_request_replace = array(
*    array ( 'pattern' => '~\r\n~', 'replace' => '\n'),
*  );
* </code>
* @access public
* @var array
*/
var $http_request_replace = array(
		array ( 'pattern' => '~\r\n~', 'replace' => '\n'),
	);


	//---------------------------------------------------------------
	/**
	* returns http request as cleaned string
	* string is empty when request not set
	* @access public
	* @param  $path string
	* @return string [empty if request not set]
	*/
	//---------------------------------------------------------------
	function get_request($arg) 
	{
		if (isset($_REQUEST[$arg])) {
			if(is_array($_REQUEST[$arg])) {
				foreach($_REQUEST[$arg] as $key => $value) {
					$arr[$key] =  $this->filter_request($value);
				}
				return $arr;
			} else {
				return $this->filter_request($_REQUEST[$arg]);
			}
		} else {
			return '';
		}
	}

	//---------------------------------------------------------------
	/**
	* performes preg_replace
	* @access public
	* @param  $value string
	* @return string
	*/
	//---------------------------------------------------------------
	function filter_request($value) {
		$value = stripslashes($value);
		if(is_array($this->http_request_replace)) {
			foreach ($this->http_request_replace as $reg) {
				$value = preg_replace($reg['pattern'], $reg['replace'], $value);
			}
		}
		return $value;
	}

	//---------------------------------------------------------------
	/**
	* returns http request [POST/GET] as string
	* @access public
	* @param $firstchar string
	* @param $excludes array
	* @return string [empty if request empty]
	*/
	//---------------------------------------------------------------
	function get_request_as_string($firstchar = '?', $excludes = array()) {
		$type = array('_POST','_GET');
		$_strReturn = '';
		foreach($type as $request) {	
			foreach(eval("return \$$request;") as $name => $foo) {
				if(in_array($name, $excludes) == false) {
					$value = http_request($name);
					if(is_array($value)) {
						foreach($value as $key => $val) {
							$_strReturn .= '&'.$name.'['.$key.']='.$val;
						}
					} else {
						$_strReturn .= '&'.$name.'='.$value;
					}
				}
			}
		}
		if($_strReturn != '') $_strReturn = preg_replace('/^&/', $firstchar, $_strReturn);
		return $_strReturn;
	}

	//---------------------------------------------------------------
	/**
	* header redirect
	* tries php header redirect, on fail js redirect, on fail meta redirect
	* @access public
	* @param  $url string
	*/
	//---------------------------------------------------------------
	function redirect($url){
		if (!headers_sent()){
		    header('Location: '.$url); exit;
		}else{
		    echo '<script type="text/javascript">';
		    echo 'window.location.href="'.$url.'";';
		    echo '</script>';
		    echo '<noscript>';
		    echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
		    echo '</noscript>'; exit;
		}
	}

}
?>
