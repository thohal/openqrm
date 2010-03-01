<?php
/**
 * @package htmlobjects
 *
 */

/**
 * Select
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */

class htmlobject_select_debug extends htmlobject_select
{

	function get_options() {
		$error = '';
		if(count($this->text) > 0){
			if(!isset($this->text[0][$this->text_index['value']])) {
				$keys = array_keys($this->text[0]);
				$key = $keys[0];
				$error[] = '$this->text_index[\'value\'] expected  '. $this->text_index['value'].' found '. $key;
			}
			if(!isset($this->text[0][$this->text_index['text']])) {
				$keys = array_keys($this->text[0]);
				$key = $keys[1];
				$error[] = '$this->text_index[\'text\'] expected  '. $this->text_index['text'].' found '. $key;
			}
			if($error !== '') {
				htmlobject_debug::_print( '', 'get_string', __CLASS__ );
				htmlobject_debug::_print( 'ERROR',  $error[0]);
				if(isset($error[1])) {
					htmlobject_debug::_print( 'ERROR',  $error[1]);
				}
			}
		}
		return parent::get_options();
	}

}
?>
