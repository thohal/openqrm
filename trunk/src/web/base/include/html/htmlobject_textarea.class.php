<?php
class htmlobject_textarea extends htmlobject
{
/**
* Attribute cols
* @access private
* @var int
*/
var $cols = '';
/**
* disable textarea 
* @access private
* @var bool
*/
var $disabled = false;
/**
* Attribute name (should the the same as Attribute id)
* @access private
* @var string
*/
var $name = '';
/**
* set textarea to readonly
* @access private
* @var bool
*/
var $readonly = false;
/**
* number of rows
* @access private
* @var int
*/
var $rows = array();
/**
* Attribute tabindex
* @access private
* @var int
*/
var $tabindex = '';
/**
* wrap type (physical,virtual,none)
* @access private
* @var string
*/
var $wrap = '';

/**
* Content of textarea
* @access private
* @var string
*/
var $text = '';


/**
* internal use only
*/
var $_cols;
var $_disabled;
var $_name;
var $_readonly;
var $_rows;
var $_tabindex;
var $_wrap;


function init_htmlobject_textarea() {
	if ($this->cols != '')			{ $this->_cols = ' cols="'.$this->cols.'"'; }
	if ($this->disabled === true)	{ $this->_disabled = ' disabled'; }
	if ($this->name != '')  		{ $this->_name = ' name="'.$this->name.'"'; }
	if ($this->readonly === true)	{ $this->_readonly = ' readonly'; }
	if ($this->rows != '')			{ $this->_rows = ' rows="'.$this->rows.'"'; }
	if ($this->tabindex != '')  	{ $this->_tabindex = ' tabindex="'.$this->tabindex.'"'; }
	if ($this->wrap != '')  		{ $this->_wrap = ' wrap="'.$this->wrap.'"'; }
}

function get_string() {
$this->init_htmlobject();
$this->init_htmlobject_textarea();
$_strReturn = '
<textarea'.$this->_id.
			$this->_name.
			$this->_css.
			$this->_title.
			$this->_style.
			$this->_cols.
			$this->_disabled.
			$this->_readonly.
			$this->_rows.
			$this->_tabindex.
			$this->_wrap.'>';
			
$_strReturn .= $this->text;
$_strReturn .= "</textarea>\n";
return $_strReturn;
}


}
?>