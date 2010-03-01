<?php
/**
 * @package htmlobjects
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

class htmlobject_http
{
/**
* regex pattern for httprequest (crosssitescripting)
* @access public
* @var array
*/
var $request_filter;


	//---------------------------------------------------------------
	/**
	* returns http request as cleaned string
	* string is empty when request not set
	* @access public
	* @param  $arg string
	* @return string | array [empty if request not set]
	*/
	//---------------------------------------------------------------
	function get_request($arg) 
	{
		$Req = '$_REQUEST'.$this->string_to_index($arg);

		if(eval("return isset($Req);") && eval("return $Req;") != '') {
			if(is_array(eval("return $Req;"))) {
				$return = $this->get_request_array(eval("return $Req;"));
			} else {
				$return = $this->filter_request(eval("return $Req;"));
			}
		} else {
			$return = '';
		}

		return $return;
	}
	//-------------------------------------------------
	/**
	 * get values from http request as array
	 * @access public
	 * @return array 
	 */
	//-------------------------------------------------
	function get_request_array($arg) {

		$arReturn = array();

		if(is_array($arg)) {
			foreach($arg as $key => $value) {
				if(is_array($value)) {
					$arReturn[$key] = $this->get_request_array($value);
				}
				if(is_string($value)) {
					$arReturn[$key] = $this->filter_request($value);
				}
			}
		}
		if(is_string($arg)) {
			$arReturn[$key] = $this->filter_request($value);
		}
		return $arReturn;
	}
	//---------------------------------------------------------------
	/**
	* performes preg_replace
	* @access protected
	* @param string $arg
	* @return string
	*/
	//---------------------------------------------------------------
	function filter_request($arg) {
		if(is_string($arg)) {
			$str = '';
			$arg = stripslashes($arg);
			if(is_array($this->request_filter)) {
				foreach ($this->request_filter as $reg) {
					$str = preg_replace($reg['pattern'], $reg['replace'], $arg);
				}
			} else {
				$str = $arg;		
			}

#preg_match_all('~\r\n~', $str, $matches);
#echo $arg;
#print_r($matches);

			return $str;
		} else {
			#debug::add($arg.' is not type string', 'ERROR');
		}
	}

	//---------------------------------------------------------------
	/**
	* set filter for request handling (XSS)
	*
	* <code>
	* $http = new http();
	* $http->set_request_filter(array(
	*    array ( 'pattern' => '~\r\n~', 'replace' => '\n'),
	*  );
	* </code>
	*
	* @access public
	* @param  array $arg
	* @return string
	*/
	//---------------------------------------------------------------
	function set_request_filter($arg = array()) {
		if(isset($arg) && count($arg) > 0) {
			$this->request_filter = array();
			foreach($arg as $key => $value) {
				if(isset($value['pattern'])) {
					if(!isset($value['replace'])) {
						#debug::add('could not find array key ["replace"]', 'NOTICE');
						$value['replace'] = '';
					}
					$this->request_filter[] = array('pattern' => $value['pattern'], 'replace' => $value['replace']);
				} else {
					#debug::add('could not find array key ["pattern"]', 'ERROR');
				}
			}
		} else {
			#debug::add('nothing to do', 'NOTICE');
		}
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
	function get_request_string( $firstchar = '?', $excludes = array(), $includes = array() ) {
		$type = array('$_POST','$_GET');
		$_str = '';
		foreach($type as $request) {	
			foreach(eval("return $request;") as $name => $foo) {
				if(isset($excludes) && is_array($excludes) && in_array($name, $excludes) === false) {
					$value = http_request($name);
					if(is_array($value)) {
						foreach($value as $key => $val) {
							$_str .= '&'.$name.'['.$key.']='.$val;
						}
					} else {
						$_str .= '&'.$name.'='.$value;
					}
				}
			}
		}
		if($_str != '') $_str = preg_replace('/^&/', $firstchar, $_str);
		return $_str;
	}

	function get_params_string($firstchar = '?', $params = array(), $encode = false) {
		$str = '';		
		if(is_array($params)) {
			foreach($params as $key => $val) {
				$str .= '&'.$key.'='.$val;
			}
		} else {
			$str .= '&'.$name.'='.$value;
		}
		if($str != '') $str = preg_replace('/^&/', $firstchar, $str);
		if($encode === true) {
 			$str = str_replace('&', '&amp;', $str);
		}
		return $str;
	}

	//-------------------------------------------------
	/**
	 * transform string to array index string
	 * @access public
	 * @param array $name
	 * @return string
	 */
	//-------------------------------------------------	
	function string_to_index($arg) {

		$strReturn = '';

		// replace unindexed array
		$arg = $this->unindex_array($arg);
		$regex = '~(\[.*\])~';

		preg_match($regex, $arg, $matches);
		if($matches) {
			$str = '['.preg_replace('~\[.*\]~', '', $arg).']'.$matches[0];
		}
		else  {
			$str = '['.$arg.']';
		}
		// add quots to make it array
		$str = str_replace('[', '["', $str);
		$str = str_replace(']', '"]', $str);
		
		return $str;

	}
	//-------------------------------------------------
	/**
	 * remove unindexed array
	 * @access public
	 * @param array $name
	 * @return string
	 */
	//-------------------------------------------------	
	function unindex_array($name) {
		return preg_replace('~\[]$~', '', $name);
	}
	//---------------------------------------------------------------
	/**
	* header redirect
	* tries php header redirect,
	* on fail js redirect,
	* on fail meta redirect
	*
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
