<?php
/**
 * @package htmlobjects
 *
 */  

/**
 * Base Class
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */

class htmlobject_base extends htmlobject_http
{
/**
* Attribute class
* @access public
* @var string
*/
var $css = '';
/**
* Attribute id
* @access public
* @var string
*/
var $id = '';
/**
* Attribute style
* @access public
* @var string
*/
var $style = '';
/**
* Attribute title
* @access public
* @var string
*/
var $title = '';

/**
* adds an eventhandler to
* @access public
* @var string
*/
var $handler = '';

/**
* string of attribs
* @access protected
* @var string
*/
var $_init;

	/**
	 * init attribs
	 *
	 * @access protected
	 */
	function init() {
		$this->_init = '';
		if ($this->css != '')  		{ $this->_init .= ' class="'.$this->css.'"'; }
		if ($this->style != '')		{ $this->_init .= ' style="'.$this->style.'"'; }
		if ($this->title != '')		{ $this->_init .= ' title="'.$this->title.'"'; }
		if ($this->handler != '')	{ $this->_init .= ' '.$this->handler; }
		// set id
		if ($this->id == '') 		{ $this->set_id(); }
		if ($this->id != '')		{ $this->_init .= ' id="'.$this->id.'"'; }
	}

	/**
	 * set html id
	 *
	 * @access public
	 * @param string $id
	 */
	function set_id($id = '') {
		if($id != '') {
			$this->id = $id;
		}
		// if no id is set
		if($this->id == '') {
			if(isset($this->name)) {
				$this->id = $this->name;				
			} else {
				$this->id = uniqid('p');
			}				
		}
	}
	
}

/**
 * Div
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_div extends htmlobject_base
{
/**
* text
* @access private
* @var string
*/
var $text = '';

	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */	
	function get_string() {
	$_strReturn = '';
		$this->init();
		$_strReturn = "\n<div$this->_init>$this->text</div>";
	return $_strReturn;
	}
}

?>
