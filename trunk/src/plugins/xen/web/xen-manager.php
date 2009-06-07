<!doctype html>
<html lang="en">
<head>
	<title>Xen manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="xen.css" />
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

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=1;
$refresh_loop_max=20;

$xen_id = htmlobject_request('xen_id');
$xen_migrate_to_id = htmlobject_request('xen_migrate_to_id');
$xen_migrate_type = htmlobject_request('xen_migrate_type');



function xen_htmlobject_select($name, $value, $title = '', $selected = '') {
    $html = new htmlobject_select();
    $html->name = $name;
    $html->title = $title;
    $html->selected = $selected;
    $html->text_index = array("value" => "value", "text" => "label");
    $html->text = $value;
    return $html->get_string();
}



function redirect($strMsg, $currenttab = 'tab0', $url = '') {
    global $thisfile;
    global $xen_id;
    if($url == '') {
        $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&xen_id='.$xen_id;
    }
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




// Dom0 actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {

		case 'refresh':
            if (strlen($xen_id)) {
                show_progressbar();
                $xen_appliance = new appliance();
                $xen_appliance->get_instance_by_id($xen_id);
                $xen = new resource();
                $xen->get_instance_by_id($xen_appliance->resources);
                // remove current stat file
                $statfile="xen-stat/$xen->id.vm_list";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                $xen->send_command($xen->ip, $resource_command);
                // wait for statfile to appear again
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error while refreshing Xen vm list ! Please check the Event-Log<br>";
                } else {
                    $strMsg .= "Refreshed Xen vm list<br>";
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'select':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $xen_id) {
                    show_progressbar();
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    // remove current stat file
                    $statfile="xen-stat/$xen->id.vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while refreshing Xen vm list ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Refreshed Xen vm list<br>";
                    }
                    redirect($strMsg, "tab0");
                }
            }
			break;
    }
}


// xen vm actions
if(htmlobject_request('action_table1') != '') {
	switch (htmlobject_request('action_table1')) {

		case 'start':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
                    show_progressbar();
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    // remove current stat file
                    $statfile="xen-stat/$xen->id.vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen start -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while starting Xen vm $xen_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Started Xen vm $xen_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'stop':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
                    show_progressbar();
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    // remove current stat file
                    $statfile="xen-stat/$xen->id.vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen stop -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while stopping Xen vm $xen_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Stopped Xen vm $xen_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'reboot':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
                    show_progressbar();
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    // remove current stat file
                    $statfile="xen-stat/$xen->id.vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen reboot -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while rebooting Xen vm $xen_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Rebooted Xen vm $xen_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'remove':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
                    show_progressbar();
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    // remove current stat file
                    $statfile="xen-stat/$xen->id.vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen remove -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while removing Xen vm $xen_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Removed Xen vm $xen_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'migrate':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
                    show_progressbar();
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $destination = new resource();
                    $destination->get_instance_by_id($xen_migrate_to_id);

                    // remove current stat file
                    $statfile="xen-stat/$xen->id.vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    if ("$xen_migrate_type" == "1") {
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen migrate -n $xen_name -i $destination->ip -t live -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    } else {
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen migrate -n $xen_name -i $destination->ip -t regular -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    }
                    $xen->send_command($xen->ip, $resource_command);
                    // wait for statfile to appear again
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error while migrating Xen vm $xen_name ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .= "Migrated Xen vm $xen_name<br>";
                    }
                }
                redirect($strMsg, "tab0");
            }
			break;
    }
}





