
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="image-shelf.css" />

<?php

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
$refresh_delay=2;
global $OPENQRM_SERVER_BASE_DIR;
// set ip
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();


// actions

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $url) {
				$image_shelf_url = $url;
				$openqrm_server->send_command(" /usr/lib/openqrm/plugins/image-shelf/bin/openqrm-image-shelf list -i $image_shelf_url");
				sleep($refresh_delay);
			}
			break;

		case 'get':
			foreach($_REQUEST['identifier'] as $id) {
				$image_id = $id;
				$image_shelf_url = htmlobject_request('image_shelf_url');
			}
			break;

		case 'put':
			foreach($_REQUEST['identifier'] as $id) {
				$final_image_id = $id;
				$image_id = htmlobject_request('image_id');
				$image_shelf_url = htmlobject_request('image_shelf_url');
			}
			break;


	}
}






function image_shelf_select() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	// main config
	$image_shelf_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/image-shelf/etc/openqrm-plugin-image-shelf.conf";
	$store = openqrm_parse_conf($image_shelf_conf_file);
	extract($store);

	// user config


	$table = new htmlobject_db_table('image_shelf_id');

	$disp = "<h1>Select Image-Shelf</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select an Image-Shelf Host from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();

	$arHead['image_shelf_id'] = array();
	$arHead['image_shelf_id']['title'] ='Id';

	$arHead['image_shelf_name'] = array();
	$arHead['image_shelf_name']['title'] ='Image-Shelf Name';

	$arHead['image_shelf_url'] = array();
	$arHead['image_shelf_url']['title'] ='Image-Shelf Url';

	$image_shelf_count=1;
	$arBody = array();

	$image_shelf_name = dirname($store[OPENQRM_SERVER_IMAGE_SHELF_1]);
	$image_shelf_name = str_replace("http://", "", $image_shelf_name);

	$arBody[] = array(
		'image_shelf_id' => $image_shelf_count,
		'image_shelf_name' => $image_shelf_name,
		'image_shelf_url' => $store[OPENQRM_SERVER_IMAGE_SHELF_1],
	);

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('select');
		$table->identifier = 'image_shelf_url';
	}
	$table->max = $image_shelf_count;
	return $disp.$table->get_string();
}





function image_shelf_display($image_shelf_url) {
	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$image_shelf_name = dirname($image_shelf_url);
	$image_shelf_name = str_replace("http://", "", $image_shelf_name);
	$image_shelf_conf = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/image-shelf/web/image-lists/$image_shelf_name/image-shelf.conf";

	$table = new htmlobject_db_table('image_id');

	$disp = "<h1>Select Image</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();

	$arHead['image_id'] = array();
	$arHead['image_id']['title'] ='Id';

	$arHead['image_version'] = array();
	$arHead['image_version']['title'] ='Distribution';

	$arHead['image_application'] = array();
	$arHead['image_application']['title'] ='Application';

	$arHead['image_filename'] = array();
	$arHead['image_filename']['title'] ='Filename';

	$arHead['image_size'] = array();
	$arHead['image_size']['title'] ='Size';

	$arHead['image_root_password'] = array();
	$arHead['image_root_password']['title'] ='Password';

	$arHead['image_maintainer'] = array();
	$arHead['image_maintainer']['title'] ='Maintainer';

	$image_count=1;
	$arBody = array();

	if (file_exists($image_shelf_conf)) {
		$image_shelf_conf_content=file($image_shelf_conf);

		foreach ($image_shelf_conf_content as $value => $image) {
			$image_parameter = explode("|", $image);
			$image_filename = $image_parameter[0];
			$image_distribution = $image_parameter[1];
			$image_version = $image_parameter[2];
			$image_application = $image_parameter[3];
			$image_size = $image_parameter[4];
			$image_root_password = $image_parameter[5];
			$image_maintainer = $image_parameter[6];

			$arBody[] = array(
				'image_id' => "$image_count",
				'image_distribution' => "$image_distribution-$image_version",
				'image_application' => "$image_application",
				// using the filename to transport the image_shelf_url
				'image_filename' => "$image_filename <input type=\"hidden\" name=\"image_shelf_url\" value=\"$image_shelf_url\">",
				'image_size' => "$image_size",
				'image_root_password' => "$image_root_password",
				'image_maintainer' => "$image_maintainer",
			);
			$image_count++;
		}
	} else {
	
		$disp = $disp."<br>";
		$disp = $disp."!! Could not connect to image-shelf $image_shelf_name !!!";
		$disp = $disp."!! $image_shelf_conf does not exist !!!";
		$disp = $disp."<br>";
	
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
	$table->sort = '';
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('get');
		$table->identifier = 'image_id';
	}
	$table->max = $image_count;
	$disp = $disp.$table->get_string();

	$disp = $disp."<br>";
	$disp = $disp."<hr>";
	return $disp;
}



