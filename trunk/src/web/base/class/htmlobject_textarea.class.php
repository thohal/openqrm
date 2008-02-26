<?php
class htmlobject_textarea
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

function init_htmlobject() {
	$this->_init_htmlobject = '';
	if ($this->css != '')  		{ $this->_init_htmlobject .= ' class="'.$this->css.'"'; }
	if ($this->id != '')  		{ $this->_init_htmlobject .= ' id="'.$this->id.'"'; }
	if ($this->style != '')		{ $this->_init_htmlobject .= ' style="'.$this->style.'"'; }
	if ($this->title != '')		{ $this->_init_htmlobject .= ' title="'.$this->title.'"'; }
	if ($this->handler != '')	{ $this->_init_htmlobject .= ' '.$this->handler; }
}
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
	$_strReturn = "\n<textarea $this->_init_htmlobject.$this->_init_textarea>";
	$_strReturn .= $this->text;
	$_strReturn .= "</textarea>\n";
return $_strReturn;
}
}
?>