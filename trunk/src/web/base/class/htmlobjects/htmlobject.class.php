<?php
/**
 * @package htmlobjects
 *
 */

 /**
 * Htmlobjects
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */

class htmlobject {

	var $_path;
	var $_debug;
	var $_http;



	function htmlobject( $path ) {
		$this->_path = $path;
	}

	function debug($level = 1, $tag = 'htmlobject_debug') {
		$this->_debug = 'debug';
	}

	/**
	 * build objects
	 *
	 * @access protected
	 */
	function factory( $name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null ) {
		if (!is_string( $name ) || !strlen( $name )) {
			throw new exception('Die zu ladende Klasse muss in einer Zeichenkette benannt werden');
		}

		$file  = $this->_path.'/htmlobject.'.$name;
		require_once( $file.'.class.php' );
		$class = 'htmlobject_'.$name;
		if($this->_debug === 'debug') {
			require_once( $this->_path.'/htmlobject.debug.class.php' );
			if( file_exists($file.'.'.$this->_debug.'.class.php') ) {
				require_once( $file.'.'.$this->_debug.'.class.php' );
				$class = $class.'_'.$this->_debug;
			}
		}	
		return new $class( $arg1, $arg2, $arg3, $arg4, $arg5, $arg6 );
	}

	function http() {		
		if($this->_http) {
			$http = $this->_http;
		} else {
			$http = $this->factory( 'http' );
			$this->_http = $http;
		}
		return $http;
	}

	function base() {
		return $this->factory( 'base' );
	}

	function box() {
		$this->base();
		return $this->factory( 'box' );
	}

	function button() {
		$this->base();
		return $this->factory( 'button' );
	}

	function div() {
		return $this->factory( 'div' );
	}

	function form() {
		$this->base();
		return $this->factory( 'form' );
	}

	function formbuilder() {
		return $this->factory( 'formbuilder', $this );
	}

	function input() {
		$this->base();
		return $this->factory( 'input' );
	}

	function select() {
		$this->base();
		return $this->factory( 'select' );
	}

	function table() {
		$this->base();
		return $this->factory( 'table' );
	}

	function tablebuilder( $sort = '', $order = '', $limit = '', $offset = '', $var_prefix = 'table_' ) {
		$this->table();
		return $this->factory( 'tablebuilder', $sort, $order, $limit, $offset, $var_prefix, $this);
	}

	function tabmenu( $data, $prefix ) {
		$this->base();
		$this->div();
		return $this->factory( 'tabmenu', $data, $prefix, '', $this->http());
	}

	function template($file) {
		return $this->factory( 'template', $file );
	}

	function textarea() {
		$this->base();
		return $this->factory( 'textarea' );
	}

	function td() {
		$this->base();
		return $this->factory( 'td' );
	}

	function tr() {
		$this->base();
		return $this->factory( 'tr' );
	}


}
?>
