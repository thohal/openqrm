<html>
<head>
<title>openQRM Storage actions</title>
<meta http-equiv="refresh" content="3; URL=storage-overview.php">
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
$user = new user($_SERVER['PHP_AUTH_USER']);
$user->set_user();
if ($user->role != "administrator") {
	exit();
}

$storage_command = $_REQUEST["storage_command"];
$storage_id = $_REQUEST["storage_id"];
$storage_name = $_REQUEST["storage_name"];
$storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "storage_", 5) == 0) {
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


	switch ($storage_command) {
		case 'new_storage':
			$storage = new storage();
			$storage_fields["storage_id"]=openqrm_db_get_free_id('storage_id', $STORAGE_INFO_TABLE);
			$storage->add($storage_fields);
			echo "Added storage $storage_name/$storage_version to the openQRM-database";
			break;

		case 'remove':
			$storage = new storage();
			$storage->remove($storage_id);
			echo "Removed storage $storage_id from the openQRM-database";
			break;

		case 'remove_by_name':
			$storage = new storage();
			$storage->remove_by_name($storage_name);
			echo "Removed storage $storage_name from the openQRM-database";
			break;



	}
?>

</body>