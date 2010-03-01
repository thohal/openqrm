<?php
/**
 * @package htmlobjects
 *
 */

 /**
 * Input
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_input_debug extends htmlobject_input
{

var $supported = array(
		'text',
		'password',
		'checkbox',
		'file',
		'radio',
		'submit',
		'reset',
		'hidden',
		'image',
		'button',
	);

	function init() {
		if(! in_array( $this->type , $this->supported) ) {
			htmlobject_debug::_print( '', 'init', __CLASS__ );
			htmlobject_debug::_print( 'ERROR', 'input type '. $this->type.' is not supported' );
		}
		parent::init();
	}
}
?>
