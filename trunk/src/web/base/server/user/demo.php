<?php
error_reporting(E_All);
$WebDir = '/openqrm/base/';
$thisfile = basename($_SERVER['PHP_SELF']);

require_once('../../include/htmlobject.inc.php');

//--------------------------------------- Vars

$gender_list = array();
$gender_list[] = array("value"=>"", "label"=>"");
$gender_list[] = array("value" => "f", "label"=>"female");
$gender_list[] = array("value"=>'m', "label"=>'male');
$gender = array('value' => 'f', 'label' => 'Gender');



$id = '1';
$login = array('value' => 'admin', 'label' => 'Login');
$password = array('value' => 'admin', 'label' => 'Password');
$comment = array('value' => 'comment', 'label' => 'Comment');

$radio_list = array();
$radio_list[] = array("value"=>"1", "label"=>"1");
$radio_list[] = array("value" => "2", "label"=>"2");
$radio_list[] = array("value"=>'3', "label"=>'3');
$radio = '2';

//--------------------------------------- Output

	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'tpl.php');
	$t->setVar(array(
		'html_head'			=> htmlobject_head("Demo"),
		'thisfile'			=> $thisfile,
		'html_id'			=> htmlobject_input('id', $id, 'hidden', 5),
		'html_login'		=> htmlobject_input('login', $login, 'text', 20),
		'html_password'		=> htmlobject_input('password', $password, 'password', 20),
		'html_gender'		=> htmlobject_select('gender', $gender_list, $gender['label'], $gender['value'], true),
		'html_comment'		=> htmlobject_textarea('comment', $comment),
		'html_radio'		=> htmlobject_radio_list('radio', $radio_list, 'Radio', $radio),
	));
	
	echo $t->parse('out', 'tplfile');

?>