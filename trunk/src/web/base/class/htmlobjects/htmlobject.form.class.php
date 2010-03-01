<?
/**
 * @package htmlobjects
 *
 */

/**
 * Form
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_form extends htmlobject_base
{
/**
* uri
* @access public
* @var string
*/
var $action = '';
/**
* mime type
* @access public
* @var string
*/
var $enctype = '';
/**
* Post/Get
* @access public
* @var string
*/
var $method = '';
/**
* Attribute name
* @access public
* @var string
*/
var $name = '';
/**
* target
* @access public
* @var string
*/
var $target = '';
/**
* form elements
* @access public
* @var string
*/
var $fields = '';

	/**
	 * init attribs
	 *
	 * @access protected
	 */
	function init() {
		parent::init();
		if ($this->action != '')  		{ $this->_init .= ' action="'.$this->action.'"'; }
		if ($this->enctype != '')  		{ $this->_init .= ' enctype="'.$this->enctype.'"'; }
		if ($this->method != '')  		{ $this->_init .= ' method="'.$this->method.'"'; }
		if ($this->name != '')  		{ $this->_init .= ' name="'.$this->name.'"'; }
		if ($this->target != '')  		{ $this->_init .= ' target="'.$this->target.'"'; }
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
		$_strReturn .= "\n<form$this->_init>\n";
		$_strReturn .= $this->fields;
		$_strReturn .= "\n</form>\n";
	return $_strReturn;
	}
}
?>
