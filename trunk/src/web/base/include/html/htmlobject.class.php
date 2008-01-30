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
* internal use only
*/
var $_css;
var $_id;
var $_style;
var $_title;


	function init_htmlobject() {
		if ($this->css != '')  	{ $this->_css = ' class="'.$this->css.'"'; }
		if ($this->id != '')  	{ $this->_id = ' id="'.$this->id.'"'; }
		if ($this->style != '')	{ $this->_style = ' style="'.$this->style.'"'; }
		if ($this->title != '')	{ $this->_title = ' title="'.$this->title.'"'; }
	}

}
?>