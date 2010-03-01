<?php
/**
 * @package htmlobjects
 *
 */

/**
 * Box
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
 
class htmlobject_box extends htmlobject_base
{

/**
* Label (Title) of box
* @access public
* @var string
*/
var $label = '';
/**
* Label for input
* @access public
* @var string
*/
var $label_for = '';
/**
* content
* @access public
* @var object | string
*/
var $content = '';
/**
* css class for left box
* @access public
* @var string
*/
var $css_left = 'left';
/**
* css class for right box
* @access public
* @var string
*/
var $css_right = 'right';

/**
* extra content
* @access private
* @var array
*/
var $arr_content = array();

	/**
	 * init attribs
	 *
	 * @access protected
	 */
	function init() {
		parent::init();
		if ($this->content == '')	{ $this->content = '&#160;'; }
		if ($this->css_left != '') 	{ $this->css_left = ' class="'.$this->css_left.'"'; }
		if ($this->css_right != '') { $this->css_right = ' class="'.$this->css_right.'"'; }
	}

	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	function get_string() {
	$_strReturn = '';

		if( is_object($this->content) ) {
			$this->id = $this->content->id.'_box'; 
			$content  = $this->content->get_string();
		}
		if( is_string($this->content) ) { 
			$content = $this->content; 
		}

		if($this->label !== '') {
			$this->init();
			$_strReturn .= "\n<div".$this->_init.">";
			$_strReturn .= "\n<div".$this->css_left.">";
			if(is_object($this->content) && isset($this->content->id)) { $_strReturn .= '<label for="'.$this->content->id.'">'.$this->label.'</label>'; }
			if(is_string($this->content)) {
				if($this->label_for != '') { $_strReturn .= '<label for="'.$this->label_for.'">'.$this->label.'</label>'; }
				else { $_strReturn .= $this->label; }
			}
			$_strReturn .= "</div>";
			$_strReturn .= "\n<div".$this->css_right.">";
			$_strReturn .= $content;
			$_strReturn .= "</div>";
			$_strReturn .= "\n<div style=\"line-height:0px;height:0px;clear:both;\" class=\"floatbreaker\">&#160;</div>";
			$_strReturn .= "\n</div>";
		} else {
			$_strReturn .= $content;
		}
	return $_strReturn;
	}

	/**
	 * Add additional content
	 *
	 * @access public
	 */
	function add($content) {
		$this->arr_content[] = $content;
	}

}
?>
