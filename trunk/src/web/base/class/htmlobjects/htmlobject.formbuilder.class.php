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

class htmlobject_formbuilder extends htmlobject_http
{
/**
* string to mark value as required
* @access public
* @var string
*/
var $str_required = '*';
/**
* store data
* @access protected
* @var string
*/
var $data = array();
/**
* store request
* @access protected
* @var array
*/
var $request = array();
/**
* store errors
* @access protected
* @var array|null
*/
var $check_request = null;



var $error_required = 'must not be empty';


var $html;


	//---------------------------------------
	/**
	 * Constructor
	 *
	 * Good to know:
	 * If form is cleared (inputs are empty) and then submitted,
	 * form will load preset values and will not appear empty.
	 * No Errors will be displayed. Form will appear like opening
	 * it for the first time.
	 *
	 * <code>
	 *
	 * $data  = array();
	 * $data['name']                              = array ();
	 * $data['name']['label']                     = 'Name';
	 * $data['name']['required']                  = true;
	 * $data['name']['static']                    = false;
	 * // validation
	 * $data['name']['validate']                  = array ();
	 * $data['name']['validate']['regex']         = '/^[a-z0-9~._-]+$/i';
	 * $data['name']['validate']['errormsg']      = 'string must be a-z0-9~._-';
	 * // build object
	 * $data['name']['object']                    = array ();
	 * $data['name']['object']['type']            = 'htmlobject_input';
	 * $data['name']['object']['attrib']          = array();
	 * $data['name']['object']['attrib']['type']  = 'text';
	 * $data['name']['object']['attrib']['name']  = 'name';
	 * $data['name']['object']['attrib']['value'] = 'somevalue';
	 *
	 * $formbuilder = new htmlobject_formbuilder( $data );
	 *
	 * // Actions
	 * // no errors, do something
	 * if(!$formbuilder->get_errors()) {
	 *		$values = $formbuilder->get_request_as_array();
	 *		print_r($values);
	 * }
	 *
	 * $template = new Template_PHPLIB();
	 * $template->debug = 0;
	 * $template->setFile('t', 'html/template.html');
	 * $template->setVar($formbuilder->get_template_array());
	 *
	 * echo $template->parse('out', 't');
	 *
	 * </code>
	 * @access public
	 * @param object $htmlobject
	 */
	//---------------------------------------
	function htmlobject_formbuilder( $htmlobject ) {
		$this->html = $htmlobject;
	}

	/**
	 * Init Formbuilder
	 *
	 * @access public
	 * @param object $htmlobject
	 */
	function init( $data ) {
		// filter quots (")
		$this->set_request_filter(array(
				array( 'pattern' => '~\r\n~', 'replace' => '\n'),
				array( 'pattern' => '~&lt;~', 'replace' => '<'),
				array( 'pattern' => '~&quot;~', 'replace' => '"'),
			));
		$this->data = $data;
		$this->set_request();
		$this->set_check_request();
	}

	//---------------------------------------
	/**
	 * get request values as array
     *
	 * @access public
	 * @return array
	 */
	//---------------------------------------
	function get_request_as_array() {

		return $this->request;

	}

	//---------------------------------------
	/**
	 * get request values as url params
	 *
	 * @access public
	 * @return string
	 */
	//---------------------------------------
	function get_request_as_string() {

		$strReturn = '';
		foreach ($this->data as $data) {
			if(isset($data['object']['attrib']['name'])) {
				$name = $this->unindex_array($data['object']['attrib']['name']);
				$request = '$this->request'.$this->string_to_index($name);
				if(eval("return isset($request);")) {
					if(is_array(eval("return $request;"))) {
						foreach(eval("return $request;") as $key => $value) {
							$strReturn .= '&'.$name.'['.$key.']='.$value;
						}
					}
					else {
						$strReturn .= '&'.$name.'='. eval("return $request;");
					}
				}
			}
		}
		return $strReturn;

	}

	//---------------------------------------
	/**
	 * get array for html template
	 *
	 * will return
	 * array[$key] = html element as string
	 * $key is
	 *
	 * @access public
	 * @return array of strings
	 */
	//---------------------------------------
	function get_template_array( $name = null ) {

		$ar = array();
		if( $name ) {
			$data[$name] = $this->data[$name];
		} else {
			$data = $this->data;
		}
		
		foreach ($data as $key => $data) {
			$box = $this->html->box();
			$box->label = $this->get_label($data);
			$box->content = $this->get_htmlobject_object($data);
			$box->css = 'htmlobject_box';
			$ar = array_merge($ar, array($key => $box->get_string()));
		}
		return $ar;

	}

	//---------------------------------------
	/**
	 * get errors
	 *
	 * will return array('name' => 'errormsg', ...)
	 * or null if no error occured
	 *
	 * @access public
	 * @return array|null
	 */
	//---------------------------------------
	function get_errors() {
		return $this->check_request;
	}

