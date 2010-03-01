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
	function init() {
		parent::init();
		if ($this->disabled === true)	{ $this->_init .= ' disabled="disabled"'; }
		if ($this->name != '')  		{ $this->_init .= ' name="'.$this->name.'"'; }
		if ($this->tabindex != '')  	{ $this->_init .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->value != '')  		{ $this->_init .= ' value="'.$this->value.'"'; }
		$this->type = strtolower($this->type);
		switch($this->type) {
			case 'submit':
			case 'reset':
			case 'button':
				$this->_init .= ' type="'.$this->type.'"';
			break;
			default:
				$this->_init .= ' type="button"';
				#if(debug::active()) {
				#	debug::add('type '.$this->type.' not supported - type set to button', 'ERROR');
				#}
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
		$_strReturn = "\n<button$this->_init>$this->label</button>";
	return $_strReturn;
	}
}

?>
