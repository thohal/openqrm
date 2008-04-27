<?php
class htmlobject 
{
/**
* Attribute class
* @access private
* @var string
*/
var $css = '';
/**
* Attribute id
* @access private
* @var string
*/
var $id = '';
/**
* Attribute style
* @access private
* @var string
*/
var $style = '';
/**
* Attribute title
* @access private
* @var string
*/
var $title = '';

/**
* adds an eventhandler to
* @access private
* @var string
*/
var $handler = '';

/**
* internal use only
*/
var $_init_htmlobject;

	function init_htmlobject() {
		if ($this->css != '')  		{ $this->_init_htmlobject .= ' class="'.$this->css.'"'; }
		if ($this->id != '')  		{ $this->_init_htmlobject .= ' id="'.$this->id.'"'; }
		if ($this->style != '')		{ $this->_init_htmlobject .= ' style="'.$this->style.'"'; }
		if ($this->title != '')		{ $this->_init_htmlobject .= ' title="'.$this->title.'"'; }
		if ($this->handler != '')	{ $this->_init_htmlobject .= ' '.$this->handler; }
	}
	
}
//--------------------------------------------------------------------------------------

class htmlobject_select extends htmlobject
{
/**
* disable select 
* @access public
* @var bool
*/
var $disabled = false;
/**
* allow multiple selection
* @access public
* @var bool
*/
var $multiple = false;
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var string
*/
var $name = '';
/**
* number of lines to be shown
* @access public
* @var int
*/
var $size = '';
/**
* Attribute tabindex
* @access public
* @var int
*/
var $tabindex = '';
/**
* content of option element (text)
* @access public
* @var array
*/
var $text = array();
/**
* index of array text
* @access public
* @var array
*/
var $text_index = array(
"value" => 'id',
"text" => 'name'
);
/**
* content of option element (value)
* @access public
* @var array
*/
var $selected = array();
/**
* selected  by text or value
* true = selected by text
* false  = selected by values
* @access public
* @var bool
*/
var $selected_by_text = false;

/**
* internal use only
*/
var $_init_select;

	function init_select() {
		$this->_init_select = '';
		if ($this->disabled === true)	{ $this->_init_select .= ' disabled'; }
		if ($this->multiple === true)	{ $this->_init_select .= ' multiple'; }
		if ($this->name != '')  		{ $this->_init_select .= ' name="'.$this->name.'"'; }
		if ($this->size != '')			{ $this->_init_select .= ' size="'.$this->size.'"'; }
		if ($this->tabindex != '')  	{ $this->_init_select .= ' tabindex="'.$this->tabindex.'"'; }
	}

	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$this->init_select();
		$_strReturn = "\n<select$this->_init_htmlobject$this->_init_select>\n";
		$_strReturn .= $this->get_options();
		$_strReturn .= "</select>\n";
	return $_strReturn;
	}

	function get_options() {
	$_strReturn = '';
	    if(count($this->text) > 0){
	        for ($i=0; count($this->text)>$i; $i++) {
	        $sel =  "";
	            if($this->selected_by_text === true) {
	        		if(in_array($this->text[$i][$this->text_index['text']], $this->selected)) {
						$sel =  ' selected="selected"'; 
					}
	        	} 
				else {
	        		if(in_array($this->text[$i][$this->text_index['value']], $this->selected)) {
						$sel =  ' selected="selected"'; 
					}
	        	}
	        $_strReturn .= "<option value=\"".$this->text[$i][$this->text_index['value']]."\"$sel>".$this->text[$i][$this->text_index['text']]."</option>\n";        
	        }
	    } else {
	        $_strReturn .= "<option value=\"\" selected=\"selected\" >&#160;</option>\n";   
	    }
	return $_strReturn;
	}
}

//-----------------------------------------------------------------------

class htmlobject_input extends htmlobject
{
/**
* @access public * @var bool
*/
var $checked = false;
/**
* disable select 
* @access public * @var bool
*/
var $disabled = false;
/**
* maxlength
* @access public * @var int
*/
var $maxlength;
/**
* Attribute name
* @access public * @var string
*/
var $name = '';
/**
* number of lines to be shown
* @access public * @var int
*/
var $size = '';
/**
* Attribute tabindex
* @access public * @var int
*/
var $tabindex = '';
/**
* type of element
* @access public * @var string
* @values text | password | checkbox | radio | submit | reset | file | hidden | image | button
*/
var $type = '';
/**
* value of input
* @access public * @var string
*/
var $value = '';

/**
* internal use only
*/
var $_init_input;