function xen_select() {
    global $OPENQRM_USER;
    global $thisfile;
    $table = new htmlobject_db_table('xen_id');

    $arHead = array();
    $arHead['xen_state'] = array();
    $arHead['xen_state']['title'] ='';

    $arHead['xen_icon'] = array();
    $arHead['xen_icon']['title'] ='';

    $arHead['xen_id'] = array();
    $arHead['xen_id']['title'] ='ID';

    $arHead['xen_name'] = array();
    $arHead['xen_name']['title'] ='Name';

    $arHead['xen_resource_id'] = array();
    $arHead['xen_resource_id']['title'] ='Res.ID';

    $arHead['xen_resource_ip'] = array();
    $arHead['xen_resource_ip']['title'] ='Ip';

    $arHead['xen_comment'] = array();
    $arHead['xen_comment']['title'] ='Comment';

    $xen_count=0;
    $arBody = array();
    $xen_tmp = new appliance();
    $xen_array = $xen_tmp->display_overview(0, 10, 'appliance_id', 'ASC');

    foreach ($xen_array as $index => $xen_db) {
        $virtualization = new virtualization();
        $virtualization->get_instance_by_id($xen_db["appliance_virtualization"]);
        if ((strstr($virtualization->type, "xen")) && (!strstr($virtualization->type, "xen-vm"))) {
            $xen_resource = new resource();
            $xen_resource->get_instance_by_id($xen_db["appliance_resources"]);
            $xen_count++;
            $resource_icon_default="/openqrm/base/img/resource.png";
            $xen_icon="/openqrm/base/plugins/xen/img/plugin.png";
            $state_icon="/openqrm/base/img/$xen_resource->state.png";
            if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
                $state_icon="/openqrm/base/img/unknown.png";
            }
            if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$xen_icon)) {
                $resource_icon_default=$xen_icon;
            }
            $arBody[] = array(
                'xen_state' => "<img width=16 height=16 src=$state_icon>",
                'xen_icon' => "<img width=24 height=24 src=$resource_icon_default>",
                'xen_id' => $xen_db["appliance_id"],
                'xen_name' => $xen_resource->hostname,
                'xen_resource_id' => $xen_resource->id,
                'xen_resource_ip' => $xen_resource->ip,
                'xen_comment' => $xen_resource->comment,
            );
        }
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
        $table->bottom = array('select');
        $table->identifier = 'xen_id';
    }
    $table->max = $xen_count;

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'xen-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'xen_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}




