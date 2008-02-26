<?php
class htmlobject_select
{
/**
* Attribute class
* @access public
* @var string
*/
var $css = '';
/**
* Attribute id
* @access public
* @var string
*/
var $id = '';
/**
* Attribute style
* @access public
* @var string
*/
var $style = '';
/**
* Attribute title
* @access public
* @var string
*/
var $title = '';

/**
* adds an eventhandler to
* @access public
* @var string
*/
var $handler = '';
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
var $_init_htmlobject;

function init_htmlobject() {
	$this->_init_htmlobject = '';
	if ($this->css != '')  		{ $this->_init_htmlobject .= ' class="'.$this->css.'"'; }
	if ($this->id != '')  		{ $this->_init_htmlobject .= ' id="'.$this->id.'"'; }
	if ($this->style != '')		{ $this->_init_htmlobject .= ' style="'.$this->style.'"'; }
	if ($this->title != '')		{ $this->_init_htmlobject .= ' title="'.$this->title.'"'; }
	if ($this->handler != '')	{ $this->_init_htmlobject .= ' '.$this->handler; }
}
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
?>