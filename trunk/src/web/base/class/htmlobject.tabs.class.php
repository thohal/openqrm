<?php
class htmlobject_tabmenu extends htmlobject
{
/**
* internal use only
*/
var $_content = array();

function add($obj) {
	if(is_object($obj)) {
		$this->_content[] = $obj;
	} else {
		echo "add() only supports objects<br />";
	}
}

	function get_string() {
	$_strReturn = '';

	if(count($this->_content) > 0) {

		$this->init_htmlobject();	
		$thisfile = basename($_SERVER["PHP_SELF"]);
		if(isset($_REQUEST['currenttab']) && $_REQUEST['currenttab'] != '') {
			$currenttab = $_REQUEST['currenttab'];
		} else {
			reset($this->_content);
			$currenttab = current($this->_content);
			$currenttab = $currenttab->id;	
		}

		$_strReturn .= $this->get_js();
		$_strReturn .= $this->get_css($currenttab);
		
		$_strReturn .= "\n<div $this->_init_htmlobject>\n";
		$_strReturn .= "<ul>\n";	

		foreach($this->_content as $content) {
			$css = '';
			if($content->id == $currenttab) { $css = ' class="current"'; }
			
			$_strReturn .= "<li id=\"tab_$content->id\"$css>";
			$_strReturn .= "<span>";
			$_strReturn .= "<a href=\"$thisfile?currenttab=$content->id\" onclick=\"ToggleTabs('$content->id'); this.blur(); return false;\">";
			$_strReturn .= $content->title;
			$_strReturn .= "</a>";
			$_strReturn .= "</span>";
			$_strReturn .= "</li>\n";
		}
		
		$_strReturn .= "</ul>\n";
		$_strReturn .= "</div>\n";
		$_strReturn .= "<div style=\"line-height:0px;clear:both;\">&#160;</div>\n";

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
		
		foreach($this->_content as $content) {
			$content->title = '';
			$_strReturn .= $content->get_string();
		}
	}	
	return $_strReturn;
	}

	function get_js() {
	$_strReturn = '';

		$_strReturn .= "\n<script>\n";
		$_strReturn .= "function ToggleTabs(id) {\n";
		foreach($this->_content as $content) {
			$_strReturn .= "document.getElementById('$content->id').style.display = 'none';\n";
			$_strReturn .= "document.getElementById('tab_$content->id').className = '';\n";
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
		foreach($this->_content as $content) {
			if($content->id == $currenttab) { $_strReturn .= "#$content->id { display: block; }\n"; }
			else { $_strReturn .= "#$content->id { display: none; }\n"; }
		}
		$_strReturn .= "</style>\n";
		
	return $_strReturn;
	}
}
?>