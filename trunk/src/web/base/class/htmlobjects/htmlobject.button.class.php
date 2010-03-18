<?php
/**
 * @package htmlobjects
 *
 */

 /**
 * Button
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
 
class htmlobject_button extends htmlobject_base
{
/**
* disable select
* @access public
* @var bool
*/
var $disabled = false;
/**
* Attribute name
* @access public
* @var string
*/
var $name = '';
/**
* Attribute tabindex
* @access public
* @var int
*/
var $tabindex = '';
/**
* type of element
* @access public
* @var enum button | submit | reset
*/
var $type = '';
/**
* value of input
* @access public
* @var string
*/
var $value = '';
/**
* value of input
* @access public
* @var string
*/
var $label = '';

	/**
	 * init attribs
	 *
	 * @access protected
	 */
	function get_attribs() {
		$str = parent::get_attribs();
		if ($this->disabled === true)	{ $str .= ' disabled="disabled"'; }
		if ($this->name != '')  		{ $str .= ' name="'.$this->name.'"'; }
		if ($this->tabindex != '')  	{ $str .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->value != '')  		{ $str .= ' value="'.$this->value.'"'; }
		$this->type = strtolower($this->type);
		switch($this->type) {
			case 'submit':
			case 'reset':
			case 'button':
				$str .= ' type="'.$this->type.'"';
			break;
			default:
				$str .= ' type="button"';
			break;
		}
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
		$_strReturn = "\n<button$attribs>$this->label</button>";
	return $_strReturn;
	}
}

?>
