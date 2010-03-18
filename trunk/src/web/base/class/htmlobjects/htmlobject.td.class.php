<?php
/**
 * @package htmlobjects
 */

//----------------------------------------------------------------------------------------
/**
 * Td
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
*/
//----------------------------------------------------------------------------------------

class htmlobject_td extends htmlobject_base
{
/**
* colspan
* @access public
* @var int
*/
var $colspan = '';
/**
* td type
* @access public
* @var enum (td,th)
*/
var $type = 'td';
/**
* Content of td
* @access public
* @var string
*/
var $text = '';


	function get_attribs() {
		$str = parent::get_attribs();
		if ($this->colspan != '') { $str .= ' colspan="'.$this->colspan.'"'; }
		return $str;
	}

	function get_string() {
		$str     = '';
		$attribs = $this->get_attribs();
		$text    = $this->text;
		if(!is_array($text)) {
			$text = array($text);
		}

		foreach($text as $value) {
			if(is_object($value)) {
				$str .= $value->get_string();
			} else {
				$str .= $value;
			}
		}
		$_str  = "\n<$this->type$attribs>";
		$_str .= $str;
		$_str .= "</$this->type>";
	return $_str;
	}

	function add($text) {
		$this->text[] = $text;
	}
}
?>
