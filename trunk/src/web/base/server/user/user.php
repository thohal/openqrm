<?php
require_once('../../include/htmlobject.inc.php');
require_once('../../include/user.inc.php');


$user = new user();
$user->get_user_data(OPENQRM_USER_NAME);

$html_id = htmlobject_input('id', $user->id, 'text', 5);
$html_name = htmlobject_input('name', $user->name, 'text', 20);
$html_password = htmlobject_input('password', $user->password, 'password', 20);
$html_gender = htmlobject_select('gender', $user->get_gender_list(), $user->gender['label'], array($user->gender['value']));
$html_first_name = htmlobject_input('first_name', $user->first_name, 'text', 50);
$html_last_name = htmlobject_input('last_name', $user->last_name, 'text', 50);
$html_department = htmlobject_input('department', $user->department, 'text', 50);
$html_office = htmlobject_input('office', $user->office, 'text', 50);
$html_role = htmlobject_select('role', $user->get_role_list(), $user->role['label'], array($user->role['value']));
#$html_role = htmlobject_input('role', $user->role);
$html_last_update_time = htmlobject_input('last_update_time', $user->last_update_time, 'text', 50);
$html_description = htmlobject_textarea('description', $user->description);
$html_capabilities = htmlobject_textarea('capabilities', $user->capabilities);
$html_state = htmlobject_input('state', $user->state, 'text', 20);




$account_output = "
$html_id
$html_name
$html_gender
$html_role
$html_first_name
$html_last_name
$html_department
$html_office
$html_state
$html_description
$html_capabilities
";

$output = array();
$output[] = array('label' => 'Account', 'value' => $account_output);
$output[] = array('label' => 'User', 'value' => 'test');

?>

<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

echo htmlobject_tabmenu($output);

?>



