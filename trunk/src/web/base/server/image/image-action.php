<html>
<head>
<title>openQRM Image actions</title>
<meta http-equiv="refresh" content="3; URL=image-overview.php">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
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

$image_command = $_REQUEST["image_command"];
$image_id = $_REQUEST["image_id"];
$image_name = $_REQUEST["image_name"];
$image_type = $_REQUEST["image_type"];
$image_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "image_", 5) == 0) {
		$image_fields[$key] = $value;
	}
}
unset($image_fields["image_command"]);

$deployment_id = $_REQUEST["deployment_id"];
$deployment_name = $_REQUEST["deployment_name"];
$deployment_type = $_REQUEST["deployment_type"];
$deployment_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "deployment_", 10) == 0) {
		$deployment_fields[$key] = $value;
	}
}


	switch ($image_command) {
		case 'new_image':
			$image = new image();
			$image_fields["image_id"]=openqrm_db_get_free_id('image_id', $IMAGE_INFO_TABLE);
			# switch deployment_id to deyployment_type
			$deployment_switch = new deployment();
			$deployment_switch->get_instance_by_id($image_type);
			$image_fields["image_type"] = $deployment_switch->type;
			$image->add($image_fields);
			echo "Added image $image_name/$image_version to the openQRM-database";
			break;

		case 'remove':
			$image = new image();
			$image->remove($image_id);
			echo "Removed image $image_id from the openQRM-database";
			break;

		case 'remove_by_name':
			$image = new image();
			$image->remove_by_name($image_name);
			echo "Removed image $image_name from the openQRM-database";
			break;

		case 'add_deployment_type':
			$deployment = new deployment();
			$deployment_fields["deployment_id"]=openqrm_db_get_free_id('deployment_id', $DEPLOYMENT_INFO_TABLE);
			$deployment->add($deployment_fields);
			echo "Added deployment type $deployment_name to the openQRM-database";
			break;

		case 'remove_deployment_type':
			$deployment = new deployment();
			$deployment->remove_by_type($deployment_type);
			echo "Removed deployment type $deployment_type from the openQRM-database";
			break;

		default:
			echo "No Such openQRM-command!";
			break;


	}
?>

</body>
