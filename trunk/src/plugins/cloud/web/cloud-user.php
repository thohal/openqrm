
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special clouduser class
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
			foreach($_REQUEST['identifier'] as $id) {
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($id);
				// remove user from htpasswd
				$username = $cl_user->name;
				$openqrm_server_command="htpasswd -D $CloudDir/.htpasswd $username";
				$output = shell_exec($openqrm_server_command);
				// remove from db
				$cl_user->remove($id);


			}
			break;
	}
}




function cloud_user_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	$table = new htmlobject_db_table('cu_id');


	$disp = "<h1>Cloud User Manager for portal at <a href=\"http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal\">http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal</a></h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b><a href=\"$thisfile?action=create\">Create new Cloud User</a></b>";
	$disp = $disp."<br>";
	$arHead = array();

	$arHead['cu_id'] = array();
	$arHead['cu_id']['title'] ='ID';

	$arHead['cu_name'] = array();
	$arHead['cu_name']['title'] ='Name';

	$arHead['cu_password'] = array();
	$arHead['cu_password']['title'] ='Password';

	$arHead['cu_fore_name'] = array();
	$arHead['cu_fore_name']['title'] ='Fore name';

	$arHead['cu_last_name'] = array();
	$arHead['cu_last_name']['title'] ='Last name';

	$arHead['cu_email'] = array();
	$arHead['cu_email']['title'] ='Email';

	$arBody = array();

	// db select
	$cl_user = new clouduser();
	$user_array = $cl_user->display_overview(0, 100, 'cu_id', 'ASC');
	foreach ($user_array as $index => $cu) {
		$arBody[] = array(
			'cu_id' => $cu["cu_id"],
			'cu_name' => $cu["cu_name"],
			'cu_password' => $cu["cu_password"],
			'cu_forename' => $cu["cu_forename"],
			'cu_lastname' => $cu["cu_lastname"],
			'cu_email' => $cu["cu_email"],
		);
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('delete');
		$table->identifier = 'cu_id';
	}
	$table->max = 100;
	return $disp.$table->get_string();
}


function cloud_create_user() {

	global $OPENQRM_USER;
	global $thisfile;


	$disp = "<h1>Create new Cloud User</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<form action='cloud-action.php' method=post>";
	$disp = $disp.htmlobject_input('cu_name', array("value" => '', "label" => 'User name'), 'text', 20);
	$disp = $disp.htmlobject_input('cu_password', array("value" => '', "label" => 'Password'), 'text', 20);
	$disp = $disp.htmlobject_input('cu_forename', array("value" => '', "label" => 'Fore name'), 'text', 50);
	$disp = $disp.htmlobject_input('cu_lastname', array("value" => '', "label" => 'Last name'), 'text', 50);
	$disp = $disp.htmlobject_input('cu_email', array("value" => '', "label" => 'Email'), 'text', 50);

	$disp = $disp."<input type=hidden name='cloud_command' value='create_user'>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Create'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";



	return $disp;
}




$output = array();


if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'create':
			$output[] = array('label' => 'Create Cloud User', 'value' => cloud_create_user());
			break;
		default:
			$output[] = array('label' => 'Cloud Manager', 'value' => cloud_user_manager());
			break;
	}
} else {
	$output[] = array('label' => 'Cloud Manager', 'value' => cloud_user_manager());
}
echo htmlobject_tabmenu($output);
?>
