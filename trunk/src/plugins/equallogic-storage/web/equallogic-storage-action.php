<?php
$equallogic_storage_command = $_REQUEST["equallogic_storage_command"];

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$EquallogicDir = $_SERVER["DOCUMENT_ROOT"].'/equallogic-storage-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special equallogic-storage classes
require_once "$RootDir/plugins/equallogic-storage/class/equallogic-storage-server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "equallogic-storage-action", "Un-Authorized access to equallogic-storage-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}



// main
$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-storage-action", "Processing equallogic-storage command $equallogic_storage_command", "", "", 0, 0, 0);

	switch ($equallogic_storage_command) {

		case 'init':
			// this command creates the following tables
			// -> equallogic_storage_servers
			// eq_id INT(5)
			// eq_storage_id INT(5)
			// eq_storage_name VARCHAR(20)
			// eq_storage_user VARCHAR(20)
			// eq_storage_password VARCHAR(20)
			// eq_storage_comment VARCHAR(50)
			//
			$create_equallogic_storage_config = "create table equallogic_storage_servers(eq_id INT(5), eq_storage_id INT(5), eq_storage_name VARCHAR(20), eq_storage_user VARCHAR(20), eq_storage_password VARCHAR(20), eq_storage_comment VARCHAR(50))";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($create_equallogic_storage_config);
			$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-storage-action", "Initialyzed Eqallogic-storage Server table", "", "", 0, 0, 0);
		    $db->Close();
			break;

		case 'uninstall':
			$drop_equallogic_storage_config = "drop table equallogic_storage_servers";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($drop_equallogic_storage_config);
			$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-storage-action", "Uninstalled Eqallogic-storage Server table", "", "", 0, 0, 0);
		    $db->Close();
			break;

		case 'get_ident':
			if (!file_exists($StorageDir)) {
				mkdir($StorageDir);
			}
			break;


		default:
			$event->log("$equallogic_storage_command", $_SERVER['REQUEST_TIME'], 3, "equallogic-storage-action", "No such event command ($equallogic_storage_command)", "", "", 0, 0, 0);
			break;


	}






?>
