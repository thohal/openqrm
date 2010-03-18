<?php
/**
 * @package htmlobjects
 */

 //----------------------------------------------------------------------------------------
/**
 * Table
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
*/
//----------------------------------------------------------------------------------------

class htmlobject_table extends htmlobject_base
{
/**
* align
* @access public
* @var enum (left | center | right)
*/
var $align = '';
/**
* table border
* @access public
* @var int
*/
var $border = '';
/**
* table backgroundcolor
* @access public
* @var HEX
*/
var $bgcolor = '';
/**
* cellpadding
* @access public
* @var int
*/
var $cellpadding;
/**
* cellspacing
* @access public
* @var int
*/
var $cellspacing;
/**
* frame
* @access public
* @var enum (void | above | below | hsides | lhs | rhs | vsides | box | border)
*/
var $frame = '';
/**
* rules
* @access public
* @var enum (none | groups | rows | cols | all)
*/
var $rules = '';
/**
* summary
* @access public
* @var string
*/
var $summary = '';
/**
* width
* @access public
* @var int
*/
var $width = '';

/**
* Content of table
* @access public
* @var array
*/
var $arr_table = array();


	function get_attribs() {
		$str = parent::get_attribs();
		if ($this->align != '') { $str .= ' align="'.$this->align.'"'; }
		if (isset($this->border) && $this->border !== '') { $str .= ' border="'.$this->border.'"'; }
		if ($this->bgcolor != '') { $str .= ' bgcolor="'.$this->bgcolor.'"'; }
		if (isset($this->cellpadding) && $this->cellpadding !== '') { $str .= ' cellpadding="'.$this->cellpadding.'"'; }
		if (isset($this->cellspacing) && $this->cellspacing !== '') { $str .= ' cellspacing="'.$this->cellspacing.'"'; }
		if ($this->frame != '') { $str .= ' frame="'.$this->frame.'"'; }
		if ($this->rules != '') { $str .= ' rules="'.$this->rules.'"'; }
		if ($this->summary != '') { $str .= ' summary="'.$this->summary.'"'; }
		if ($this->width != '') { $str .= ' width="'.$this->width.'"'; }
		return $str;
	}

	function get_string() {
	$_strReturn = '';
		$attribs = $this->get_attribs();
		$_strReturn = "\n<table$attribs>";
		foreach($this->arr_table as $tr) {
			if(is_object($tr) == true && get_class($tr) == 'htmlobject_tr') {
				$_strReturn .= $tr->get_string();
			}
			elseif(is_string($tr) == true) {
				$_strReturn .= $tr;
			}
			else {
				$_strReturn .= 'tr type not defined';
			}
		}
		$_strReturn .= "</table>\n";
	return $_strReturn;
	}

	function add($tr) {
		$this->arr_table[] = $tr;
	}

}
?>