function image_storage_select($image_id, $image_shelf_url) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$image_tmp = new image();
	$table = new htmlobject_db_table('image_id');

	$disp = '<h1>Select (NFS-) Image to put the template on</h1>';
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

		// for now we only support nfs-images
		if (!strcmp($image_deployment->type, "nfs-deployment")) {

			$arBody[] = array(
				'image_icon' => "<img width=20 height=20 src=$image_icon>",
				'image_id' => $image_db["image_id"],
				'image_name' => $image_db["image_name"],
				'image_version' => $image_db["image_version"],
				// use the image_type to transport image_id + image_shelf_url
				'image_type' => "$image_deployment->description  <input type=\"hidden\" name=\"image_shelf_url\" value=\"$image_shelf_url\"><input type=\"hidden\" name=\"image_id\" value=\"$image_id\">",
				'image_comment' => $image_db["image_comment"],
			);
		}
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('put');
		$table->identifier = 'image_id';
	}
	$table->max = count($image_array);
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}




function image_shelf_final($final_image_id, $image_id, $image_shelf_url) {
	global $openqrm_server;
	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	// here we execute the request !
	
	// get the image filename on the shelf from its id
	$image_count=1;
	$image_shelf_name = dirname($image_shelf_url);
	$image_shelf_name = str_replace("http://", "", $image_shelf_name);
	$image_shelf_conf = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/image-shelf/web/image-lists/$image_shelf_name/image-shelf.conf";
	$image_shelf_conf_content=file($image_shelf_conf);
	foreach ($image_shelf_conf_content as $value => $image) {
		$image_parameter = explode("|", $image);
		$image_filename = $image_parameter[0];
		$image_distribution = $image_parameter[1];
		$image_version = $image_parameter[2];
		$image_application = $image_parameter[3];
		$image_size = $image_parameter[2];

		if ($image_count == $image_id) {
			break;
		} else {
			$image_count++;
		}
	}

	// get the final_image details
	$final_image = new image();
	$final_image->get_instance_by_id($final_image_id);
	$final_storage = new storage();
	$final_storage->get_instance_by_id($final_image->storageid);
	$final_storage_resource = new resource();
	$final_storage_resource->get_instance_by_id($final_storage->resource_id);
	$final_storage_resource_ip = $final_storage_resource->ip;

	$final_image_export = $final_image->deployment_parameter;
	$final_image_export = str_replace("IMAGE_ROOT_DIR=", "", $final_image_export);
	$final_image_export = str_replace('"', '', $final_image_export);
	$final_image_export = trim($final_image_export);

	$openqrm_server->send_command(" /usr/lib/openqrm/plugins/image-shelf/bin/openqrm-image-shelf get -i $image_shelf_url -f $image_filename -n $final_storage_resource_ip:$final_image_export -d $image_distribution -u $OPENQRM_USER->name -p $OPENQRM_USER->password");

	$disp = '<h1>Executing request</h1>';
	$disp .= '<br>';
	$disp .= '<br>';
	$disp .= "openQRM now tranferring the Content of the Image-template $image_id ($image_filename) from $image_shelf_url to Server-image $final_image_id.";
	$disp .= '<br>';
	$disp .= 'This process will take some time. Please find details about the progress in the Event-list.';
	$disp .= '<br>';
	return $disp;


}



$output = array();
if (strlen($final_image_id)) {
	$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_shelf_final($final_image_id, $image_id, $image_shelf_url));
} else if (strlen($image_id)) {
	$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_storage_select($image_id, $image_shelf_url));
} else if (strlen($image_shelf_url)) {
	$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_shelf_display($image_shelf_url));
} else  {
	$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_shelf_select());
}

echo htmlobject_tabmenu($output);

?>