	function init_input() {
	$this->_init_input = '';
		if ($this->checked != '')  		{ $this->_init_input .= ' checked'; }
		if ($this->disabled === true)	{ $this->_init_input .= ' disabled'; }
		if ($this->maxlength != '')		{ $this->_init_input .= ' maxlength="'.$this->maxlength.'"'; }
		if ($this->name != '')  		{ $this->_init_input .= ' name="'.$this->name.'"'; }
		if ($this->size != '')			{ $this->_init_input .= ' size="'.$this->size.'"'; }
		if ($this->tabindex != '')  	{ $this->_init_input .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->type != '')  		{ $this->_init_input .= ' type="'.$this->type.'"'; }
		if ($this->value != '')  		{ $this->_init_input .= ' value="'.$this->value.'"'; }
	}

	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$this->init_input();
		$_strReturn = "\n<input$this->_init_htmlobject$this->_init_input>";
	return $_strReturn;
	}
}

//------------------------------------------------------------------

class htmlobject_textarea extends htmlobject
{
/**
* Attribute cols
* @access public
* @var int
*/
var $cols = 50;
/**
* disable textarea 
* @access public
* @var bool
*/
var $disabled = false;
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var string
*/
var $name = '';
/**
* set textarea to readonly
* @access public
* @var bool
*/
var $readonly = false;
/**
* number of rows
* @access public
* @var int
*/
var $rows = 5;
/**
* Attribute tabindex
* @access public
* @var int
*/
var $tabindex = '';
/**
* wrap type (physical,virtual,none)
* @access public
* @var string
*/
var $wrap = '';

/**
* Content of textarea
* @access public
* @var string
*/
var $text = '';

/**
* internal use only
*/
var $_init_textarea;

	function init_textarea() {
	$this->_init_textarea = '';
		if ($this->cols != '')			{ $this->_init_textarea .= ' cols="'.$this->cols.'"'; }
		if ($this->disabled === true)	{ $this->_init_textarea .= ' disabled'; }
		if ($this->name != '')  		{ $this->_init_textarea .= ' name="'.$this->name.'"'; }
		if ($this->readonly === true)	{ $this->_init_textarea .= ' readonly'; }
		if ($this->rows != '')			{ $this->_init_textarea .= ' rows="'.$this->rows.'"'; }
		if ($this->tabindex != '')  	{ $this->_init_textarea .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->wrap != '')  		{ $this->_init_textarea .= ' wrap="'.$this->wrap.'"'; }
	}

	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$this->init_textarea();
		$_strReturn = "\n<textarea$this->_init_htmlobject$this->_init_textarea>";
		$_strReturn .= $this->text;
		$_strReturn .= "</textarea>\n";
	return $_strReturn;
	}
}

//------------------------------------------------------------------

class htmlobject_div extends htmlobject
{
/**
* text
* @access private
* @var string
*/
var $text = '';
	
	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$_strReturn = "\n<div$this->_init_htmlobject>$this->text</div>";
	return $_strReturn;
	}
}

//------------------------------------------------------------------

class htmlobject_td extends htmlobject
{
/**
* align
* @access public
* @var enum (left | center | right | justify | char)
*/
var $align = '';
/**
* backgroundcolor
* @access public
* @var HEX
*/
var $bgcolor = '';
/**
* colspan
* @access public
* @var int
*/
var $colspan = '';
/**
* td type
* @access public
* @var enum (td,th)
*/
var $type = 'td';
/**
* vertical align
* @access public
* @var enum (top | middle | bottom | baseline)
*/
var $valign = '';

/**
* Content of td
* @access public
* @var string
*/
var $text = '';

/**
* internal use only
*/
var $_init_td;

	function init_td() {
	$this->_init_td = '';
		if ($this->align != '') { $this->_init_td .= ' align="'.$this->align.'"'; }
		if ($this->bgcolor != '') { $this->_init_td .= ' bgcolor="'.$this->bgcolor.'"'; }
		if ($this->colspan != '') { $this->_init_td .= ' colspan="'.$this->colspan.'"'; }
		if ($this->valign != '') { $this->_init_td .= ' valign="'.$this->valign.'"'; }
	}

	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$this->init_td();
		$_strReturn = "\n<$this->type$this->_init_htmlobject$this->_init_td>";
		$_strReturn .= $this->text;
		$_strReturn .= "</$this->type>";
	return $_strReturn;
	}
}
//------------------------------------------------------------------

