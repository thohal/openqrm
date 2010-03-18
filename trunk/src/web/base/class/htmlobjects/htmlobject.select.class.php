<?php
/**
 * @package htmlobjects
 *
 */

/**
 * Select
 *
 * @package htmlobjects
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */

class htmlobject_select extends htmlobject_base
{
/**
* disable select
* @access public
* @var bool
*/
var $disabled = false;
/**
* allow multiple selection
* @access public
* @var bool
*/
var $multiple = false;
/**
* Attribute name (should the the same as Attribute id)
* @access public
* @var string
*/
var $name = '';
/**
* number of lines to be shown
* @access public
* @var int
*/
var $size = '';
/**
* Attribute tabindex
* @access public
* @var int
*/
var $tabindex = '';
/**
* content of option element (text)
* @access public
* @var array
*/
var $text = array();
/**
* index of array text
* @access public
* @var array
*/
var $text_index = array(
"value" => 'id',
"text" => 'name'
);
/**
* content of option element (value)
* @access public
* @var array
*/
var $selected = array();
/**
* selected  by text or value
*
* true = selected by text
* false  = selected by values
* @access public
* @var bool
*/
var $selected_by_text = false;

	/**
	 * init attribs
	 *
	 * @access protected
	 */
	function get_attribs() {
		$str = parent::get_attribs();
		if ($this->disabled === true)	{ $str .= ' disabled="disabled"'; }
		if ($this->multiple === true)	{ $str .= ' multiple="multiple"'; }
		if ($this->name != '')  		{ $str .= ' name="'.$this->name.'"'; }
		if ($this->size != '')			{ $str .= ' size="'.$this->size.'"'; }
		if ($this->tabindex != '')  	{ $str .= ' tabindex="'.$this->tabindex.'"'; }
		return $str;
	}

	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	function get_string() {
	$_strReturn = '';
		$attribs = $this->get_attribs();
		$_strReturn = "\n<select$attribs>\n";
		$_strReturn .= $this->get_options();
		$_strReturn .= "</select>\n";
	return $_strReturn;
	}

	function get_options() {
	$_strReturn = '';
		$count = count($this->text);
	    if($count > 0){
	        for ($i=0; $i < $count; $i++) {
	        $sel =  "";
	            if($this->selected_by_text === true) {
	        		if(in_array($this->text[$i][$this->text_index['text']], $this->selected)) {
						$sel =  ' selected="selected"';
					}
	        	}
				else {
	        		if(in_array($this->text[$i][$this->text_index['value']], $this->selected)) {
						$sel =  ' selected="selected"';
					}
	        	}
	        $_strReturn .= "<option value=\"".$this->text[$i][$this->text_index['value']]."\"$sel>".$this->text[$i][$this->text_index['text']]."</option>\n";
	        }
	    } else {
	        $_strReturn .= '';
	    }
	return $_strReturn;
	}
}
?>
