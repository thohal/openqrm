<?php
/**
 * @package Htmlobjects
 */


/**
 * @package Htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @version 1.0
 */
class htmlobject_tabmenu extends htmlobject
{
/**
* field to add value to checkbox
* @access public
* @var string
*/
var $serialize;
/**
* css class to highlight active tab
* @access public
* @var string
*/
var $tabcss = 'current';
/**
* tab values
* @access private
* @var array
*/
var $_tabs = array();
/**
* tab values
* @access private
* @var array
*/
var $_thisfile = '';


	function htmlobject_tabmenu($arr, $serialize = 'currenttab') {
		$this->_thisfile = basename($_SERVER['PHP_SELF']);
		$this->serialize = $serialize;
		$this->_set($arr);	
	}

	//----------------------------------------------------------------------------------------
	/**
	* set tabs data
	* @access private
	* @param array $arr
	*/
	//----------------------------------------------------------------------------------------	
	function _set($arr) {

		$i = 0;
		foreach ($arr as $val) {
			
			$identifier = $this->serialize.''.$i;

			if(array_key_exists('value', $val)) {
				$html = new htmlobject_div();
				$html->id = $identifier;
				$html->css = 'htmlobject_tab_box';
				$html->text = $val['value'].'<div style="clear:both;line-height:0;">&#160;</div>';
				$value = $html->get_string();
			} else { $value = ''; }

			array_key_exists('label', $val) ? $label = $val['label'] : $label = '';
			array_key_exists('target', $val) ? 	$target = $val['target'] : $target = $this->_thisfile;
			array_key_exists('request', $val) ? $request = $val['request'] : $request = array();

			$this->_tabs[] = array(
				'target' => $target,
				'value' => $value,
				'label' => $label,
				'id' => $identifier,
				'request' => $request,
				);
		$i++;
		}

	}

	//----------------------------------------------------------------------------------------
	/**
	* create tabs
	* @access private
	* @param string $currenttab
	* @return string
	*/
	//----------------------------------------------------------------------------------------	
	function _get_tabs($currenttab) {
		
		$_strReturn = "\n<div $this->_init_htmlobject>\n";
		$_strReturn .= "<ul>\n";	

		$i = 0;
		foreach($this->_tabs as $tab) {
			$css = '';
			if($tab['id'] == $this->serialize.$currenttab) { $css = ' class="'.$this->tabcss.'"'; }

			$target = $tab['target'];
			$request = '?'.$this->serialize.'=tab'.$i;
			foreach ($tab['request'] as $key => $arg) {
				$request = $request.'&'.$key.'='.$arg;
			}
			
			$_strReturn .= '<li id="tab_'.$tab['id'].'"'.$css.'>';
			$_strReturn .= "<span>";

			if($tab['target'] == $this->_thisfile && $tab['id'] != $this->serialize.$currenttab) {
				$_strReturn .= '<a href="'.$target.$request.'" onclick="ToggleTabs(\''.$tab['id'].'\'); this.blur(); return false;">';
			} else {
				$_strReturn .= '<a href="'.$target.$request.'" onclick="this.blur();">';
			}
			$_strReturn .= $tab['label'];
			$_strReturn .= "</a>";
			$_strReturn .= "</span>";
			$_strReturn .= "</li>\n";

		$i++;
		}
		
		$_strReturn .= "</ul>\n";
		$_strReturn .= "</div>\n";
		$_strReturn .= "<div style=\"line-height:0px;clear:both;\">&#160;</div>\n";

	return $_strReturn;
	}

	//----------------------------------------------------------------------------------------
	/**
	* create JS toggle function
	* @access private
	* @return string
	*/
	//----------------------------------------------------------------------------------------	
	function _get_js() {
	$_strReturn = '';
  
		$_strReturn .= "\n<script>\n";
		$_strReturn .= "function ToggleTabs(id) {\n";
		foreach($this->_tabs  as $tab) {
			if(basename($tab['target']) == $this->_thisfile) {
				$_strReturn .= "document.getElementById('".$tab['id']."').style.display = 'none';\n";
				$_strReturn .= "document.getElementById('tab_".$tab['id']."').className = '';\n";
			}
		}
		$_strReturn .= "document.getElementById(id).style.display = 'block';\n";
		$_strReturn .= "document.getElementById('tab_'+id).className = '".$this->tabcss."';\n";
		$_strReturn .= "}\n";	
		$_strReturn .= "</script>\n";
		
	return $_strReturn;
	}

	//----------------------------------------------------------------------------------------
	/**
	* create css
	* @access private
	* @param string $currenttab
	* @return string
	*/
	//----------------------------------------------------------------------------------------	
	function _get_css($currenttab) {
	$_strReturn = '';

		$_strReturn .= "\n<style>\n";
		foreach($this->_tabs as $tab) {
			if($tab['id'] == $this->serialize.$currenttab) { $_strReturn .= "#".$tab['id']." { display: block; }\n"; }
			else { $_strReturn .= "#".$tab['id']." { display: none; }\n"; }
		}
		$_strReturn .= "</style>\n";
		
	return $_strReturn;
	}

	//----------------------------------------------------------------------------------------
	/**
	* create messagebox
	* @access private
	* @return string
	*/
	//----------------------------------------------------------------------------------------	
	function _get_messagebox() {
	$_strReturn = '';
	    if(isset($_REQUEST['strMsg']) && $_REQUEST['strMsg'] != "") {
	    $_strReturn .= '
	    <div class="msgBox" id="msgBox">'.$_REQUEST['strMsg'].'</div>
	    <script>
	    var aktiv = window.setInterval("msgBox()", 5000);

	    function msgBox() {
	        document.getElementById(\'msgBox\').style.display = \'none\';
	        window.clearInterval(aktiv);
	    }
	    </script>';
	    }
	return $_strReturn;
	}

	//----------------------------------------------------------------------------------------
	/**
	* build tabs output
	* @access public
	* @param array $arr
	* @return string
	*/
	//----------------------------------------------------------------------------------------	
	function get_string() {
	$_strReturn = '';

		if(count($this->_tabs) > 0) {
	
			$this->init_htmlobject();	
	
			if(isset($_REQUEST[$this->serialize]) && $_REQUEST[$this->serialize] != '') {
				$currenttab = str_replace('tab', '', $_REQUEST[$this->serialize]);
			} else {
				$currenttab = '0';
			}
	
			$_strReturn .= $this->_get_js();
			$_strReturn .= $this->_get_css($currenttab);
			$_strReturn .= $this->_get_tabs($currenttab);
			$_strReturn .= $this->_get_messagebox();
	
			foreach ($this->_tabs as $tab) {
				if($tab['value'] != '') {
					$_strReturn .= $tab['value'];
				}
			}
	
		}	
	return $_strReturn;
	}
}
?>
