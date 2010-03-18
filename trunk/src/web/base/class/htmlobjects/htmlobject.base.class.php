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

class htmlobject_base
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
#var $_init;

	/**
	 * init attribs
	 *
	 * @access protected
	 */
	function get_attribs() {
		$str = '';
		if ($this->css != '')  		{ $str .= ' class="'.$this->css.'"'; }
		if ($this->style != '')		{ $str .= ' style="'.$this->style.'"'; }
		if ($this->title != '')		{ $str .= ' title="'.$this->title.'"'; }
		if ($this->handler != '')	{ $str .= ' '.$this->handler; }
		// set id
		if ($this->id == '') 		{ $this->set_id(); }
		if ($this->id != '')		{ $str .= ' id="'.$this->id.'"'; }
		return $str;
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
?>
