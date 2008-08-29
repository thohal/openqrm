
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
//				$openqrm_server->send_command(" /usr/lib/openqrm/plugins/image-shelf/bin/openqrm-image-shelf list -u $image_shelf_url");
//				sleep($refresh_delay);
			}
			break;

		case 'get':
			foreach($_REQUEST['identifier'] as $id) {
				$image_id = $id;
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

	$arHead['image_distribution'] = array();
	$arHead['image_distribution']['title'] ='Distribution';

	$arHead['image_version'] = array();
	$arHead['image_version']['title'] ='Version';

	$arHead['image_application'] = array();
	$arHead['image_application']['title'] ='Application';

	$arHead['image_filename'] = array();
	$arHead['image_filename']['title'] ='Filename';

	$arHead['image_size'] = array();
	$arHead['image_size']['title'] ='Size';

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
			$image_size = $image_parameter[2];

			$arBody[] = array(
				'image_id' => "$image_count",
				'image_distribution' => "$image_distribution",
				'image_version' => "$image_version",
				'image_application' => "$image_application",
				// using the filename to transport the image_shelf_url
				'image_filename' => "$image_filename <input type=\"hidden\" name=\"image_shelf_url\" value=\"$image_shelf_url\">",
				'image_size' => "(big)",
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


echo "hallo  $image_id  + $image_shelf_url<br>";



}


$output = array();
if (strlen($image_id)) {
	$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_storage_select($image_id, $image_shelf_url));
} else if (strlen($image_shelf_url)) {
	$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_shelf_display($image_shelf_url));
} else  {
	$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_shelf_select());
}

echo htmlobject_tabmenu($output);

?>
