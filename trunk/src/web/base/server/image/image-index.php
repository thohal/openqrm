<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	header("Location: $url");
	exit;
}


if(htmlobject_request('action') != '') {
$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'remove':
			if(strtolower(OPENQRM_USER_ROLE_NAME) == 'administrator') {
				$image = new image();
				foreach($_REQUEST['identifier'] as $id) {
					$strMsg .= $image->remove($id);
				}
				redirect($strMsg);
			}
		break;
	}

}

// we need to include the resource.class after the redirect to not send any header
require_once "$RootDir/class/resource.class.php";


function image_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$image_tmp = new image();
	$table = new htmlobject_db_table('image_id');

	$disp = '<h1>Image List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['image_icon'] = array();
	$arHead['image_icon']['title'] ='';
	$arHead['image_icon']['sortable'] = false;

	$arHead['image_id'] = array();
	$arHead['image_id']['title'] ='ID';

	$arHead['image_name'] = array();
	$arHead['image_name']['title'] ='Name';

	$arHead['image_version'] = array();
	$arHead['image_version']['title'] ='Version';

	$arHead['image_type'] = array();
	$arHead['image_type']['title'] ='Deployment Type';

	$arHead['image_comment'] = array();
	$arHead['image_comment']['title'] ='Comment';
	$arHead['image_comment']['sortable'] = false;

	$arHead['image_capabilities'] = array();
	$arHead['image_capabilities']['title'] ='Capabilities';
	$arHead['image_capabilities']['sortable'] = false;

	$arHead['image_edit'] = array();
	$arHead['image_edit']['title'] ='';
	$arHead['image_edit']['sortable'] = false;
	if(strtolower(OPENQRM_USER_ROLE_NAME) != 'administrator') {
		$arHead['image_edit']['hidden'] = true;
	}

	$arBody = array();
	$image_array = $image_tmp->display_overview(1, $table->limit, $table->sort, $table->order);
	$image_icon = "/openqrm/base/img/image.png";

	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_db["image_type"]);

		$strEdit = '';
		if($image_db["image_id"] != 1) {
			$strEdit = '<a href="image-edit.php?image_id='.$image_db["image_id"].'&currenttab=tab2">edit</a>';
		}

		$arBody[] = array(
			'image_icon' => "<img width=20 height=20 src=$image_icon>",
			'image_id' => $image_db["image_id"],
			'image_name' => $image_db["image_name"],
			'image_version' => $image_db["image_version"],
			'image_type' => $image_deployment->description,
			'image_comment' => $image_db["image_comment"],
			'image_capabilities' => $image_db["image_capabilities"],
			'image_edit' => $strEdit,
		);

	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('remove');
		$table->identifier = 'image_id';
		$table->identifier_disabled = array(1);
	}
	$table->max = count($image_array);
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}


$output = array();
$output[] = array('label' => 'Image List', 'value' => image_display());
if(strtolower(OPENQRM_USER_ROLE_NAME) == 'administrator') {
	$output[] = array('label' => 'New Image', 'target' => 'image-new.php');
}


?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="image.css" />
<?php
echo htmlobject_tabmenu($output);
?>

