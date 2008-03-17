<html>
<head>
<title>openQRM Image actions</title>
<?php
	$dep_is_selected = $_REQUEST["dep_is_selected"];
	$image_type = $_REQUEST["image_type"];
	if (!strlen($dep_is_selected)) {
		echo "<meta http-equiv=\"refresh\" content=\"0; URL=image-overview.php?currenttab=tab2&strMsg=Processing image command\">";
	} else {
		echo "<meta http-equiv=\"refresh\" content=\"0; URL=image-overview.php?currenttab=tab1&dep_is_selected=$dep_is_selected&image_type=$image_type&strMsg=Image-type-loaded\">";
		exit();
	}
?>

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
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to image-actions from $OPENQRM_USER->name!");
	exit();
}

$image_command = $_REQUEST["image_command"];
$image_id = $_REQUEST["image_id"];
$image_name = $_REQUEST["image_name"];
$image_type = $_REQUEST["image_type"];
$image_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "image_", 6) == 0) {
		$image_fields[$key] = $value;
	}
}
unset($image_fields["image_command"]);

$deployment_id = $_REQUEST["deployment_id"];
$deployment_name = $_REQUEST["deployment_name"];
$deployment_type = $_REQUEST["deployment_type"];
$deployment_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "deployment_", 11) == 0) {
		$deployment_fields[$key] = $value;
	}
}

	syslog(LOG_NOTICE, "openQRM-engine: Processing command $image_command on Image $image_name");
	switch ($image_command) {
		case 'new_image':
			$image = new image();
			$image_fields["image_id"]=openqrm_db_get_free_id('image_id', $IMAGE_INFO_TABLE);
			# switch deployment_id to deyployment_type
			$deployment_switch = new deployment();
			$deployment_switch->get_instance_by_id($image_type);
			$image_fields["image_type"] = $deployment_switch->type;
			$image->add($image_fields);
			break;

		case 'update_image':
			$image = new image();
			if(!strlen($image_fields["image_isshared"])) {
				$image_fields["image_isshared"]="0";
			}
			$image->update($image_id, $image_fields);
			break;

		case 'remove':
			$image = new image();
			$image->remove($image_id);
			break;

		case 'remove_by_name':
			$image = new image();
			$image->remove_by_name($image_name);
			break;

		case 'add_deployment_type':
			$deployment = new deployment();
			$deployment_fields["deployment_id"]=openqrm_db_get_free_id('deployment_id', $DEPLOYMENT_INFO_TABLE);
			$deployment->add($deployment_fields);
			break;

		case 'remove_deployment_type':
			$deployment = new deployment();
			$deployment->remove_by_type($deployment_type);
			break;

		default:
			echo "No Such openQRM-command!";
			break;


	}
?>

</body>
