<?php
/**
 * @package htmlobjects
 *
 */

/**
 * Formbuilder
 * uses class htmlobject_input, htmlobject_button
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
  */

class htmlobject_formbuilder_debug extends htmlobject_formbuilder
{

	function htmlobject_formbuilder_debug( $htmlobject ) {
		parent::htmlobject_formbuilder($htmlobject);
	}

	function init( $data ) {

		$error = null;

		foreach($data as $key => $value) {
			if(!isset($value['object'])) {
				$error[] = array('ERROR', '["'.$key.'"]["object"] not set');
			}
			if(!isset($value['object']['type'])) {
				$error[] = array('ERROR', '["'.$key.'"]["object"]["type"] not set');
			} else {
				switch($value['object']['type']) {
					case 'htmlobject_input':
					case 'htmlobject_select':
					case 'htmlobject_textarea':
					case 'htmlobject_button':
						break;
					default:
						$error[] = array('ERROR', $value['object']['type'].' is not supported');
					break;
				}
			}

			if(!isset($value['object']['attrib'])) {
				$error[] = array('ERROR', '["'.$key.'"]["object"]["attrib"] not set');
			}

			if(!isset($value['object']['attrib']['name'])) {
				$error[] = array('ERROR', '["'.$key.'"]["object"]["attrib"]["name"] not set');
			}
			elseif ($value['object']['attrib']['name'] == '') {
				$error[] = array('ERROR', '["'.$key.'"]["object"]["attrib"]["name"] is empty');
			}

			if(isset($value['validate']) &&
				!isset($value['validate']['errormsg'])
			) {
				$error[] = array('ERROR', '["'.$key.'"]["validate"]["errormsg"] not set');
			}
			elseif (isset($value['validate']) &&
					$value['validate']['errormsg'] == ''
			) {
				$error[] = array('NOTICE', '["'.$key.'"]["validate"]["errormsg"] is empty');
			}
		}

		if($error) {
			htmlobject_debug::_print( '', 'construct', __CLASS__ );
			foreach( $error as $value ) {
				htmlobject_debug::_print( $value[0], $value[1] );
			}
		}

		parent::init( $data );
	}

	
} // end class
?>
