<?php
$storage_command = $_REQUEST["storage_command"];
$storage_name = $_REQUEST["storage_name"];
?>

<html>
<head>
<title>openQRM Storage actions</title>
<meta http-equiv="refresh" content="0; URL=storage-overview.php?currenttab=tab1&strMsg=Processing <?php echo $storage_command; ?> on <?php echo $storage_name; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to storage-actions from $OPENQRM_USER->name!");
	exit();
}

$storage_id = $_REQUEST["storage_id"];
$storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "storage_", 8) == 0) {
		$storage_fields[$key] = $value;
	}
}
unset($storage_fields["storage_command"]);

$deployment_id = $_REQUEST["deployment_id"];
$deployment_name = $_REQUEST["deployment_name"];
$deployment_type = $_REQUEST["deployment_type"];
$deployment_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "deployment_", 10) == 0) {
		$deployment_fields[$key] = $value;
	}
}


	syslog(LOG_NOTICE, "openQRM-engine: Processing command $storage_command on storage $storage_name");

	switch ($storage_command) {
		case 'new_storage':
			$storage = new storage();
			$storage_fields["storage_id"]=openqrm_db_get_free_id('storage_id', $STORAGE_INFO_TABLE);
			$storage->add($storage_fields);
			break;

		case 'remove':
			$storage = new storage();
			$storage->remove($storage_id);
			break;

		case 'remove_by_name':
			$storage = new storage();
			$storage->remove_by_name($storage_name);
			break;



	}
?>

</body>
