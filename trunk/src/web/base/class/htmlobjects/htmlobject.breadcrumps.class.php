<?php
/**
 * @package htmlobjects
 *
 */

/**
 * Breadcrumps
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */

class htmlobject_breadcrumps
{

var $_string ;




	function set( $path, $param, $params = array() ) {

#echo get_class($this);

		$this->_string = '';
		$http = new htmlobject_http();
		if( count($params) > 0 ) {
			$string = split( '/', $params[$param]);
			unset($params[$param]);
			$params = $http->get_params_string('&', $params, true);
			$this->_string .= '<a href="'.$path.'?'.$param.'='.$params.'">..</a>  / ';
			$s = '';
			foreach( $string as $key => $value ) {
				if( $value !== '' ) {
					$s .= $value.'/';
					$p = $path.'?'.$param.'='.$s.$params;
					$this->_string .= '<a href="'.$p.'">'.$value.'</a>  / ';
				}
			}
		}
	}

	function get_string() {
		return '<div id="breadcrumps">'.$this->_string.'</div>';
	}

}

?>
