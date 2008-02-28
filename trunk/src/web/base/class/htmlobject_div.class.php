<?php
class htmlobject_div 
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
* text
* @access private
* @var string
*/
var $text = '';

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
	
	function get_string() {
	$_strReturn = '';
		$this->init_htmlobject();
		$_strReturn = "\n<div$this->_init_htmlobject>$this->text</div>";
	return $_strReturn;
	}
}
?>