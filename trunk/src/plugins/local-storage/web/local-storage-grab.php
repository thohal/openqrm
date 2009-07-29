<!doctype html>
<html lang="en">
<head>
	<title>Select ZFS Storage</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special local-storage classes
require_once "$RootDir/plugins/local-storage/class/localstoragestate.class.php";

$resource_id = htmlobject_request('resource_id');
$action=htmlobject_request('action');
global $resource_id;
global $APPLIANCE_INFO_TABLE;

$refresh_delay=1;
$refresh_loop_max=20;


function redirect_resource($strMsg, $resource_id) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&identifier[]='.$resource_id;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

function redirect_image($strMsg, $resource_id, $image_id) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&resource_id='.$resource_id.'&identifier[]='.$image_id;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}

// function to set the resource capabilities
function set_res_capabilities($res_id, $cmd, $key, $value) {
    $resource = new resource();
    $resource->get_instance_by_id($res_id);

    switch ($cmd) {
        case 'set':
            $resource_fields["resource_capabilities"] = "$resource->capabilities $key='$value'";
            break;
    }
    $resource->update_info($res_id, $resource_fields);
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




// running the actions
if(htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'grab':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        show_progressbar();
                        $resource = new resource();
                        $resource->get_instance_by_id($id);
                        $redir_msg="Selected resource $id";
                        redirect_resource($redir_msg, $id);
                    }
                }
                break;

            case 'transfer':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        show_progressbar();
                        $image = new image();
                        $image->get_instance_by_id($id);
                        $resource = new resource();
                        $resource->get_instance_by_id($resource_id);
                        // create a token for the grab
                        $grab_token = $image->generatePassword(10);
                        set_res_capabilities($resource_id, "set", "LOCAL_STORAGE_GRAB", $grab_token);
                        // create a new grab appliance
                        $appliance_name = "grab-".$resource_id."-".$id."-x";
                        $appliance_id = openqrm_db_get_free_id('appliance_id', $APPLIANCE_INFO_TABLE);
                        // prepare array to add appliance
                        $ar_grab = array(
                            'appliance_id' => $appliance_id,
                            'appliance_resources' => $resource_id,
                            'appliance_name' => $appliance_name,
                            'appliance_kernelid' => 1,
                            'appliance_imageid' => $id,
                            'appliance_virtualization' => 1,
                            'appliance_cpunumber' => 0,
                            'appliance_memtotal' => 0,
                            'appliance_capabilities' => 'LOCAL_STORAGE_GRAB',
                            'appliance_comment' => "Local-storage grab-appliance",
                            'appliance_ssi' => 0,
                            'appliance_highavailable' => 0,
                        );

                        // create + start the appliance :)
                        $appliance = new appliance();
                        $appliance->add($ar_grab);
                        // wait for appliance being added
                        sleep(1);
                        $appliance->get_instance_by_id($appliance_id);
                        // add apppliance + token to db
                        $local_storage_state = new localstoragestate();
                        $local_storage_state_id = openqrm_db_get_free_id('ls_id', $local_storage_state->_db_table);
                        // prepare array to add appliance
                        $ar_ls_state = array(
                            'ls_id' => $local_storage_state_id,
                            'ls_appliance_id' => $appliance->id,
                            'ls_token' => $grab_token,
                            'ls_state' => 0,
                        );
                        $local_storage_state->add($ar_ls_state);
                        // finally start the appliance + grab phase
                        $appliance->start();
                        $redir_msg="Created temporary grab-appliance $appliance_name. Starting the grab-phase ...";
                        redirect_image($redir_msg, $resource_id, $id);
                    }
                }
                break;

        }
	}
}



function local_select_resource() {
	global $OPENQRM_USER;
	global $thisfile;

	$table = new htmlobject_db_table('resource_id');

	$arHead = array();
	$arHead['resource_state'] = array();
	$arHead['resource_state']['title'] ='';

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';

	$arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_name'] = array();
	$arHead['resource_name']['title'] ='Name';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$arHead['resource_mac'] = array();
	$arHead['resource_mac']['title'] ='Mac';

	$resource_count=0;
	$arBody = array();
	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(1, 1000, 'resource_id', 'ASC');
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		$resource_resource = new resource();
		$resource_resource->get_instance_by_id($resource->resource_id);
        // check if idle
        if ((!strcmp($resource->state, "active")) && ($resource->imageid == 1)) {

            $resource_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$resource_icon="/openqrm/base/plugins/local-resource/img/resource.png";
			$state_icon="/openqrm/base/img/$resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$resource_icon)) {
				$resource_icon_default=$resource_icon;
			}
			$arBody[] = array(
				'resource_state' => "<img src=$state_icon>",
				'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'resource_id' => $resource->id,
				'resource_name' => $resource->hostname,
				'resource_ip' => $resource->ip,
				'resource_mac' => "$resource->mac",
			);
        }
	}
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
    $table->identifier_type = "radio";
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('grab');
		$table->identifier = 'resource_id';
	}
	$table->max = $resource_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'local-storage-grab1.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'idle_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




function local_select_image($resource_id) {
	global $OPENQRM_USER;
	global $thisfile;

	$table = new htmlobject_db_table('image_id');

	$arHead = array();

	$arHead['image_icon'] = array();
	$arHead['image_icon']['title'] ='';

	$arHead['image_id'] = array();
	$arHead['image_id']['title'] ='ID';

	$arHead['image_name'] = array();
	$arHead['image_name']['title'] ='Name';

	$arHead['image_type'] = array();
	$arHead['image_type']['title'] ='Type';

	$arHead['image_rootdevice'] = array();
	$arHead['image_rootdevice']['title'] ='Root-device';

	$image_count=0;
	$arBody = array();
	$image_tmp = new image();
	$image_array = $image_tmp->display_overview(1, 1000, 'image_id', 'ASC');
	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);
        // check if it is a local-storage image
        if (!strcmp($image->type, "local-storage")) {
            $image_count++;
			$image_icon="/openqrm/base/plugins/local-image/img/storage.png";
			$arBody[] = array(
				'image_icon' => "<img width=24 height=24 src=$image_icon><input type='hidden' name='resource_id' value=$resource_id>",
				'image_id' => $image->id,
				'image_name' => $image->name,
				'image_type' => $image->type,
				'image_rootdevice' => "$image->rootdevice",
			);
        }
	}
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
    $table->identifier_type = "radio";
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('transfer');
		$table->identifier = 'image_id';
	}
	$table->max = $image_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'local-storage-grab2.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'resource_id' => $resource_id,
		'image_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


function local_transfer_disk($resource_id, $image_id) {

// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'local-storage-grab3.tpl.php');
	$t->setVar(array(
		'resource_id' => $resource_id,
		'image_id' => $image_id,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}




$output = array();

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'grab':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Grab disk', 'value' => local_select_image($id));
                }
            } else {
            	$output[] = array('label' => 'Select', 'value' => local_select_resource());
            }
            break;

		case 'transfer':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Grab disk', 'value' => local_transfer_disk($resource_id, $id));
                }
            } else {
                $output[] = array('label' => 'Grab disk', 'value' => local_select_image($resource_id));
            }
            break;


	}


} else  {
	$output[] = array('label' => 'Select', 'value' => local_select_resource());
}


echo htmlobject_tabmenu($output);

?>
