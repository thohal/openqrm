
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
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";

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
				$openqrm_server_command="htpasswd -D $CloudDir/user/.htpasswd $username";
				$output = shell_exec($openqrm_server_command);
				// remove permissions and limits
				$cloud_user_limit = new clouduserlimits();
				$cloud_user_limit->remove_by_cu_id($id);
				// remove from db
				$cl_user->remove($id);
			}
			break;

		case 'enable':
			foreach($_REQUEST['identifier'] as $id) {
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($id);
				$cl_user->activate_user_status($id, 1);
			}
			break;

		case 'disable':
			foreach($_REQUEST['identifier'] as $id) {
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($id);
				$cl_user->activate_user_status($id, 0);
			}
			break;

		case 'update':
			foreach($_REQUEST['identifier'] as $id) {
				$up_ccunits = $_REQUEST['cu_ccunits'];
				$cl_user = new clouduser();
				$cl_user->get_instance_by_id($id);
				$cl_user->set_users_ccunits($id, $up_ccunits[$id]);
			}
			break;

		case 'limit':
			// gather user_limits parameter in array
			foreach ($_REQUEST as $key => $value) {
				if (strncmp($key, "cl_", 3) == 0) {
					$user_limits_fields[$key] = $value;
				}
			}
			$cloud_user_id = $_REQUEST['cl_cu_id'];
			$cloud_user_limit = new clouduserlimits();
			$cloud_user_limit->get_instance_by_cu_id($cloud_user_id);
			$cl_id = $cloud_user_limit->id;
			$cloud_user_limit->update($cl_id, $user_limits_fields);
			echo "Updated limits for Cloud user $cloud_user_id<br>";
			break;

	}
}




function cloud_user_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	$table = new htmlobject_db_table('cu_id');

	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

	$disp = "<h1>Cloud User Manager for portal at <a href=\"$external_portal_name\">$external_portal_name</a></h1>";
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

	$arHead['cu_ccunits'] = array();
	$arHead['cu_ccunits']['title'] ='CC-Units';

	$arHead['cu_status'] = array();
	$arHead['cu_status']['title'] ='Status';

	$arBody = array();

	// db select
    $cl_user_count = 0;
	$cl_user = new clouduser();
	$user_array = $cl_user->display_overview($table->offset, $table->limit, 'cu_id', 'ASC');
	foreach ($user_array as $index => $cu) {
		$cu_status = $cu["cu_status"];
		if ($cu_status == 1) {
			$status_icon = "<img src=\"/cloud-portal/img/active.png\">";
		} else {
			$status_icon = "<img src=\"/cloud-portal/img/inactive.png\">";
		}
		// set the ccunits input
		$ccunits = $cu["cu_ccunits"];
		if (!strlen($ccunits)) {
			$ccunits = 0;
		}
		$cu_id = $cu["cu_id"];
		$ccunits_input = "<input type=\"text\" name=\"cu_ccunits[$cu_id]\" value=\"$ccunits\" size=\"5\ maxsize=\"10\">";
		
		$arBody[] = array(
			'cu_id' => $cu["cu_id"],
			'cu_name' => $cu["cu_name"],
			'cu_password' => $cu["cu_password"],
			'cu_forename' => $cu["cu_forename"],
			'cu_lastname' => $cu["cu_lastname"],
			'cu_email' => $cu["cu_email"],
			'cu_ccunits' => $ccunits_input,
			'cu_status' => $status_icon,
		);
        $cl_user_count++;
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
		$table->bottom = array('update', 'enable', 'disable', 'limits', 'delete');
		$table->identifier = 'cu_id';
	}
	$table->max = 1000;
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
	$disp = $disp.htmlobject_input('cu_street', array("value" => '', "label" => 'Street+number'), 'text', 100);
	$disp = $disp.htmlobject_input('cu_city', array("value" => '', "label" => 'City'), 'text', 100);
	$disp = $disp.htmlobject_input('cu_country', array("value" => '', "label" => 'Country'), 'text', 100);
	$disp = $disp.htmlobject_input('cu_phone', array("value" => '', "label" => 'Phone'), 'text', 100);

	$disp = $disp."<input type=hidden name='cloud_command' value='create_user'>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Create'>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."</form>";



	return $disp;
}




function cloud_set_user_limits($cloud_user_id) {

	global $OPENQRM_USER;
	global $thisfile;

	$cloud_user = new clouduser();
	$cloud_user->get_instance_by_id($cloud_user_id);

	$cloud_user_limit = new clouduserlimits();
	$cloud_user_limit->get_instance_by_cu_id($cloud_user_id);
	$resource_limit = $cloud_user_limit->resource_limit;
	$memory_limit = $cloud_user_limit->memory_limit;
	$disk_limit = $cloud_user_limit->disk_limit;
	$cpu_limit = $cloud_user_limit->cpu_limit;
	$network_limit = $cloud_user_limit->network_limit;

	$disp = "<h1>Set Cloud User Limits</h1>";
	$disp = $disp."<br>";
	$disp = $disp."Cloud Limits for User $cloud_user->name  (0 => infinite)";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<form action=$thisfile method=post>";

	$disp = $disp.htmlobject_input('cl_resource_limit', array("value" => $resource_limit, "label" => 'Max Resource'), 'text', 20);
	$disp = $disp.htmlobject_input('cl_memory_limit', array("value" => $memory_limit, "label" => 'Max Memory'), 'text', 20);
	$disp = $disp.htmlobject_input('cl_disk_limit', array("value" => $disk_limit, "label" => 'Max Disk Space'), 'text', 20);
	$disp = $disp.htmlobject_input('cl_cpu_limit', array("value" => $cpu_limit, "label" => 'Max CPU'), 'text', 20);
	$disp = $disp.htmlobject_input('cl_network_limit', array("value" => $network_limit, "label" => 'Max NIC'), 'text', 20);

	$disp = $disp."<input type=hidden name='cl_cu_id' value=$cloud_user_id>";
	$disp = $disp."<input type=hidden name='action' value='limit'>";
	$disp = $disp."<br>";
	$disp = $disp."<input type=submit value='Set-Limits'>";
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
		case 'limits':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Cloud User Limits', 'value' => cloud_set_user_limits($id));
			}
			$output[] = array('label' => 'Cloud User Manager', 'value' => cloud_user_manager());
			break;
		default:
			$output[] = array('label' => 'Cloud User Manager', 'value' => cloud_user_manager());
			break;
	}
} else {
	$output[] = array('label' => 'Cloud User Manager', 'value' => cloud_user_manager());
}
echo htmlobject_tabmenu($output);
?>
