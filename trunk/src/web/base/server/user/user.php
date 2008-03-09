<?php
error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
require_once('../../include/htmlobject.inc.php');
require_once('../../include/user.inc.php');

if(htmlobject_request('action') != '') {
	require_once('action.inc.php');
}

if(strtolower($OPENQRM_USER->role) == 'administrator' && htmlobject_request('name') != '') {
	$user = new user(htmlobject_request('name'));
} else {
	$user = new user($_SERVER['PHP_AUTH_USER']);
}


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

if(htmlobject_request('delete') == 1) {

$account_output = '
<form action="'.$thisfile.'" method="post">
<input type="hidden" name="currenttab" value="tab0">
<input type="hidden" name="action" value="user_delete_2">
<input type="hidden" name="name" value="'.htmlobject_request('name').'">
<center>
<br><br>
Really delete user <strong>'.htmlobject_request('name').'</strong> ?
<br><br>
<br><br>
<input type="submit" value="ok" class="button">
<br><br>
</center>
</form>
';

} else {

html_elements();

$switch = '
<table>
<tr>
<td><label for="action_up">update</label></td>
<td><input type="radio" name="action" id="action_up" value="user_update" checked></td>
<td><label for="action_del">delete</label></td>
<td><input type="radio" name="action" id="action_del" value="user_delete"></td>
<td><input type="submit" class="button"></td>
</tr>
</table>
';

$account_output = "
<form action=\"$thisfile\" method=\"post\">
<input type=\"hidden\" name=\"currenttab\" value=\"tab0\">
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
$html_last_update_time
$html_description
$html_capabilities
$switch

</form>
";

}

$output = array();
$output[] = array('label' => 'Account', 'value' => $account_output);

if(strtolower($OPENQRM_USER->role) == 'administrator') {

	//---------------------------------------------------------
	$ar_edit = array();
	$ar_users = $user->get_users();

	foreach ($ar_users as $ar) {
	$tmp = '';
		foreach ($ar as $val) {
			$text = $val['value'];
			if($text == '') { $text = '&#160;'; }
			$tmp .= '<td class="'. $val['label'] .' div_td">'.$text.'</td>';
		}
		$ar_edit[] = '
			<tr class="div_tr"
				onmouseover="this.style.backgroundColor = \'#eeeeee\';"
				onmouseout="this.style.backgroundColor = \'transparent\'";
				onclick="location.href=\''.$thisfile.'?currenttab=tab0&name='.$ar[0]['value'].'\';">
		';
		$ar_edit[] = $tmp;
		$ar_edit[] = '</tr>';
	}

	$edit_user_output = "
	<form action=\"$thisfile\" method=\"post\">
	<input type=\"hidden\" name=\"currenttab\" value=\"tab1\">
	<input type=\"hidden\" name=\"action\" value=\"user_edit\">
	<table cellpadding=\"0\" cellspacing=\"0\">
	";
	
	foreach ( $ar_edit as $res ) {
		$edit_user_output .= ''.$res.'';
	}
	
	$edit_user_output .= "
	</table>
	</form>
	";
	$output[] = array('label' => 'Edit User', 'value' => $edit_user_output);

	//---------------------------------------------------------
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
	<input type=\"submit\" class=\"button\">
	</form>
	";
	$output[] = array('label' => 'Add User', 'value' => $add_user_output);
}

echo htmlobject_head('User Administration');

?>
<body>
<style>
.user_name { width:74px; }
.user_id { width:40px; }
.user_first_name { width:90px; }
.user_last_name { width:90px; }
.user_role { width:50px; }
.user_last_update_time { width:80px; }
</style>

<?php
echo htmlobject_tabmenu($output);
?>

</body>
</html>



