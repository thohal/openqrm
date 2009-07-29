<!doctype html>
<html lang="en">
<head>
	<title>Image-Shelf manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="image-shelf.css" />
    <link type="text/css" href="/openqrm/base/js/jquery/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>

<style type="text/css">

.ui-progressbar-value {
    background-image: url(/openqrm/base/img/progress.gif);
}

#progressbar {
    position: absolute;
    left: 150px;
    top: 250px;
    width: 400px;
    height: 20px;
}
</style>
</head>
<body>
<div id="progressbar">
</div>


<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


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
// special image-shelf classe
require_once "$RootDir/plugins/image-shelf/class/imageshelf.class.php";

// post parameters
$step = htmlobject_request('step');
$image_id = htmlobject_request('image_id');
$image_shelf_id = htmlobject_request('image_shelf_id');


global $OPENQRM_SERVER_BASE_DIR;
// set ip
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
// set refresh timeout
$refresh_delay=1;
$refresh_loop_max=20;
// actions
if (!strlen($step)) {
    $step=1;
}



function redirect($strMsg, $currenttab = 'tab0', $url = '', $step, $image_shelf_id) {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&step='.$step.'&image_shelf_id='.$image_shelf_id;
	}
	// using meta refresh because of the java-script in the header	
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

function wait_for_statfile($sfile) {
    global $refresh_delay;
    global $refresh_loop_max;
    $refresh_loop=0;
    while (!file_exists($sfile)) {
        sleep($refresh_delay);
        $refresh_loop++;
        flush();
        if ($refresh_loop > $refresh_loop_max)  {
            return false;
        }
    }
    return true;
}


function show_progressbar() {
?>
    <script type="text/javascript">
        $("#progressbar").progressbar({
			value: 100
		});
        var options = {};
        $("#progressbar").effect("shake",options,2000,null);
	</script>
<?php
        flush();
}



if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    show_progressbar();
                    $imageshelf = new imageshelf();
                    $imageshelf->get_instance_by_id($id);
                    $image_shelf_url = $imageshelf->uri;
                    $image_shelf_name = $imageshelf->name;
                    $image_shelf_id = $imageshelf->id;
                    $image_shelf_user=$imageshelf->user;
                    $image_shelf_password=$imageshelf->password;
                    // remove current image-conf
                    $image_shelf_conf = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/image-shelf/web/image-lists/$image_shelf_name/image-shelf.conf";
                    if (file_exists($image_shelf_conf)) {
                        unlink($image_shelf_conf);
                    }
                    // send command
                    $openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/image-shelf/bin/openqrm-image-shelf list -n $image_shelf_name -i $image_shelf_url -u $image_shelf_user -p $image_shelf_password");
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($image_shelf_conf)) {
                        $redir_msg = "Error getting the image-shelf.conf file from the Server ! Please check the Event-Log";
                    } else {
                        $redir_msg = "Displaying server-templates on Image-Shelf $image_shelf_name";
                        // take some time to let the file fully arrive
                        sleep(2);
                    }
                    redirect($redir_msg, '', '', 2, $id);
                    break;
                }
            }
			break;

		case 'get':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $image_id = $id;
                    $image_shelf_id = htmlobject_request('image_shelf_id');
                    $step=3;
                    break;
                }
            }
			break;

		case 'put':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $final_image_id = $id;
                    $image_id = htmlobject_request('image_id');
                    $image_shelf_id = htmlobject_request('image_shelf_id');
                    $step=4;
                    break;
                }
            }
			break;


		case 'remove':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $imageshelf = new imageshelf();
                    $imageshelf->get_instance_by_id($id);
                    $imageshelf_name = $imageshelf->name;
                    $imageshelf->remove($id);
                    $strMsg .= 'Removed imageshelf <b>'.$imageshelf_name.'</b><br>';
                    break;
                }
                $args = '?strMsg='.$strMsg;
                $args .= '&currentab=tab0';
                $url = 'image-shelf-manager.php'.$args;
                redirect($strMsg, '', $url, 1, "");
            }
		break;
	}
}




