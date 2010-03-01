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
	function init() {
		parent::init();
		if ($this->checked !== false)  	{ $this->_init .= ' checked="checked"'; }
		if ($this->disabled === true)	{ $this->_init .= ' disabled="disabled"'; }
		if ($this->maxlength != '')		{ $this->_init .= ' maxlength="'.$this->maxlength.'"'; }
		if ($this->name != '')  		{ $this->_init .= ' name="'.$this->name.'"'; }
		if ($this->size != '')			{ $this->_init .= ' size="'.$this->size.'"'; }
		if ($this->tabindex != '')  	{ $this->_init .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->value != '')  		{ $this->_init .= ' value="'.$this->value.'"'; }
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
				$this->_init .= ' type="'.$this->type.'"';
			break;
			default:
				$this->_init .= ' type="text"';
			break;
		}
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
		$_strReturn = "<input$this->_init>";
	return $_strReturn;
	}
}

?>
