<?php
class htmlobject_box
{
/**
* Attribute class
* @access public
* @var string
*/
var $css = 'box';
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
* Attribute name (should the the same as Attribute id)
* @access public
* @var string
*/
var $label = '';
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var object
*/
var $content = '';
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var string
*/
var $css_left = 'left';
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var string
*/
var $css_right = 'right';

/**
* internal use only
*/
var $_init_htmlobject;

function init_htmlobject() {
	$this->_init_htmlobject = '';
	if ($this->css != '')  		{ $this->_init_htmlobject .= ' class="'.$this->css.'"'; }
	if ($this->id != '')  		{ $this->_init_htmlobject .= ' id="'.$this->id.'"'; }
	if ($this->style != '')		{ $this->_init_htmlobject .= ' style="'.$this->style.'"'; }
	if ($this->title != '')		{ $this->_init_htmlobject .= ' title="'.$this->title.'"'; }
	if ($this->handler != '')	{ $this->_init_htmlobject .= ' '.$this->handler; }
}

function init_htmlobject_box() {
	if ($this->label == '')		{ $this->label = '&#160;'; }
	if ($this->content == '')	{ $this->content = '&#160;'; }
	if ($this->css_left != '') 	{ $this->css_left = ' class="'.$this->css_left.'"'; }
	if ($this->css_right != '') { $this->css_right = ' class="'.$this->css_right.'"'; }
}

function get_string() {

$this->init_htmlobject();
$this->init_htmlobject_box();

$_strReturn = '
<div'.$this->_init_htmlobject.'>
<div'.$this->css_left.'><label for="'.$this->content->name.'">'.$this->label.'</label></div>
<div'.$this->css_right.'>'.$this->content->get_string().'</div>
<div style="line-height:0px;height:0px;clear:both;" class="floatbreaker">&#160;</div>
</div>
';

return $_strReturn;
}

}
?>