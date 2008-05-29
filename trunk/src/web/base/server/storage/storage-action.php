<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/storagetype.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $STORAGETYPE_INFO_TABLE;

$storage_command = htmlobject_request("storage_command");
$event = new event();
$strMsg = '';

// user/role authentication
if (!strstr($OPENQRM_USER->role, "administrator")) {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "storage-action", "Un-Authorized access to storage-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}



$storagetype_id = htmlobject_request("storagetype_id");
$storagetype_name = htmlobject_request("storagetype_name");
$storagetype_description = htmlobject_request("storagetype_description");
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "storagetype_", 12) == 0) {
		$storagetype_fields[$key] = $value;
	}
}


$event->log("$storage_command", $_SERVER['REQUEST_TIME'], 5, "storage-action", "Processing command $storage_command on storage $storage_name", "", "", 0, 0, 0);
switch ($storage_command) {
	case 'add_storagetype_type':
		$storagetype = new storagetype();
		$storagetype_fields["storagetype_id"]=openqrm_db_get_free_id('storagetype_id', $STORAGETYPE_INFO_TABLE);
		$storagetype->add($storagetype_fields);
		$strMsg="Adding storage-type $storagetype_name";
		$event->log("$storage_command", $_SERVER['REQUEST_TIME'], 5, "storage-action", "$strMsg", "", "", 0, 0, 0);
		break;

	case 'remove_storagetype_type':
		$storagetype = new storagetype();
		$storagetype->remove_by_name($storagetype_name);
		$strMsg="Removing storage-type $storagetype_name";
		$event->log("$storage_command", $_SERVER['REQUEST_TIME'], 5, "storage-action", "$strMsg", "", "", 0, 0, 0);
		break;

	default:
		$event->log("$storage_command", $_SERVER['REQUEST_TIME'], 3, "storage-action", "No such event command ($storage_command)", "", "", 0, 0, 0);
		break;
}



?>



<html>
<head>
<title>openQRM Storage actions</title>
<meta http-equiv="refresh" content="0; URL=storage-overview.php?currenttab=tab0&strMsg=<?php echo $strMsg; ?>">
</head>
<body>
</body>
