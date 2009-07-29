<!doctype html>
<html lang="en">
<head>
	<title>NFS manual Storage configuration</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="nfs-storage.css" />
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
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$action=htmlobject_request('action');
$nfs_storage_id = htmlobject_request('nfs_storage_id');
$nfs_storage_name = htmlobject_request('nfs_storage_id');
$nfs_lun_name = htmlobject_request('nfs_lun_name');
$nfs_lun_snap_name = htmlobject_request('nfs_lun_snap_name');
$exports_list_update = htmlobject_request('exports_list_update');

global $nfs_storage_id;
global $nfs_storage_name;
global $nfs_lun_name;
global $nfs_lun_snap_name;
global $exports_list_update;

$nfs_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "nfs_storage_", 11) == 0) {
		$nfs_storage_fields[$key] = $value;
	}
}

$refresh_delay=1;
$refresh_loop_max=20;

$openqrm = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;


function redirect_nfs($strMsg, $nfs_storage_id) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&nfs_storage_id='.$nfs_storage_id;
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


// running the actions
if(htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'update':
                if (strlen($nfs_storage_id)) {
                    show_progressbar();
                    if (!strlen($exports_list_update)) {
                        $redir_msg = "Got emtpy export-list ! Not updating ...";
                        redirect_nfs($redir_msg, $nfs_storage_id);
                    }

                    $storage = new storage();
                    $storage->get_instance_by_id($nfs_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".nfs.stat.manual";

                    if (!file_exists($statfile)) {
                        $full_path_to_statfile = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nfs-storage/web/$statfile";
                        $openqrm->send_command("touch $full_path_to_statfile && chmod 777 $full_path_to_statfile");
                    }
                    if (!wait_for_statfile($statfile)) {
                        $redir_msg = "Error creating file ($statfile)";
                        redirect_nfs($redir_msg, $nfs_storage_id);
                        exit(0);
                    }

                    if (is_writable($statfile)) {
                        if (!$handle = fopen($statfile, 'w')) {
                            $redir_msg = "Cannot open file ($statfile)";
                            redirect_nfs($redir_msg, $nfs_storage_id);
                            exit(0);
                        }
                        if (fwrite($handle, $exports_list_update) === FALSE) {
                            $redir_msg = "Cannot write to file ($statfile)";
                            redirect_nfs($redir_msg, $nfs_storage_id);
                            exit(0);
                        }
                        fclose($handle);
                    } else {
                        $redir_msg = "$statfile is not writeable !";
                        redirect_nfs($redir_msg, $nfs_storage_id);
                        exit(0);
                    }
                    $redir_msg = "Updated export-list for NFS storage $nfs_storage_id";
                    redirect_nfs($redir_msg, $nfs_storage_id);
                }
                break;


            case 'remove':
                if (strlen($nfs_storage_id)) {
                    show_progressbar();
                    $storage = new storage();
                    $storage->get_instance_by_id($nfs_storage_id);
                    $storage_resource = new resource();
                    $storage_resource->get_instance_by_id($storage->resource_id);
                    $storage_resource_id = $storage_resource->id;
                    $statfile="storage/".$storage_resource_id.".nfs.stat.manual";
                    if (!unlink($statfile)) {
                        $redir_msg = "Cannot remove file ($statfile)";
                        redirect_nfs($redir_msg, $nfs_storage_id);
                    }
                    $redir_msg = "Removed export-list for NFS storage $nfs_storage_id";
                    redirect_nfs($redir_msg, $nfs_storage_id);
                }
                break;














        }
    }
}



function nfs_storage_config($nfs_storage_id) {
	global $OPENQRM_USER;
	global $thisfile;
	$storage = new storage();
	$storage->get_instance_by_id($nfs_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

	$table0 = new htmlobject_db_table('storage_id');
	$arHead0 = array();
	$arHead0['storage_state'] = array();
	$arHead0['storage_state']['title'] ='';

	$arHead0['storage_icon'] = array();
	$arHead0['storage_icon']['title'] ='';

	$arHead0['storage_id'] = array();
	$arHead0['storage_id']['title'] ='ID';

	$arHead0['storage_name'] = array();
	$arHead0['storage_name']['title'] ='Name';

	$arHead0['storage_ip'] = array();
	$arHead0['storage_ip']['title'] ='Ip';

	$arHead0['storage_config'] = array();
	$arHead0['storage_config']['title'] ='Manual Config.';

    $storage_icon="/openqrm/base/plugins/nfs-storage/img/storage.png";
    $state_icon="/openqrm/base/img/$storage_resource->state.png";
    if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
        $state_icon="/openqrm/base/img/unknown.png";
    }
    if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
        $resource_icon_default=$storage_icon;
    }
    $storage_configuration="<img src=\"/openqrm/base/img/storage.png\" width=\"24\" height=\"24\" border=\"0\" alt=\"manual config.\"/>";


	$arBody0 = array();
    $arBody0[] = array(
        'storage_state' => "<img src=$state_icon>",
        'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
        'storage_id' => $storage->id,
        'storage_name' => $storage->name,
        'storage_ip' => $storage_resource->ip,
        'storage_config' => $storage_configuration,
    );

	$table0->id = 'Tabelle';
	$table0->css = 'htmlobject_table';
	$table0->border = 1;
	$table0->cellspacing = 0;
	$table0->cellpadding = 3;
	$table0->form_action = $thisfile;
	$table0->head = $arHead0;
	$table0->body = $arBody0;
	$table0->max = 1;

    // manual export file existing ?
    $storage_resource_id = $storage_resource->id;
    $statfile="storage/".$storage_resource_id.".nfs.stat.manual";
    if (file_exists($statfile)) {
        $exports_list = "<ul type=\"disc\">";
		$storage_content=file($statfile);
		foreach ($storage_content as $index => $nfs) {
			// find export name
            $nfs_line = trim($nfs);
            if (strlen($nfs_line)) {
                $exports_list .= "<li>$nfs_line</li>";
            }
        }
        $exports_list .= "</ul>";

    } else {
        $exports_list = "no statfile $statfile exists so far <br>";
    }

    // textarea for adding/updating export file
    $exports_list_update_input = "<strong>Put in your updated exported paths in here </strong><br>";
    $exports_list_update_input .= "<textarea name=\"exports_list_update\" rows=\"8\" cols=\"40\"></textarea>";
    $back_link = "<a href=nfs-storage-manager.php?action=reload&nfs_storage_id=$nfs_storage_id><strong>Back</strong></a>";

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'nfs-storage-config.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'storage_table' => $table0->get_string(),
        'export_list' => $exports_list,
        'exports_list_update_input' => $exports_list_update_input,
    	'hidden_nfs_storage_id' => "<input type='hidden' name='nfs_storage_id' value=$nfs_storage_id>",
		'remove' => htmlobject_input('action', array("value" => 'remove', "label" => 'Remove'), 'submit'),
		'submit' => htmlobject_input('action', array("value" => 'update', "label" => 'Update'), 'submit'),
        'back_link' => $back_link,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


$output = array();
$output[] = array('label' => 'Nfs Storage Config', 'value' => nfs_storage_config($nfs_storage_id));

echo htmlobject_tabmenu($output);

?>


