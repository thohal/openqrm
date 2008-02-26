<?php
class htmlobject_input
{
/**
* Attribute class
* @access public * @var string
*/
var $css = '';
/**
* Attribute id
* @access public * @var string
*/
var $id = '';
/**
* Attribute style
* @access public * @var string
*/
var $style = '';
/**
* Attribute title
* @access public * @var string
*/
var $title = '';
/**
* adds an eventhandler to
* @access public * @var string
*/
var $handler = '';


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
var $type = array();
/**
* value of input
* @access public * @var string
*/
var $value = '';

/**
* internal use only
*/
var $_init_input;
var $_init_htmlobject;

function init_htmlobject() {
	$this->_init_htmlobject = '';
	if ($this->css != '')  		{ $this->_init_htmlobject .= ' class="'.$this->css.'"'; }
	if ($this->id != '')  		{ $this->_init_htmlobject .= ' id="'.$this->id.'"'; }
	if ($this->style != '')		{ $this->_init_htmlobject .= ' style="'.$this->style.'"'; }
	if ($this->title != '')		{ $this->_init_htmlobject .= ' title="'.$this->title.'"'; }
	if ($this->handler != '')	{ $this->_init_htmlobject .= ' '.$this->handler; }
}
function init_input() {
	$this->_init_input = '';
	if ($this->disabled === true)	{ $this->_init_input .= ' disabled'; }
	if ($this->maxlength != '')		{ $this->_init_input .= ' maxlength="'.$this->maxlength.'"'; }
	if ($this->name != '')  		{ $this->_init_input .= ' name="'.$this->name.'"'; }
	if ($this->size != '')			{ $this->_init_input .= ' size="'.$this->size.'"'; }
	if ($this->tabindex != '')  	{ $this->_init_input .= ' tabindex="'.$this->tabindex.'"'; }
	if ($this->checked != '')  		{ $this->_init_input .= ' checked'; }
	if ($this->type != '')  		{ $this->_init_input .= ' type="'.$this->type.'"'; }
	$this->_init_input .= ' value="'.$this->value.'"';
}

function get_string() {
$_strReturn = '';
	$this->init_htmlobject();
	$this->init_input();
	$_strReturn = "\n<input$this->_init_htmlobject$this->_init_input>";
return $_strReturn;
}
}
?>