function xen_display($appliance_id) {
    global $OPENQRM_SERVER_BASE_DIR;
    global $OPENQRM_USER;
    global $thisfile;

    // refresh
    $xen_appliance = new appliance();
    $xen_appliance->get_instance_by_id($appliance_id);
    $xen = new resource();
    $xen->get_instance_by_id($xen_appliance->resources);

    // dom0 infos
    $arBody = array();
    $resource_icon_default="/openqrm/base/img/resource.png";
    $xen_icon="/openqrm/base/plugins/xen/img/plugin.png";
    $state_icon="/openqrm/base/img/$xen->state.png";
    if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
        $state_icon="/openqrm/base/img/unknown.png";
    }
    if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$xen_icon)) {
        $resource_icon_default=$xen_icon;
    }
    $xen_create_button="<a href=\"xen-create.php?xen_id=$xen_appliance->id\" style=\"text-decoration: none\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
    // here we take the resource id as the identifier because
    // we need to run commands on the resource ip
    $arBody[] = array(
        'xen_state' => "<img width=16 height=16 src=$state_icon><input type='hidden' name='xen_id' value=$appliance_id>",
        'xen_icon' => "<img width=24 height=24 src=$resource_icon_default>",
        'xen_id' => $xen_appliance->id,
        'xen_name' => $xen->hostname,
        'xen_resource_id' => $xen->id,
        'xen_resource_ip' => $xen->ip,
        'xen_resource_memory' => $xen->memtotal." MB",
        'xen_create' => $xen_create_button,
    );


    // vm infos
    $loop = 0;
    $arBody1 = array();
    $xen_vm_list_file="xen-stat/$xen->id.vm_list";
    if (file_exists($xen_vm_list_file)) {
        $xen_vm_list_content=file($xen_vm_list_file);
        foreach ($xen_vm_list_content as $index => $xenxmoutput) {
            if ($loop == 0) {
                $loop = 1;
                continue;
            }
            $first_at_pos = strpos($xenxmoutput, "@");
            $first_at_pos++;
            $xen_name_first_at_removed = substr($xenxmoutput, $first_at_pos, strlen($xenxmoutput)-$first_at_pos);
            $second_at_pos = strpos($xen_name_first_at_removed, "@");
            $second_at_pos++;
            $xen_name_second_at_removed = substr($xen_name_first_at_removed, $second_at_pos, strlen($xen_name_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($xen_name_second_at_removed, "@");
            $third_at_pos++;
            $xen_name_third_at_removed = substr($xen_name_second_at_removed, $third_at_pos, strlen($xen_name_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($xen_name_third_at_removed, "@");
            $fourth_at_pos++;
            $xen_name_fourth_at_removed = substr($xen_name_third_at_removed, $fourth_at_pos, strlen($xen_name_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($xen_name_fourth_at_removed, "@");
            $fivth_at_pos++;
            $xen_name_fivth_at_removed = substr($xen_name_fourth_at_removed, $fivth_at_pos, strlen($xen_name_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($xen_name_fivth_at_removed, "@");
            $sixth_at_pos++;
            $xen_name_sixth_at_removed = substr($xen_name_fivth_at_removed, $sixth_at_pos, strlen($xen_name_fivth_at_removed)-$fivth_at_pos);
            $seventh_at_pos = strpos($xen_name_sixth_at_removed, "@");
            $seventh_at_pos++;

            $xen_openqrm_vm = trim(substr($xenxmoutput, 0, $first_at_pos-1));
            $xen_name = trim(substr($xen_name_first_at_removed, 0, $second_at_pos-1));
            $xen_vm_memory = trim(substr($xen_name_second_at_removed, 0, $third_at_pos-1));
            $xen_vm_mac = trim(substr($xen_name_third_at_removed, 0, $fourth_at_pos-1));
            $xen_vm_bridge = trim(substr($xen_name_fourth_at_removed, 0, $fivth_at_pos-1));
            $xen_vm_vnc = trim(substr($xen_name_fivth_at_removed, 0, $sixth_at_pos-1));
            $xen_vm_online = trim(substr($xen_name_sixth_at_removed, 0));

            $xen_vm_resource = new resource();
            $xen_vm_resource->get_instance_by_mac($xen_vm_mac);
            $xen_vm_id = $xen_vm_resource->id;
            $xen_vm_ip = $xen_vm_resource->ip;

            // if it is an openqrm vm -> plus migration
            // we need a select with the ids/ips from all resources which
            // are used by appliances with xen capabilities
            $xen_host_resource_list = array();
            $appliance_list = new appliance();
            $appliance_list_array = $appliance_list->get_list();
            foreach ($appliance_list_array as $index => $app) {
                $appliance_xen_host_check = new appliance();
                $appliance_xen_host_check->get_instance_by_id($app["value"]);
                $virtualization = new virtualization();
                $virtualization->get_instance_by_id($appliance_xen_host_check->virtualization);
                if ((strstr($virtualization->type, "xen")) && (!strstr($virtualization->type, "xen-vm"))) {
                    $xen_host_resource = new resource();
                    $xen_host_resource->get_instance_by_id($appliance_xen_host_check->resources);
                    $xen_host_resource_list[] = array("value"=>$xen_host_resource->id, "label"=>$xen_host_resource->ip,);
                }
            }

            $migrateion_select = xen_htmlobject_select('xen_migrate_to_id', $xen_host_resource_list, '', $xen_host_resource_list);

            // here we fill table 1
            $xen_vm_actions = "";
            $xen_vm_migrate_actions = "";
            // online ? openqrm-vm ?
            if ($xen_vm_online == 1) {
                $xen_vm_state_icon = "/openqrm/base/img/active.png";
                // online actions
                $xen_vm_actions= $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=stop&xen_id=$xen_appliance->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"></a>&nbsp;";
                $xen_vm_actions = $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=reboot&xen_id=$xen_appliance->id\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"></a>&nbsp;";
                if ($xen_openqrm_vm == 1) {
                    $xen_vm_migrate_actions = $xen_vm_migrate_actions."<b><input type='checkbox' name='xen_migrate_type' value='1'> live</b>";
                    $xen_vm_migrate_actions = $xen_vm_migrate_actions.$migrateion_select;
                }
            } else {
                $xen_vm_state_icon = "/openqrm/base/img/off.png";
                $xen_vm_actions= $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=start&xen_id=$xen_appliance->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"></a>&nbsp;";
                if ($xen_openqrm_vm == 1) {
                    $xen_vm_actions = $xen_vm_actions."<a href=\"xen-vm-config.php?xen_name=$xen_name&xen_id=$xen_appliance->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"></a>&nbsp;";
                    $xen_vm_actions = $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=remove&xen_id=$xen_appliance->id\"><img height=16 width=16 src=\"/openqrm/base/img/off.png\" border=\"0\"></a>&nbsp;";
                }
            }

            // add to table1
            $arBody1[] = array(
                'xen_vm_state' => "<img src=$xen_vm_state_icon><input type='hidden' name='xen_id' value=$xen_appliance->id>",
                'xen_vm_id' => $xen_vm_id,
                'xen_vm_name' => $xen_name,
                'xen_vm_vnc' => $xen_vm_vnc,
                'xen_vm_ip' => $xen_vm_ip,
                'xen_vm_mac' => $xen_vm_mac,
                'xen_vm_bridge' => $xen_vm_bridge,
                'xen_vm_memory' => $xen_vm_memory." MB",
                'xen_vm_actions' => $xen_vm_actions,
                'xen_vm_migrate_actions' => $xen_vm_migrate_actions,
            );


        }
    }


    // main output section
    // ############################ Xen Host table #############################
    $disp = "<h1>Xen-Admin</h1>";
    $disp = $disp."<br>";

    $table = new htmlobject_table_identifiers_checked('xen_id');

    $arHead = array();
    $arHead['xen_state'] = array();
    $arHead['xen_state']['title'] ='';

    $arHead['xen_icon'] = array();
    $arHead['xen_icon']['title'] ='';

    $arHead['xen_id'] = array();
    $arHead['xen_id']['title'] ='ID';

    $arHead['xen_name'] = array();
    $arHead['xen_name']['title'] ='Name';

    $arHead['xen_resource_id'] = array();
    $arHead['xen_resource_id']['title'] ='Res.ID';

    $arHead['xen_resource_ip'] = array();
    $arHead['xen_resource_ip']['title'] ='Ip';

    $arHead['xen_resource_memory'] = array();
    $arHead['xen_resource_memory']['title'] ='Memory';

    $arHead['xen_create'] = array();
    $arHead['xen_create']['title'] ='';

    $table->id = 'Tabelle';
    $table->css = 'htmlobject_table';
    $table->border = 1;
    $table->cellspacing = 0;
    $table->cellpadding = 3;
    $table->form_action = $thisfile;
    $table->sort = '';
    $table->head = $arHead;
    $table->body = $arBody;
    if ($OPENQRM_USER->role == "administrator") {
        $table->bottom = array('refresh');
        $table->identifier = 'xen_id';
    }
    $table->max = 1;


    // ############################ Xen vms table ###################
    $disp = $disp."<h1>VMs on Xen Host $xen->id/$xen->hostname</h1>";
    $table1 = new htmlobject_db_table('xen_vm_name');
    $arHead1 = array();
    $arHead1['xen_vm_state'] = array();
    $arHead1['xen_vm_state']['title'] ='';

    $arHead1['xen_vm_res'] = array();
    $arHead1['xen_vm_res']['title'] ='Res.';

    $arHead1['xen_vm_name'] = array();
    $arHead1['xen_vm_name']['title'] ='Name';

    $arHead1['xen_vm_vnc'] = array();
    $arHead1['xen_vm_vnc']['title'] ='vnc';

    $arHead1['xen_vm_ip'] = array();
    $arHead1['xen_vm_ip']['title'] ='IP';

    $arHead1['xen_vm_mac'] = array();
    $arHead1['xen_vm_mac']['title'] ='MAC';

    $arHead1['xen_vm_bridge'] = array();
    $arHead1['xen_vm_bridge']['title'] ='Bridge';

    $arHead1['xen_vm_memory'] = array();
    $arHead1['xen_vm_memory']['title'] ='Memory';

    $arHead1['xen_vm_actions'] = array();
    $arHead1['xen_vm_actions']['title'] ='VM-Actions';

    $arHead1['$xen_vm_migrate_actions'] = array();
    $arHead1['$xen_vm_migrate_actions']['title'] ='Migration';



    $table1->id = 'Tabelle';
    $table1->css = 'htmlobject_table';
    $table1->border = 1;
    $table1->cellspacing = 0;
    $table1->cellpadding = 3;
    $table1->form_action = $thisfile;
    $table1->sort = '';
    $table1->identifier_type = "checkbox";
    $table1->bottom_buttons_name = "action_table1";
    $table1->identifier_name = "identifier_table1";
    $table1->head = $arHead1;
    $table1->body = $arBody1;
    if ($OPENQRM_USER->role == "administrator") {
        $table1->bottom = array('start', 'stop', 'restart', 'remove', 'migrate');
        $table1->identifier = 'xen_vm_name';
    }
    $table1->max = count($registerd_vms);

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'xen-vms.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'xen_server_table' => $table->get_string(),
        'xen_server_id' => $xen_appliance->id,
        'xen_server_name' => $xen_appliance->name,
        'xen_vm_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');

    return $disp;

}



$output = array();
if(htmlobject_request('action') != '') {
    if (isset($_REQUEST['identifier'])) {
        switch (htmlobject_request('action')) {
            case 'select':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Xen Admin', 'value' => xen_display($id));
                }
                break;
            case 'refresh':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Xen Admin', 'value' => xen_display($id));
                }
                break;
        }

    } else {
        $output[] = array('label' => 'Xen Admin', 'value' => xen_select());
    }

} else if (strlen($xen_id)) {
    $output[] = array('label' => 'Xen Admin', 'value' => xen_display($xen_id));
} else  {
    $output[] = array('label' => 'Xen Admin', 'value' => xen_select());
}

echo htmlobject_tabmenu($output);

?>