	//---------------------------------------
	/**
	 * set values from http request as array
	 *
	 * @access protected
	 */
	//---------------------------------------
	function set_request() {

		$arReturn = null;
		foreach ($this->data as $data) {
			if(isset($data['object']['attrib']['name'])) {
				if( !isset($data['static']) || $data['static'] !== true ) {
					// set vars
					$name    = $this->unindex_array($data['object']['attrib']['name']);
					$request = $this->get_request($name);
					if($request) {
						$regex = '~\[(.[^\]]*)\]~';
						preg_match_all($regex, $name, $matches, PREG_SET_ORDER);
						if($matches) {
							$tag   = preg_replace('~\[.*\]~', '', $name);
							$count = count($matches)-1;
							$ar    = &$arReturn[$tag];							
							for($i = 0; $i <= $count; ++$i){
								$ar = &$ar[$matches[$i][1]];
								if($i === $count){
									$ar = $request;
								}
							}
						} else {
							$arReturn[$name] = $request;
						}
					}
				}
			}
		}
		$this->request = $arReturn;

	}

	//---------------------------------------
	/**
	 * Check $this->data request
	 *
	 * Returns array of errors if
	 * request does not match given regex.
	 * Empty if no missmatch occured.
	 *
	 * @access protected
	 * @todo pregmatch for arrays
	 */
	//---------------------------------------
	function set_check_request() {
		foreach ($this->data as $data) {
			// handle validate
			if(
				isset($data['validate']) &&
				isset($data['validate']['regex']) &&
				isset($data['validate']['errormsg']) &&
				isset($data['object']['attrib']['name']) &&
				count($this->request) > 0
			) {
				$regex   = $data['validate']['regex'];
				$name    = $data['object']['attrib']['name'];
				$request = '$this->request'.$this->string_to_index($name);
				if(eval("return isset($request);") && isset($regex) && $regex != '') {
					$matches = @preg_match($regex, eval("return $request;"));
					if(!$matches) {
						$this->check_request[$name] = $data['validate']['errormsg'];
					}
				}
			}
			// handle required
			if(
				isset($data['object']['attrib']['name']) &&
				count($this->request) > 0 &&
				isset($data['required'])
			) {
				$name    = $data['object']['attrib']['name'];
				$request = '$this->request'.$this->string_to_index($name);
				if (eval("return !isset($request);") && isset($data['required']) && $data['required'] == true) {
					$this->check_request[$name] = $data['label'].' '.$this->error_required;
				}
			}
		}

	}

	//---------------------------------------
	/**
	 * get html objects
	 *
	 * @access protected
	 * @param array $data
	 * @return object|null
	 */
	//---------------------------------------
	function get_htmlobject_object($data) {

		if(
			isset($data['object']) &&
			isset($data['object']['type']) &&
			isset($data['object']['attrib']) &&
			isset($data['object']['attrib']['name'])
		) {

			// set vars
			$object  = strtolower($data['object']['type']);
			$attribs = $data['object']['attrib'];
			$name 	 = $data['object']['attrib']['name'];

			// build object
			switch($object) {
				case 'htmlobject_input':
				case 'htmlobject_select':
				case 'htmlobject_textarea':
				case 'htmlobject_button':
					$html = $this->make_htmlobject($object, $attribs);
				break;
			}

			// set request
			if( !isset($data['static']) || $data['static'] !== true ) {
				if(	isset($this->request) && count($this->request) > 0) {
					$request = '$this->request'.$this->string_to_index($name);
				}
			} else {
				$html->value = $data['object']['attrib']['value'];
			} 

			// build return
			if(
				isset($request) &&
				$request != '' &&
				isset($html)
			) {
				// add request to object
				switch($object) {
					case 'htmlobject_input':
						$html = $this->handle_htmlobject_input($html, $request);
					break;
					case 'htmlobject_select':
						$html = $this->handle_htmlobject_select($html, $request);
					break;
					case  'htmlobject_textarea':
						$html = $this->handle_htmlobject_textarea($html, $request);
					break;
				}
				return $html;
			}
			elseif(isset($html)) {
				return $html;
			} else {
				return '';
			}
		}

	}

	//---------------------------------------
	/**
	 * make html objects
	 *
	 * @access protected
	 * @param string $object
	 * @param array $attrib
	 * @return object
	 */
	//---------------------------------------
	function make_htmlobject( $object, $attrib ) {

		$object = str_replace('htmlobject_', '', $object);

		// build htmlobject
		$html = $this->html->$object();
		foreach ($attrib as $key => $param) {
			$html->$key = $param;
		}
		// make sure id is set
		$html->set_id();
		return $html;

	}

	//------- Object Section

