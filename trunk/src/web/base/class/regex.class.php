<?php
/**
 * @package htmlobjects
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
