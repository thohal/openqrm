<?php
/**
 * @package htmlobjects
 */
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
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

class debug
{
static protected $debug = false;
static protected $panic = false;
static protected $_infos = array();
	//--------------------------------
	/**
	* Start Debugger
	*
	* @access public
	*/
	//--------------------------------
	public static function start($panic = false) {
		if($panic === true) {
			self::$panic = true; 
		}
		self::$debug = true;
	}
	//--------------------------------
	/**
	* Stop Debugger
	*
	* @access public
	*/
	//--------------------------------
	public static function stop() {
		self::$debug = false;
	}
	//--------------------------------
	/**
	* Stop Debugger
	*
	* @access public
	*/
	//--------------------------------
	public static function active() {
		return self::$debug;
	}

	//--------------------------------
	/**
	* Add string to debugger info array
	*
	* @access public
	* @param string $msg
	* @param string $state
	*/
	//--------------------------------
	public static function add( $msg, $state = 'INFO' ) {
		if(self::active()) {
			#if((self::$panic === true) || (self::$panic === false && $state != strtolower('INFO'))) {			
				$debug = debug_backtrace();		
				self::$_infos[] = $state.' '.$debug[1]['class'].'->'.$debug[1]['function'].'() '.$msg;
				#for($i = 2; $i < count($debug); $i++) {
				#	self::$_infos[] = '---- line '.$debug[$i]['line'].' : '.$debug[$i]['class'].'->'.$debug[$i]['function'].'()';
				#}
			#}
		}
	}
	//--------------------------------
	/**
	* Print Debuger Info
	*
	* @access public
	*/
	//--------------------------------
	public static function flush() {
		if(self::$debug === true) {
			print "Debugger Info\n";
			print "<pre>\n";
			foreach(self::$_infos as $msg) {
				print $msg."\n";
			}
			print '</pre>';
			// unset array
			self::$_infos = array();
		}
	}



}
?>