	//---------------------------------------
	/**
	 * handle htmlobject_input
	 *
	 * @access protected
	 * @param object $html
	 * @param array $attrib
	 * @param string $request
	 * @return object
	 */
	//---------------------------------------
	function handle_htmlobject_input($html, $request) {

		$html->type = strtolower($html->type);
		switch($html->type) {
			case 'submit':
			case 'reset':
			case 'file':
			case 'image':
			case 'button':
				// do nothing
			break;
			case 'radio':
				if(
					eval("return isset($request);") &&
					eval("return $request;") == $html->value
				) {
					$html->checked = true;
				} else {
					$html->checked = false;
				}
			break;
			case 'checkbox':
				$checked = false;
				if(eval("return isset($request);")) {
					if(is_string(eval("return $request;"))) {
						if(eval("return $request;") != '') {
							$checked = true;
						}
					}
					if(is_array(eval("return $request;"))) {
						if(in_array($html->value, eval("return $request;"))) {
							$checked = true;
						}
					}
					$html->checked = $checked;
				}				
			break;
			case 'text':
			case 'hidden':
			case 'password':
				if(eval("return isset($request);")) {
					$html->value = str_replace('"', '&quot;', eval("return $request;"));
				} else {
					$html->value = '';
				}
			break;
		}
		return $html;

	}

	//---------------------------------------
	/**
	 * handle htmlobject_select
	 *
	 * @access protected
	 * @param object $html
	 * @param string $request
	 * @return object
	 */
	//---------------------------------------
	function handle_htmlobject_select($html, $request) {

		if(eval("return isset($request);")) {
			if(is_array(eval("return $request;"))) {
				$html->selected = eval("return $request;");
			} else {
				$html->selected = array(eval("return $request;"));
			}
		}
		return $html;

	}

	//---------------------------------------
	/**
	 * handle htmlobject_textarea
	 *
	 * @access protected
	 * @param object $html
	 * @param string $request
	 * @return object
	 */
	//---------------------------------------
	function handle_htmlobject_textarea($html, $request) {

		if(eval("return isset($request);")) {
			$html->text = str_replace('<', '&lt;', eval("return $request;"));
		} else {
			$html->text = '';
		}
		return $html;

	}

	//---------------------------------------
	/**
	 * handle label
	 *
	 * @access protected
	 * @param array $data
	 * @return string
	 */
	//---------------------------------------
	function get_label($data) {

		$label = '';
		if(
			isset($data['label']) && $data['label'] != '' &&
			isset($data['object']['attrib']['name'])
		) {
			$label = $data['label'];
			$name  = $data['object']['attrib']['name'];
			// mark error
			if($this->check_request) {
				if(array_key_exists($name, $this->check_request)) {
					$label = '<span class="error">'.$label.'</span>';
				}
			}
			// mark required
			if(isset($data['required']) && $data['required'] === true) {
				$label = $label.' '.$this->str_required;
			}
		}
		return $label;

	}
	//-------------------------- Helpers

	//---------------------------------------
	/**
	 * get tab_request as html inputs
	 *
	 * @access public
	 * @param array $arValues
	 * @return string
	 */
	//---------------------------------------
	function get_tab_request_as_input($arValues = array()) {

		$strReturn = '';
		$arValues = array_merge($this->tab_request, $arValues);
		foreach ($arValues as $key => $value) {
			$strReturn .= $this->get_input($key, $value, 'hidden');
		}
		return $strReturn;

	}

	//---------------------------------------
	/**
	 * get htmlobject_input as string
	 *
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param enum $type
	 * @return string
	 */
	//---------------------------------------
	function get_input($name, $value, $type = 'hidden') {

		$value = str_replace('"', '&quot;', $value);
		$value = str_replace('<', '&lt;', $value);

		$html = $this->html->input();
		$html->name = $name;
		$html->value = $value;
		$html->type = $type;

		return $html->get_string();
	}

	//---------------------------------------
	/**
	 * get htmlobject_button as string
	 *
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @return string
	 */
	//---------------------------------------
	function get_button($name, $value, $label) {

		$html = $this->html->button();
		$html->name = $name;
		$html->value = $value;
		$html->type = 'submit';
		$html->label = $label;

		return $html->get_string();
	}


	//---------------------------------------
	/**
	 * get formbuilder as string
	 *
	 * @access public
	 * @return string
	 */
	//---------------------------------------
	function get_string( $name = null ) {
	$str  = '';
		if( $name ) {
			$data = $data = $this->get_template_array( $name );
		} else {
			$data = $data = $this->get_template_array();
		}


		foreach( $data as $key => $value) {
			if( $name ) {
				if( $key === $name ) {
					$str .= $value;
				}				
			} else {
				$str .= $value;
			}
		}
	return $str;
	}


} // end class
?>