function image_shelf_select() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$table = new htmlobject_db_table('imageshelf_id');
	$arHead = array();

	$arHead['imageshelf_id'] = array();
	$arHead['imageshelf_id']['title'] ='Id';

	$arHead['imageshelf_name'] = array();
	$arHead['imageshelf_name']['title'] ='Name';

	$arHead['imageshelf_protocol'] = array();
	$arHead['imageshelf_protocol']['title'] ='Proto';

	$arHead['imageshelf_uri'] = array();
	$arHead['imageshelf_uri']['title'] ='URI';

	$image_shelf_count=1;
	$imageshelf_tmp = new imageshelf();
	$imageshelf_array = $imageshelf_tmp->display_overview(0, $table->limit, $table->sort, $table->order);

	$arBody = array();
	foreach ($imageshelf_array as $index => $imageshelf_db) {
		$arBody[] = array(
			'imageshelf_id' => $imageshelf_db["imageshelf_id"],
			'imageshelf_name' => $imageshelf_db["imageshelf_name"],
			'imageshelf_protocol' => $imageshelf_db["imageshelf_protocol"],
			'imageshelf_uri' => $imageshelf_db["imageshelf_uri"],
		);
		$image_shelf_count++;
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
		$table->bottom = array('select', 'remove');
		$table->identifier = 'imageshelf_id';
	}
	$table->max = $image_shelf_count;

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'image-shelf-select.tpl.php');
	$t->setVar(array(
		'image_shelf_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function image_shelf_display($image_shelf_id) {
	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$imageshelf = new imageshelf();
	$imageshelf->get_instance_by_id($image_shelf_id);
	$image_shelf_name = $imageshelf->name;
	$image_shelf_conf = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/image-shelf/web/image-lists/$image_shelf_name/image-shelf.conf";

	$table = new htmlobject_db_table('image_id');
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
    // be sure it is there, otherwise wait for it
    if (!wait_for_statfile($image_shelf_conf)) {
        $redir_msg = "Error getting the image-shelf.conf file from the Server ! Please check the Event-Log";
        redirect($redir_msg, '', '', '', '');
    }
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
				// using the filename to transport the image_shelf_id
				'image_filename' => "$image_filename <input type=\"hidden\" name=\"image_shelf_id\" value=\"$image_shelf_id\">",
				'image_size' => "$image_size",
				'image_root_password' => "$image_root_password",
				'image_maintainer' => "$image_maintainer",
			);
			$image_count++;
		}
	} else {
        $redir_msg = "Could not connect to image-shelf $image_shelf_name ! Please check the Event-Log";
        redirect($redir_msg, '', '', '', '');

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

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'image-shelf-get.tpl.php');
	$t->setVar(array(
		'image_shelf_get_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function image_storage_select($image_id, $image_shelf_id) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$image_tmp = new image();
	$table = new htmlobject_db_table('image_id');

	$disp = "<h1>Select (NFS-) Image to 'put' the template on</h1>";
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
		if ((!strcmp($image_deployment->type, "nfs-deployment")) || (!strcmp($image_deployment->type, "lvm-nfs-deployment"))) {

			$arBody[] = array(
				'image_icon' => "<img width=20 height=20 src=$image_icon>",
				'image_id' => $image_db["image_id"],
				'image_name' => $image_db["image_name"],
				'image_version' => $image_db["image_version"],
				// use the image_type to transport image_id + image_shelf_id
				'image_type' => "$image_deployment->description  <input type=\"hidden\" name=\"image_shelf_id\" value=\"$image_shelf_id\"><input type=\"hidden\" name=\"image_id\" value=\"$image_id\">",
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
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'image-shelf-put.tpl.php');
	$t->setVar(array(
		'image_shelf_put_table' => $table->get_string(),
        'image_template' => $image_id,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}




function image_shelf_final($final_image_id, $image_id, $image_shelf_id) {
	global $openqrm_server;
	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;
	// here we execute the request !
	// get the image filename on the shelf from its id
	$image_count=1;
	$imageshelf = new imageshelf();
	$imageshelf->get_instance_by_id($image_shelf_id);
	$image_shelf_name = $imageshelf->name;
	$image_shelf_uri = $imageshelf->uri;
	$image_shelf_user=$imageshelf->user;
	$image_shelf_password=$imageshelf->password;
	$image_shelf_conf = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/image-shelf/web/image-lists/$image_shelf_name/image-shelf.conf";
    // be sure it is there, otherwise wait for it
    if (!wait_for_statfile($image_shelf_conf)) {
        $redir_msg = "Error getting the image-shelf.conf file from the Server ! Please check the Event-Log";
        redirect($redir_msg, '', '', '', '');
    }
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
	$final_image_export = $final_image->rootdevice;
    // send command
	$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/image-shelf/bin/openqrm-image-shelf get -n $image_shelf_name -i $image_shelf_uri -f $image_filename -s $final_storage_resource_ip:$final_image_export -d $image_distribution -u $image_shelf_user -p $image_shelf_password -o $OPENQRM_USER->name -q $OPENQRM_USER->password");

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'image-shelf-final.tpl.php');
	$t->setVar(array(
        'image_shelf_url' => $image_shelf_uri,
		'image_filename' => $image_filename,
        'final_image_name' => $final_image->name,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
switch ($step) {
	case 1:
		$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_shelf_select());
		break;
	case 2:
		$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_shelf_display($image_shelf_id));
		break;
	case 3:
		$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_storage_select($image_id, $image_shelf_id));
		break;
	case 4:
		$output[] = array('label' => 'Image-Shelf Admin', 'value' => image_shelf_final($final_image_id, $image_id, $image_shelf_id));
		break;
	default:

		break;
}

if(strtolower(OPENQRM_USER_ROLE_NAME) == 'administrator') {
	$output[] = array('label' => 'New Image-Shelf', 'target' => 'image-shelf-new.php');
}

echo htmlobject_tabmenu($output);

?>
