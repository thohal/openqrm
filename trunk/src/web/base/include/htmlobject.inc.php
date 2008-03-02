<?php
$ClassDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/class/';
require_once($ClassDir.'htmlobject_input.class.php');
require_once($ClassDir.'htmlobject_textarea.class.php');
require_once($ClassDir.'htmlobject_select.class.php');
require_once($ClassDir.'htmlobject_box.class.php');
require_once($ClassDir.'htmlobject_tabmenu.class.php');
require_once($ClassDir.'htmlobject_div.class.php');

//---------------------------------------------------------------
/**
* builds html input box
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @param  $type enum(text,hidden,password)
* @param  $maxlength int
* @return string
*/
function htmlobject_input($name, $value, $type = 'text', $maxlength = '') {

	$html = new htmlobject_input();
	$html->name = $name;
	$html->value = $value['value'];
	$html->title = $value['label'];
	$html->type = $type;
	$html->maxlength = $maxlength;
	switch($type) {
		case 'text':
		case 'password': 
			return htmlobject_box_from_object($html);
			break;
		case 'hidden':
			$html->title = '';
			$html->maxlength = '';
			return $html->get_string();
			break;
	}
}
//---------------------------------------------------------------
/**
* builds html select box
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @param  $title string
* @param  $selected array()
* @return string
*/
function htmlobject_select($name, $value, $title = '', $selected = array()) {

		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;

		return htmlobject_box_from_object($html, ' select');
}
//---------------------------------------------------------------
/**
* builds html textarea box
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @return string
*/
function htmlobject_textarea($name, $value) {

		$html = new htmlobject_textarea();
		$html->name = $name;
		$html->title = $value['label'];
		$html->text = $value['value'];

		return htmlobject_box_from_object($html, ' textarea');
}
//---------------------------------------------------------------
/**
* builds tabmenu
* @access public
* @param  $value array(array((label=>, value=>))
* @return string
*/
function htmlobject_tabmenu($value) {

	$tabmenu = new htmlobject_tabmenu();
	$tabmenu->css = 'htmlobject_tabs';

	$i = 0;
	foreach ($value as $val) {
		$html = new htmlobject_div();
		$html->id = 'tab'.$i;
		$html->css = 'htmlobject_tab_box';
		$html->title = $val['label'];
		$html->text = $val['value'];
		$tabmenu->add($html);
		$i++;
	}

	return $tabmenu->get_string();
}
//---------------------------------------------------------------
/**
* builds htmlbox from object
* object must be htmlobject
* @access public
* @param $html object
* @return string
*/
function htmlobject_box_from_object($html, $css='') {

	$box = new htmlobject_box();
	$box->id = 'htmlobject_box_'. $html->name;
	$box->css = 'htmlobject_box'.$css;
	$box->label = $html->title;
	$box->content = $html;
	
	return $box->get_string();
}
//---------------------------------------------------------------
function htmlobject_request($arg) 
{
	if (isset($_REQUEST[$arg])) 
			return $_REQUEST[$arg];	
	else
			return '';
}
?>