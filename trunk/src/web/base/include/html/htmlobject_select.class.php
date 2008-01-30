<?php
class htmlobject_select extends htmlobject
{
/**
* disable select 
* @access private
* @var bool
*/
var $disabled = false;
/**
* allow multiple selection
* @access private
* @var bool
*/
var $multiple = false;
/**
* Attribute name (should the the same as Attribute id)
* @access private
* @var string
*/
var $name = '';
/**
* number of lines to be shown
* @access private
* @var int
*/
var $size = '';
/**
* Attribute tabindex
* @access private
* @var int
*/
var $tabindex = '';
/**
* content of option element (text)
* @access private
* @var array
*/
var $text = array();
/**
* content of option element (value)
* @access private
* @var array
*/
var $values = array();
/**
* selected option of option element
* @access private
* @var array
*/
var $selected = array();
/**
* selected  by text or value
* true = selected by text
* false  = selected by values
* @access private
* @var bool
*/
var $selected_by_text = true;

/**
* internal use only
*/
var $_disabled;
var $_multiple;
var $_name;
var $_size;
var $_tabindex;

function init_htmlobject_select() {
	if ($this->disabled === true)	{ $this->_disabled = ' disabled'; }
	if ($this->multiple === true)	{ $this->_multiple = ' multiple'; }
	if ($this->name != '')  		{ $this->_name = ' name="'.$this->name.'"'; }
	if ($this->size != '')			{ $this->_size = ' size="'.$this->size.'"'; }
	if ($this->tabindex != '')  	{ $this->_tabindex = ' tabindex="'.$this->tabindex.'"'; }
	if (count($this->values) == 0) 	{ $this->values = $this->text; 	}
}

function get_string() {
$this->init_htmlobject();
$this->init_htmlobject_select();
$_strReturn = '
<select'.$this->_id.
			$this->_name.
			$this->_css.
			$this->_tabindex.
			$this->_title.
			$this->_style.
			$this->_size.
			$this->_multiple.
			$this->_disabled.'>
';
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
        		if(in_array($this->text[$i], $this->selected)) { $sel =  ' selected="selected"'; }
        	} 
			else {
        		if(in_array($this->values[$i], $this->selected)) { $sel =  ' selected="selected"'; }
        	}
        $_strReturn .= "<option value=\"".$this->values[$i]."\"$sel>".$this->text[$i]."</option>\n";        
        }
    } else {
        $_strReturn .= "<option value=\"\" selected=\"selected\" >&#160;</option>\n";   
    }
return $_strReturn;
}


}
?>