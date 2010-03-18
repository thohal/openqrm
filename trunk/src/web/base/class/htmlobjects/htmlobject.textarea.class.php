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
	function get_attribs() {
		$str = parent::get_attribs();
		if ($this->cols != '')			{ $str .= ' cols="'.$this->cols.'"'; }
		if ($this->disabled === true)	{ $str .= ' disabled'; }
		if ($this->name != '')  		{ $str .= ' name="'.$this->name.'"'; }
		if ($this->readonly === true)	{ $str .= ' readonly'; }
		if ($this->rows != '')			{ $str .= ' rows="'.$this->rows.'"'; }
		if ($this->tabindex != '')  	{ $str .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->wrap != '')  		{ $str .= ' wrap="'.$this->wrap.'"'; }
		return $str;
	}

	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	function get_string() {
	$_strReturn = '';
		$attribs = $this->get_attribs();
		$_strReturn = "\n<textarea$attribs>";
		$_strReturn .= $this->text;
		$_strReturn .= "</textarea>\n";
	return $_strReturn;
	}
}
?>
