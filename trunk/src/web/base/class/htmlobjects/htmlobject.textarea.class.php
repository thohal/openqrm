<?php
/**
 * @package htmlobjects
 *
 */

 /**
 * Textarea
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_textarea extends htmlobject_base
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
	 * init attribs
	 *
	 * @access protected
	 */
	function init() {
		parent::init();
		if ($this->cols != '')			{ $this->_init .= ' cols="'.$this->cols.'"'; }
		if ($this->disabled === true)	{ $this->_init .= ' disabled'; }
		if ($this->name != '')  		{ $this->_init .= ' name="'.$this->name.'"'; }
		if ($this->readonly === true)	{ $this->_init .= ' readonly'; }
		if ($this->rows != '')			{ $this->_init .= ' rows="'.$this->rows.'"'; }
		if ($this->tabindex != '')  	{ $this->_init .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->wrap != '')  		{ $this->_init .= ' wrap="'.$this->wrap.'"'; }
	}

	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	function get_string() {
	$_strReturn = '';
		$this->init();
		$_strReturn = "\n<textarea$this->_init>";
		$_strReturn .= $this->text;
		$_strReturn .= "</textarea>\n";
	return $_strReturn;
	}
}
?>
