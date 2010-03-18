<?php
/**
 * @package htmlobjects
 */
 
//----------------------------------------------------------------------------------------
/**
 * Tr
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
*/
//----------------------------------------------------------------------------------------

class htmlobject_tr extends htmlobject_base
{
/**
* Content of tr
* @access public
* @var string
*/
var $arr_tr = array();


	function get_string() {
	$_strReturn = '';
		$str = $this->get_attribs();
		$_strReturn = "\n<tr$str>";
		foreach($this->arr_tr as $td) {
			if(is_object($td) == true && get_class($td) == 'htmlobject_td') {
				$_strReturn .= $td->get_string();
			}
			elseif(is_string($td) == true) {
				$_strReturn .= $td;
			}
			else {
				$_strReturn .= 'td type not defined';
			}
		}
		$_strReturn .= "</tr>\n";
	return $_strReturn;
	}

	function add($td) {
		$this->arr_tr[] = $td;
	}

}
?>
