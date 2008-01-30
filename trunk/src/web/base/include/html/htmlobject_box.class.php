<?php
class htmlobject_box extends htmlobject
{
/**
* Attribute name (should the the same as Attribute id)
* @access private
* @var string
*/
var $label = '';
/**
* Attribute name (should the the same as Attribute id)
* @access private
* @var string
*/
var $content = '';
/**
* Attribute name (should the the same as Attribute id)
* @access private
* @var string
*/
var $css_left = '';
/**
* Attribute name (should the the same as Attribute id)
* @access private
* @var string
*/
var $css_right = '';

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
<table'.$this->id.
		$this->name.
		$this->css.
		$this->style.
		$this->title.'>
<tr>
<td'.$this->css_left.'>'.$this->label.'</td>
<td'.$this->css_right.'>'.$this->content.'</td>
</tr>
</table>
';

return $_strReturn;
}


}
?>