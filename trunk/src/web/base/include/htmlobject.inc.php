<?php
$ClassDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/class/';

require_once($ClassDir.'PHPLIB.php');
require_once($ClassDir.'htmlobject.class.php');
require_once($ClassDir.'htmlobject.box.class.php');
require_once($ClassDir.'htmlobject.table.class.php');
require_once($ClassDir.'htmlobject.head.class.php');
require_once($ClassDir.'htmlobject.tabs.class.php');
require_once($ClassDir.'htmlobject.preloader.class.php');

//---------------------------------------------------------------
/**
* builds html input box
* @access public
* @param  $name string
* @param  $value array(array(label=>, value=>))
* @param  $type enum(text,hidden,password,checkbox)
* @param  $maxlength int
* @return string
*/
function htmlobject_input($name, $value, $type = 'text', $arg = '') {

	$html = new htmlobject_input();
	$html->name = $name;
	$html->id = 'p'.uniqid();
	$html->value = $value['value'];
	$html->title = $value['label'];
	$html->type = $type;
	
	switch($type) {
		case 'text':
		case 'password':
			$html->maxlength = $arg;		
			return htmlobject_box_from_object($html, ' input');
			break;
		case 'checkbox':
			$html->value = '';
			$html->checked = $arg;		
			return htmlobject_box_from_object($html, ' checkbox');
			break;
		case 'hidden':
			$html->title = '';
			$html->id = '';
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
		$html->id = 'p'.uniqid();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;

		return htmlobject_box_from_object($html, ' select');
}
//---------------------------------------------------------------
/**
* builds html select
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @param  $title string
* @param  $selected array()
* @return string
*/
function htmlobject_select_simple($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
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
		$html->id = 'p'.uniqid();
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

	$tabmenu = new htmlobject_tabmenu($value);
	$tabmenu->css = 'htmlobject_tabs';

	/*
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
*/

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
function htmlobject_box_from_object($html, $css='', $usetitle = true) {

	$box = new htmlobject_box();
	$box->id = 'htmlobject_box_'. $html->name;
	$box->css = 'htmlobject_box'.$css;
	$box->label = $html->title;
	$box->content = $html;
	
	if($usetitle === false) {
		$html->title = '';
	}
	
	return $box->get_string();
}
//---------------------------------------------------------------
/**
* builds html radio box
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @param  $checked string
* @return string
*/
function htmlobject_radio_list($name, $value, $title = '', $checked = '') {
	$_strReturn = '';

	$i = 0;
	foreach ($value as $val) {
		$html = new htmlobject_input();
		$html->type = 'radio';
		$html->id = 'p'.uniqid();
		$html->name = $name;
		$html->title =  $val['label'];
		if($checked == $val['value']) {
			$html->checked = true;
		}
		$html->value = $val['value'];
		$_strReturn .= htmlobject_box_from_object($html, ' radio');
		
		$i++;
	}
	
	$html = new htmlobject_div();
	$html->name = '';
	$html->title = $title;
	$html->text = $_strReturn;

	$_strReturn = htmlobject_box_from_object($html, ' outerbox', false);	
	
	return $_strReturn;
}
//---------------------------------------------------------------
/**
* builds html table
* @access public
* @param  $name string
* @param  $value array(array(label=>, value=>, ...)
* @param  $mode enum(object,string)
* @return string
*/
function htmlobject_table($id, $value, $mode = 'object') {
	$_strReturn = '';
	
	$table = new htmlobject_table();
	$table->id = $id;
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 0;

	$i = 0;
	if($mode == 'object') {
		foreach ($value as $val) {	
		$tr = new htmlobject_tr();
			foreach($val as $key => $v) {
				$td = new htmlobject_td();
				$td->css = $key;
				$td->text = $v;
				$tr->add($td);
			}
		$table->add($tr);
		}
	}
	if($mode == 'string') {
		$tr = '';
			foreach ($value as $val) {
				$tr .= $val;
			}
		$table->add($tr);
	}
	
	return $table->get_string();
}
//---------------------------------------------------------------
/**
* builds head of Page
* @access public
* @param $title string
* @return string
*/
function htmlobject_head($title = '', $timer = '', $url = '') {

	$html = new htmlobject_head();
	$html->add_meta('content-language','en');
	$html->add_meta('content-type','text/html; charset=utf-8');
	$html->add_meta('expires','Sat, 01 Dec 2001 00:00:00 GMT');
	$html->add_meta('cache-control','no-cache');
	$html->add_meta('pragma','no-cache');
	if($timer != '' && $url != '') {
		$html->add_meta('refresh', $timer.'; URL='.$url);	
	}
	$html->add_style('../../css/htmlobject.css');
	$html->title = $title;
	
	return $html->get_string();
}
//---------------------------------------------------------------
function htmlobject_request($arg) 
{
	if (isset($_REQUEST[$arg])) 
			return $_REQUEST[$arg];	
	else
			return '';
}

function error_redirect($strMsg = '') {
global $thisfile;
	$args = '?strMsg=<strong>Error:</strong><br>'.$strMsg;
	foreach($_POST as $key => $value) {
	if($key != 'action') {
		
		if(is_array($value)) {
			foreach($value as $key1 => $value1) {
				$args .= '&'.$key.'[]='.$value1;
			}
		} else {
			$args .= '&'.$key.'='.$value;
		}
	}
	}
	foreach($_GET as $key => $value) {
	if($key != 'action') {
		
		if(is_array($value)) {
			foreach($value as $key1 => $value1) {
				$args .= '&'.$key.'[]='.$value1;
			}
		} else {
			$args .= '&'.$key.'='.$value;
		}
	}
	}
	return $thisfile.$args;
}
?>