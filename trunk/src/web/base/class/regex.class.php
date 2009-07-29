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
 * Regex
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
//----------------------------------------------------------------------------------------
class regex
{
	//--------------------------------
	/**
	 * pereg_match()
	 *
	 * @param string $pattern
	 * @param string $match
	 * @return array|null
	 */
	//--------------------------------
	static public function match($pattern, $match) {
		@preg_match($pattern, $match, $matches);
		if(debug::active()) {
			$error = error_get_last();
			if(strstr($error['message'], 'preg_match')) {
				$msg = str_replace('preg_match() [<a href=\'function.preg-match\'>function.preg-match</a>]:', '' , $error['message']);
				debug::add($msg.' in '. $pattern, 'ERROR');
			}
		}
		if($matches) {
			return $matches;
		} else {
			return null;
		}
	}
	//--------------------------------
	/**
	 * pereg_replace()
	 *
	 * @param string $pattern
	 * @param string $replace
	 * @param string $string
	 * @return array|null
	 */
	//--------------------------------
	static public function replace($pattern, $replace, $string) {
		$error = '';
		$str = @preg_replace($pattern, $replace, $string) | $error;
		echo $error;
		if(debug::active()) {
			$error = error_get_last();
			if(strstr($error['message'], 'preg_replace')) {
				$msg = str_replace('preg_replace() [<a href=\'function.preg-replace\'>function.preg-replace</a>]:', '' , $error['message']);
				debug::add($msg.' in '. $pattern, 'ERROR');
			}
		}
		return $str;
	}

}
?>
