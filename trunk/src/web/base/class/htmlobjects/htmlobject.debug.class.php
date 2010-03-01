<?php
/**
 * @package htmlobjects
 */

//----------------------------------------------------------------------------------------
/**
 * Http
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
//----------------------------------------------------------------------------------------

class htmlobject_debug
{


	function _print( $level, $msg, $class = '') {
		if($level === '') {
			$msg = '<b>'.str_replace('_debug', '',  $class ).': '.$msg.'</b><br>';
			print $msg;
			print '<small>backtrace {<br>';
			foreach(debug_backtrace() as $key => $msg) {
				print '&#160;&#160;&#160;&#160;';
				print basename($msg['file']).' ';
				print 'line: '.$msg['line'].' ';
				print '['.$msg['class'].$msg['type'].$msg['function'].'()]';
				print '<br>';
			}
			print '}</small><br>';
			#echo '<pre>';
			#print_r();
			#echo '</pre>';

		} else {
			print $level.': '.$msg.'<br>';
		}
	}

	/*
	function _print( $level, $msg) {
		if($level === '') {
			$msg = '<b>'.str_replace('_debug', '',  __CLASS__ ).': '.$msg.'</b><br>';
			print $msg;
		} else {
			print $level.': '.$msg.'<br>';
		}
	}
	*/

}
?>
