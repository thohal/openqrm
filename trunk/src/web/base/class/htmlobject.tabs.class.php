<?php
class htmlobject_tabmenu extends htmlobject
{
var $identifier = 'tab';
var $tabs = array();


	function htmlobject_tabmenu($arr) {
		$thisfile = basename($_SERVER['PHP_SELF']);
		$i = 0;
		foreach ($arr as $val) {
			$identifier = $this->identifier.$i;

			if(array_key_exists('value', $val) && $val['value'] != '') {
				$html = new htmlobject_div();
				$html->id = $identifier;
				$html->css = 'htmlobject_tab_box';
				$html->text = $val['value'];
				$value = $html->get_string();
				
			} else {
				$value = '';
			}

			if(array_key_exists('label', $val) && $val['label'] != '') {
				$label = $val['label'];
			} else {
				$label = '';
			}

			if(array_key_exists('target', $val) && $val['target'] != '') {
				$target = $val['target'];
			} else {
				$target = $thisfile;
			}

			if(array_key_exists('request', $val) && $val['request'] != '') {
				$request = $val['request'];
			} else {
				$request = array();
			}

			$this->tabs[] = array(
				'target' => $target,
				'value' => $value,
				'label' => $label,
				'id' => $identifier,
				'request' => $request,
				);
		$i++;
		}
	}



	function get_tabs($currenttab) {

		$thisfile = basename($_SERVER['PHP_SELF']);
		$_strReturn = "\n<div $this->_init_htmlobject>\n";
		$_strReturn .= "<ul>\n";	

		foreach($this->tabs as $content) {
			$css = '';
			if($content['id'] == $currenttab) { $css = ' class="current"'; }

			$target = $content['target'].'?currenttab='.$content['id'];
			foreach ($content['request'] as $key => $arg) {
				$target = $target.'&'.$key.'='.$arg;
			}
			
			$_strReturn .= '<li id="tab_'.$content['id'].'"'.$css.'>';
			$_strReturn .= "<span>";
			if($content['target'] == $thisfile) {
				$_strReturn .= '<a href="'.$target.'" onclick="ToggleTabs(\''.$content['id'].'\'); this.blur(); return false;">';
			} else {
				$_strReturn .= '<a href="'.$target.'"; onclick="this.blur();">';
			}
			$_strReturn .= $content['label'];
			$_strReturn .= "</a>";
			$_strReturn .= "</span>";
			$_strReturn .= "</li>\n";
		}
		
		$_strReturn .= "</ul>\n";
		$_strReturn .= "</div>\n";
		$_strReturn .= "<div style=\"line-height:0px;clear:both;\">&#160;</div>\n";

	return $_strReturn;
	}


	function get_js() {
	$_strReturn = '';
	$thisfile = basename($_SERVER['PHP_SELF']);

		$_strReturn .= "\n<script>\n";
		$_strReturn .= "function ToggleTabs(id) {\n";
		foreach($this->tabs  as $content) {
			if($content['target'] == $thisfile) {
				$_strReturn .= "document.getElementById('".$content['id']."').style.display = 'none';\n";
				$_strReturn .= "document.getElementById('tab_".$content['id']."').className = '';\n";
			}
		}
		$_strReturn .= "document.getElementById(id).style.display = 'block';\n";
		$_strReturn .= "document.getElementById('tab_'+id).className = 'current';\n";
		$_strReturn .= "}\n";	
		$_strReturn .= "</script>\n";
		
	return $_strReturn;
	}


	function get_css($currenttab) {
	$_strReturn = '';

		$_strReturn .= "\n<style>\n";
		foreach($this->tabs as $content) {
			if($content['id'] == $currenttab) { $_strReturn .= "#".$content['id']." { display: block; }\n"; }
			else { $_strReturn .= "#".$content['id']." { display: none; }\n"; }
		}
		$_strReturn .= "</style>\n";
		
	return $_strReturn;
	}


	function get_messagebox() {
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

	

	function get_string() {
	$_strReturn = '';

		if(count($this->tabs) > 0) {
	
			$this->init_htmlobject();	
	
			if(isset($_REQUEST['currenttab']) && $_REQUEST['currenttab'] != '') {
				$currenttab = $_REQUEST['currenttab'];
			} else {
				$currenttab = $this->tabs[0]['id'];
			}
	
			$_strReturn .= $this->get_js();
			$_strReturn .= $this->get_css($currenttab);
			$_strReturn .= $this->get_tabs($currenttab);
			$_strReturn .= $this->get_messagebox();
	
			foreach ($this->tabs as $tab) {
				if($tab['value'] != '') {
					$_strReturn .= $tab['value'];
				}
			}
	
		}	
	return $_strReturn;
	}
}
?>