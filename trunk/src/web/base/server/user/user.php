<?php
error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
require_once('../../include/htmlobject.inc.php');
require_once('../../include/user.inc.php');

echo $OPENQRM_USER->role;

if(htmlobject_request('action') != '') {
	require_once('action.inc.php');
}

$user = new user($_SERVER['PHP_AUTH_USER']);
$user->set_user_form();

function html_elements() {
global $user;

$GLOBALS['html_id'] = htmlobject_input('id', $user->id, 'hidden', 5);
$GLOBALS['html_name'] = htmlobject_input('name', $user->name, 'text', 20);
$GLOBALS['html_password'] = htmlobject_input('password', $user->password, 'text', 20);
$GLOBALS['html_gender'] = htmlobject_select('gender', $user->get_gender_list(), $user->gender['label'], array($user->gender['value']));
$GLOBALS['html_first_name'] = htmlobject_input('first_name', $user->first_name, 'text', 50);
$GLOBALS['html_last_name'] = htmlobject_input('last_name', $user->last_name, 'text', 50);
$GLOBALS['html_department'] = htmlobject_input('department', $user->department, 'text', 50);
$GLOBALS['html_office'] = htmlobject_input('office', $user->office, 'text', 50);
$GLOBALS['html_role'] = htmlobject_select('role', $user->get_role_list(), $user->role['label'], array($user->role['value']));
$GLOBALS['html_last_update_time'] = htmlobject_input('last_update_time', $user->last_update_time, 'text', 50);
$GLOBALS['html_description'] = htmlobject_textarea('description', $user->description);
$GLOBALS['html_capabilities'] = htmlobject_textarea('capabilities', $user->capabilities);
$GLOBALS['html_state'] = htmlobject_input('state', $user->state, 'text', 20);

}


html_elements();

$account_output = "
<form action=\"$thisfile\" method=\"post\">
<input type=\"hidden\" name=\"currenttab\" value=\"tab0\">
<input type=\"hidden\" name=\"action\" value=\"user_update\">
$html_id
$html_name
$html_role
$html_first_name
$html_last_name
$html_gender
$html_department
$html_office
$html_state
$html_last_update_time
$html_description
$html_capabilities
<input type=\"submit\">
</form>
";

$user->id['value'] = '';
$user->name['value'] = '';
$user->password['value'] = '';
$user->gender['value'] = '';
$user->role['value'] = '';
$user->first_name['value'] = '';
$user->last_name['value'] = '';
$user->department['value'] = '';
$user->office['value'] = '';
$user->last_update_time['value'] = '';
$user->description['value'] = '';
$user->capabilities['value'] = '';
$user->state['value'] = '';

html_elements();

$add_user_output = "
<form action=\"$thisfile\" method=\"post\">
<input type=\"hidden\" name=\"currenttab\" value=\"tab2\">
<input type=\"hidden\" name=\"action\" value=\"user_insert\">
$html_id
$html_name
$html_password
$html_role
$html_first_name
$html_last_name
$html_gender
$html_department
$html_office
$html_state
$html_description
$html_capabilities
<input type=\"submit\">
</form>
";

$ar_edit = array();
$ar_users = $user->get_users();
foreach ($ar_users as $ar) {
$tmp = '';

	foreach ($ar as $val) {
	
		$html = new htmlobject_div();
		$html->style = 'float:left;';
		$html->css = $val['label'] .' div_td';
		
		$text = $val['value'];
		if($text == '') { $text = '&#160;'; }
		
		$html->text = $text;
		$tmp .= $html->get_string();
		
	}
	$html = new htmlobject_div();
	$html->css = 'div_tr';
	$html->handler = '
		onmouseover="this.style.backgroundColor = \'aqua\';"
		onmouseout="this.style.backgroundColor = \'transparent\'";
		onclick="location.href=\''.$thisfile.'?currenttab=tab0&name='.$ar[0]['value'].'\'";
	';
	$html->text = $tmp .'<div class="floatbreaker">&#160;</div>';
	$ar_edit[] = $html->get_string();	
}

$edit_user_output = "
<form action=\"$thisfile\" method=\"post\">
<input type=\"hidden\" name=\"currenttab\" value=\"tab1\">
<input type=\"hidden\" name=\"action\" value=\"user_edit\">
";
foreach ( $ar_edit as $res ) {
$edit_user_output .= $res;
}

$edit_user_output .= "
</form>
";

$output = array();
$output[] = array('label' => 'Account', 'value' => $account_output);
$output[] = array('label' => 'Edit User', 'value' => $edit_user_output);
$output[] = array('label' => 'Add User', 'value' => $add_user_output);

?>

<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.user_name { width:74px; }
.user_id { width:40px; }
.user_first_name { width:90px; }
.user_last_name { width:90px; }
.user_role { width:50px; }
.user_last_update_time { width:80px; }

.div_tr {
cursor:pointer; 
border-left: 1px solid;
}
.div_td {
padding:5px !important;
border-bottom: 1px solid;
border-right: 1px solid;
}
</style>

<?php

echo htmlobject_tabmenu($output);

?>