class htmlobject_tr extends htmlobject
{
/**
* align
* @access public
* @var enum (left | center | right | justify | char)
*/
var $align = '';
/**
* backgroundcolor
* @access public
* @var HEX
*/
var $bgcolor = '';
/**
* vertical align
* @access public
* @var enum (top | middle | bottom | baseline)
*/
var $valign = '';

/**
* Content of tr
* @access public
* @var string
*/
var $arr_tr = array();

/**
* internal use only
*/
var $_init_tr;

	function init_tr() {
	$this->_init_tr = '';
		if ($this->align != '') { $this->_init_tr .= ' align="'.$this->align.'"'; }
		if ($this->bgcolor != '') { $this->_init_tr .= ' bgcolor="'.$this->bgcolor.'"'; }
		if ($this->valign != '') { $this->_init_tr .= ' valign="'.$this->valign.'"'; }
	}

	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$this->init_tr();
		$_strReturn = "\n<tr$this->_init_htmlobject$this->_init_tr>";
		foreach($this->arr_tr as $td) {
			if(is_object($td) == true && get_class($td) == 'htmlobject_td') {
				$_strReturn .= $td->get_string();
			}
			elseif(is_string($td) == true) {
				$_strReturn .= $td;
			}
			else {
				$_strReturn .= 'td type not defined';
			}
		}
		$_strReturn .= "</tr>\n";
	return $_strReturn;
	}
	
	function add($td) {
		$this->arr_tr[] = $td;
	}	
	
}
//------------------------------------------------------------------

class htmlobject_table extends htmlobject
{
/**
* align
* @access public
* @var enum (left | center | right)
*/
var $align = '';
/**
* table border
* @access public
* @var int
*/
var $border = '';
/**
* table backgroundcolor
* @access public
* @var HEX
*/
var $bgcolor = '';
/**
* cellpadding
* @access public
* @var int
*/
var $cellpadding;
/**
* cellspacing
* @access public
* @var int
*/
var $cellspacing;
/**
* frame
* @access public
* @var enum (void | above | below | hsides | lhs | rhs | vsides | box | border)
*/
var $frame = '';
/**
* rules
* @access public
* @var enum (none | groups | rows | cols | all)
*/
var $rules = '';
/**
* summary
* @access public
* @var string
*/
var $summary = '';
/**
* width
* @access public
* @var int
*/
var $width = '';

/**
* Content of table
* @access public
* @var array
*/
var $arr_table = array();

/**
* internal use only
*/
var $_init_table;

	function init_table() {
	$this->_init_table = '';
		if ($this->align != '') { $this->_init_table .= ' align="'.$this->align.'"'; }
		if (isset($this->border) && $this->border !== '') { $this->_init_table .= ' border="'.$this->border.'"'; }
		if ($this->bgcolor != '') { $this->_init_table .= ' bgcolor="'.$this->bgcolor.'"'; }
		if (isset($this->cellpadding) && $this->cellpadding !== '') { $this->_init_table .= ' cellpadding="'.$this->cellpadding.'"'; }
		if (isset($this->cellspacing) && $this->cellspacing !== '') { $this->_init_table .= ' cellspacing="'.$this->cellspacing.'"'; }
		if ($this->frame != '') { $this->_init_table .= ' frame="'.$this->frame.'"'; }
		if ($this->rules != '') { $this->_init_table .= ' rules="'.$this->rules.'"'; }
		if ($this->summary != '') { $this->_init_table .= ' summary="'.$this->summary.'"'; }
		if ($this->width != '') { $this->_init_table .= ' width="'.$this->width.'"'; }
	}

	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$this->init_table();
		$_strReturn = "\n<table$this->_init_htmlobject$this->_init_table>";
		foreach($this->arr_table as $tr) {
			if(is_object($tr) == true && get_class($tr) == 'htmlobject_tr') {
				$_strReturn .= $tr->get_string();
			}
			elseif(is_string($tr) == true) {
				$_strReturn .= $tr;
			}			
			else {
				$_strReturn .= 'tr type not defined';
			}			
		}
		$_strReturn .= "</table>\n";
	return $_strReturn;
	}
	
	function add($tr) {
		$this->arr_table[] = $tr;
	}	
	
}
//------------------------------------------------------------------

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