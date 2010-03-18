<?php
/**
 * @package htmlobjects
 *
 */

 /**
 * Input
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_input extends htmlobject_base
{
/**
* @access public
* @var bool
*/
var $checked = false;
/**
* disable select
* @access public
* @var bool
*/
var $disabled = false;
/**
* maxlength
* @access public
* @var int
*/
var $maxlength;
/**
* Attribute name
* @access public
* @var string
*/
var $name = '';
/**
* number of lines to be shown
* @access public
* @var int
*/
var $size = '';
/**
* Attribute tabindex
* @access public
* @var int
*/
var $tabindex = '';
/**
* type of element
* @access public
* @var enum text | password | checkbox | radio | submit | reset | file | hidden | image | button
*/
var $type = '';
/**
* value of input
* @access public
* @var string
*/
var $value = '';

	/**
	 * init attribs
	 *
	 * @access protected
	 */
	function get_attribs() {
		$str = parent::get_attribs();
		if ($this->checked !== false)  	{ $str .= ' checked="checked"'; }
		if ($this->disabled === true)	{ $str .= ' disabled="disabled"'; }
		if ($this->maxlength != '')		{ $str .= ' maxlength="'.$this->maxlength.'"'; }
		if ($this->name != '')  		{ $str .= ' name="'.$this->name.'"'; }
		if ($this->size != '')			{ $str .= ' size="'.$this->size.'"'; }
		if ($this->tabindex != '')  	{ $str .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->value != '')  		{ $str .= ' value="'.$this->value.'"'; }
		$this->type = strtolower($this->type);
		switch($this->type) {
			case 'text':
			case 'password':
			case 'checkbox':
			case 'radio':
			case 'submit':
			case 'reset':
			case 'hidden':
			case 'image':
			case 'button':
			case 'file':
				$str .= ' type="'.$this->type.'"';
			break;
			default:
				$str .= ' type="text"';
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
		$_strReturn = "<input$attribs>";
	return $_strReturn;
	}
}

?>
