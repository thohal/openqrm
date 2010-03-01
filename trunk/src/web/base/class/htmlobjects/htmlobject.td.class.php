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


	function init() {
		parent::init();
		if ($this->colspan != '') { $this->_init .= ' colspan="'.$this->colspan.'"'; }
	}

	function get_string() {
	$_strReturn = '';
		$this->init();
		$_strReturn = "\n<$this->type$this->_init>";
		$_strReturn .= $this->text;
		$_strReturn .= "</$this->type>";
	return $_strReturn;
	}
}
?>
