<?php
class http
{

	//---------------------------------------------------------------
	/**
	* returns http request as cleaned string
	* string is empty when request not set
	* @access public
	* @param  $path string
	* @param  $filter bool
	* @return string [empty if request not set]
	*/
	//---------------------------------------------------------------
	function get_request($arg, $filter = true) 
	{
		if (isset($_REQUEST[$arg])) {
			if(is_array($_REQUEST[$arg])) {
				foreach($_REQUEST[$arg] as $key => $value) {
					$value = stripslashes($value);
					if($filter === true) {
						$value = str_replace('&quot;', '"', $value);
						$value = str_replace('&lt;', '<', $value);
						$value = str_replace('\r\n', '\n', $value);
					}
					$arr[$key] = $value;
				}
				return $arr;
			} else {
				$value = $_REQUEST[$arg];
				$value = stripslashes($value);
				if($filter === true) {
					$value = str_replace('&quot;', '"', $value);
					$value = str_replace('&lt;', '<', $value);
					$value = str_replace('\r\n', '\n', $value);
				}
				return $value;
			}
		} else {
			return '';
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
