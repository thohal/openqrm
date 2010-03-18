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
var $elements = array();

	/**
	 * init attribs
	 *
	 * @access protected
	 */
	function get_attribs() {
		$str = parent::get_attribs();
		if ($this->action != '')  		{ $str .= ' action="'.$this->action.'"'; }
		if ($this->enctype != '')  		{ $str .= ' enctype="'.$this->enctype.'"'; }
		if ($this->method != '')  		{ $str .= ' method="'.$this->method.'"'; }
		if ($this->name != '')  		{ $str .= ' name="'.$this->name.'"'; }
		if ($this->target != '')  		{ $str .= ' target="'.$this->target.'"'; }
		return $str;
	}

	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	function get_string() {
		$str = '';
		$arr = $this->get_template_array();
		foreach($arr as $key => $value) {
			if($key === 'formbuilder') {
				foreach($value as $val) {
					$str .= $val;
				}
			} else {
				$str .= $value;
			}
		}
		$attribs = $this->get_attribs();
		$_strReturn = '';
		$_strReturn .= "\n<form$attribs>\n";
		$_strReturn .= $str;
		$_strReturn .= "\n</form>\n";
		return $_strReturn;
	}

	function add($object, $key = null) {
		$this->elements[$key] = $object;
	}

	function get_template_array() {
		$arr = array();
		foreach($this->elements as $key => $value) {
			if(is_object($value)){	
				if(
					$value instanceof htmlobject_formbuilder ||
					$value instanceof htmlobject_formbuilder_debug
				) {	
					$arr[$key] = $value->get_template_array();
				} else {
					$arr[$key] = $value->get_string();
				}
			}
		}
		return $arr;
	}

}
?>
