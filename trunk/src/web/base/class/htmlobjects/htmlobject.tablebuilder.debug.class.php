<?php
/**
 * @package htmlobjects
 */

//----------------------------------------------------------------------------------------
/**
 * Tablebuilder
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
*/
//----------------------------------------------------------------------------------------

class htmlobject_tablebuilder_debug extends htmlobject_tablebuilder
{

var $_error_body;
var $_error_head;
var $_send;

	function init_table() {
		parent::init_table();

		if( isset($this->identifier) && $this->identifier !== '' ) {

			if( !isset($this->actions) || count($this->actions) < 1) {

			htmlobject_debug::_print( '', 'init');
			htmlobject_debug::_print('ERROR', 'actions not set');

			}
		}

		#echo '<pre>';
		#print_r($this);
		#echo '</pre>';
	}

	//----------------------------------------------------------------------------------------
	/**
	* builds table head
	* @access public
	* @return object|string htmlobject_tr or empty string
	*/
	//----------------------------------------------------------------------------------------	
	function get_table_head() {

		$error = null;
		if(count($this->head) < 1) {
			$this->_error_head[] = array('ERROR', 'No Table head set');
		} else {
			foreach( $this->head as $key => $value ) {
				foreach($this->_body as $body) {
					if(!in_array($key, array_keys($body))) {
						$this->_error_body[] = array('ERROR', 'array index ['.$key.'] is not set');
						$error = true;
					}
					break;
				}

				if($error) {
					if( isset($this->head[$key]) ) {
						$this->_error_head[] = array('NOTICE', 'array index ['.$key.'] is set');
					}
					if( isset($this->head[$key]['hidden']) ) {
						$this->_error_head[] = array('NOTICE', 'array index ['.$key.'][hidden] is set');
					}
					if( isset($this->head[$key]['sortable']) ) {
						$this->_error_head[] = array('NOTICE', 'array index ['.$key.'][sortable] is set');
					}
					if(isset($this->head[$key]['title'])) {
						$this->_error_head[] = array('NOTICE', 'array index ['.$key.'][title] is set');
					}
				} else {
					if(!isset($this->head[$key]['title'])) {
						$this->_error_head[] = array('ERROR', 'array index ['.$key.'][title] is not set');
					}
				}

				$error = null;
			}

			if( $this->_error_head ) {
				htmlobject_debug::_print( '', 'Building head', __CLASS__ );
				foreach( $this->_error_head as $error ) {
					htmlobject_debug::_print( $error[0], $error[1] );
					$this->_send_head = true;
				}
			}
		}
		return parent::get_table_head();
	}
	//----------------------------------------------------------------------------------------
	/**
	* adds one row to table body
	* @access public
	* @param array $val 
	* @return object|string htmlobject_tr or empty string
	*/
	//----------------------------------------------------------------------------------------		
	function get_table_body($key, $val, $i) {

		if( isset($this->identifier) && !in_array( $this->identifier, array_keys($val) ) ) {
			$this->_error_body[] = array('ERROR', 'index not found ['.__CLASS__.'->identifier = "'.$this->identifier.'"]');
		}

		foreach($val as $key2 => $value) {
			if(!isset($this->head[$key2])) {
				$this->_error_body[] = array('ERROR', 'body index ['.$key2.'] not found in head array');
			}

		}

		if( $this->_error_body && !$this->_send ) {
			htmlobject_debug::_print( '', 'Building body', __CLASS__ );
			foreach( $this->_error_body as $error ) {
				htmlobject_debug::_print( $error[0], $error[1] );
				$this->_send = true;
			}
		}

		return parent::get_table_body( $key, $val, $i );
	}



}//-- end class
?>
