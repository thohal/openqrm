<!doctype html>
<html lang="en">
<head>
	<title>KVM manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="kvm.css" />
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
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$kvm_server_id = htmlobject_request('kvm_server_id');
$kvm_vm_mac = htmlobject_request('kvm_vm_mac');
$action=htmlobject_request('action');
global $kvm_server_id;
global $kvm_vm_mac;
$refresh_delay=1;
$refresh_loop_max=20;

$event = new event();
global $event;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;



function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
    global $kvm_server_id;
    if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&kvm_server_id='.$kvm_server_id;
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


// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $kvm_server_id) {
                    show_progressbar();
                    $kvm_appliance = new appliance();
                    $kvm_appliance->get_instance_by_id($kvm_server_id);
                    $kvm_server = new resource();
                    $kvm_server->get_instance_by_id($kvm_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $kvm_server_resource_id = $kvm_server->id;
                    $statfile="kvm-stat/".$kvm_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $kvm_server->send_command($kvm_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during refreshing vm list ! Please check the Event-Log<br>";
                    } else {
                        $strMsg .="Refreshing vm list<br>";
                    }
                    $rurl = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&identifier[]='.$kvm_server_id;
                    redirect($strMsg, "tab0");
                    exit(0);
                }
            }
            break;

		case 'refresh':
            show_progressbar();
            $kvm_appliance = new appliance();
            $kvm_appliance->get_instance_by_id($kvm_server_id);
            $kvm_server = new resource();
            $kvm_server->get_instance_by_id($kvm_appliance->resources);
            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
            // remove current stat file
            $kvm_server_resource_id = $kvm_server->id;
            $statfile="kvm-stat/".$kvm_server_resource_id.".vm_list";
            if (file_exists($statfile)) {
                unlink($statfile);
            }
            // send command
            $kvm_server->send_command($kvm_server->ip, $resource_command);
            // and wait for the resulting statfile
            if (!wait_for_statfile($statfile)) {
                $strMsg .= "Error during refreshing vm list ! Please check the Event-Log<br>";
            } else {
                $strMsg .="Refreshing vm list<br>";
            }
            redirect($strMsg, "tab0");
            break;


        case 'start':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $kvm_server_name) {
                    show_progressbar();
                    $kvm_appliance = new appliance();
                    $kvm_appliance->get_instance_by_id($kvm_server_id);
                    $kvm_server = new resource();
                    $kvm_server->get_instance_by_id($kvm_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm start -n $kvm_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $kvm_server_resource_id = $kvm_server->id;
                    $statfile="kvm-stat/".$kvm_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $kvm_server->send_command($kvm_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during starting $kvm_server_name ! Please check the Event-Log<br>";
                    } else {
    					$strMsg .="Starting $kvm_server_name <br>";
                    }
				}
				redirect($strMsg, "tab0");
            }
            break;


		case 'stop':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $kvm_server_name) {
                    show_progressbar();
                    $kvm_appliance = new appliance();
                    $kvm_appliance->get_instance_by_id($kvm_server_id);
                    $kvm_server = new resource();
                    $kvm_server->get_instance_by_id($kvm_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm stop -n $kvm_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $kvm_server_resource_id = $kvm_server->id;
                    $statfile="kvm-stat/".$kvm_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $kvm_server->send_command($kvm_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during stopping $kvm_server_name ! Please check the Event-Log<br>";
                    } else {
    					$strMsg .="Stopping $kvm_server_name <br>";
                    }
				}
				redirect($strMsg, "tab0");
            }
            break;

		case 'reboot':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $kvm_server_name) {
                    show_progressbar();
                    $kvm_appliance = new appliance();
                    $kvm_appliance->get_instance_by_id($kvm_server_id);
                    $kvm_server = new resource();
                    $kvm_server->get_instance_by_id($kvm_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm reboot -n $kvm_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $kvm_server_resource_id = $kvm_server->id;
                    $statfile="kvm-stat/".$kvm_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $kvm_server->send_command($kvm_server->ip, $resource_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during restarting $kvm_server_name ! Please check the Event-Log<br>";
                    } else {
    					$strMsg .="Restarting $kvm_server_name <br>";
                    }
				}
				redirect($strMsg, "tab0");
            }
			break;

		case 'delete':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $kvm_server_name) {
                    show_progressbar();
                    $kvm_appliance = new appliance();
                    $kvm_appliance->get_instance_by_id($kvm_server_id);
                    $kvm_server = new resource();
                    $kvm_server->get_instance_by_id($kvm_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm delete -n $kvm_server_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    // remove current stat file
                    $kvm_server_resource_id = $kvm_server->id;
                    $statfile="kvm-stat/".$kvm_server_resource_id.".vm_list";
                    if (file_exists($statfile)) {
                        unlink($statfile);
                    }
                    // send command
                    $kvm_server->send_command($kvm_server->ip, $resource_command);
                    // we should remove the resource of the vm !
                    $kvm_resource = new resource();
                    $kvm_resource->get_instance_by_mac($kvm_vm_mac);
                    $kvm_vm_id=$kvm_resource->id;
                    $kvm_resource->remove($kvm_vm_id, $kvm_vm_mac);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($statfile)) {
                        $strMsg .= "Error during removing $kvm_server_name ! Please check the Event-Log<br>";
                    } else {
    					$strMsg .="Removed $kvm_server_name and its resource $kvm_vm_id<br><br>";
                    }
				}
				redirect($strMsg, "tab0");
            }
			break;


	}
}





function kvm_server_select() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_db_table('kvm_server_id');

	$arHead = array();
	$arHead['kvm_server_state'] = array();
	$arHead['kvm_server_state']['title'] ='';

	$arHead['kvm_server_icon'] = array();
	$arHead['kvm_server_icon']['title'] ='';

	$arHead['kvm_server_id'] = array();
	$arHead['kvm_server_id']['title'] ='ID';

	$arHead['kvm_server_name'] = array();
	$arHead['kvm_server_name']['title'] ='Name';

	$arHead['kvm_server_resource_id'] = array();
	$arHead['kvm_server_resource_id']['title'] ='Res.ID';

	$arHead['kvm_server_resource_ip'] = array();
	$arHead['kvm_server_resource_ip']['title'] ='Ip';

	$arHead['kvm_server_comment'] = array();
	$arHead['kvm_server_comment']['title'] ='Comment';

	$kvm_server_count=0;
	$arBody = array();
	$kvm_server_tmp = new appliance();
	$kvm_server_array = $kvm_server_tmp->display_overview(0, 100, 'appliance_id', 'ASC');

	foreach ($kvm_server_array as $index => $kvm_server_db) {
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($kvm_server_db["appliance_virtualization"]);
		if ((strstr($virtualization->type, "kvm")) && (!strstr($virtualization->type, "kvm-vm"))) {
			$kvm_server_resource = new resource();
			$kvm_server_resource->get_instance_by_id($kvm_server_db["appliance_resources"]);
			$kvm_server_count++;
			$resource_icon_default="/openqrm/base/img/resource.png";
			$kvm_server_icon="/openqrm/base/plugins/kvm/img/plugin.png";
			$state_icon="/openqrm/base/img/$kvm_server_resource->state.png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$kvm_server_icon)) {
				$resource_icon_default=$kvm_server_icon;
			}
			$arBody[] = array(
				'kvm_server_state' => "<img src=$state_icon>",
				'kvm_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'kvm_server_id' => $kvm_server_db["appliance_id"],
				'kvm_server_name' => $kvm_server_db["appliance_name"],
				'kvm_server_resource_id' => $kvm_server_resource->id,
				'kvm_server_resource_ip' => $kvm_server_resource->ip,
				'kvm_server_comment' => $kvm_server_db["appliance_comment"],
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
		$table->bottom = array('select');
		$table->identifier = 'kvm_server_id';
	}
	$table->max = $kvm_server_count;
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'kvm_server_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function kvm_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $OPENQRM_SERVER_BASE_DIR;

	$table = new htmlobject_table_identifiers_checked('kvm_server_id');

	$arHead = array();
	$arHead['kvm_server_state'] = array();
	$arHead['kvm_server_state']['title'] ='State';

	$arHead['kvm_server_icon'] = array();
	$arHead['kvm_server_icon']['title'] ='Type';

	$arHead['kvm_server_id'] = array();
	$arHead['kvm_server_id']['title'] ='ID';

	$arHead['kvm_server_name'] = array();
	$arHead['kvm_server_name']['title'] ='Name';

	$arHead['kvm_server_resource_id'] = array();
	$arHead['kvm_server_resource_id']['title'] ='Res.ID';

	$arHead['kvm_server_resource_ip'] = array();
	$arHead['kvm_server_resource_ip']['title'] ='Ip';

	$arHead['kvm_server_comment'] = array();
	$arHead['kvm_server_comment']['title'] ='';

	$arHead['kvm_server_create'] = array();
	$arHead['kvm_server_create']['title'] ='';

	$kvm_server_count=1;
	$arBody = array();
	$kvm_server_tmp = new appliance();
	$kvm_server_tmp->get_instance_by_id($appliance_id);
	$kvm_server_resource = new resource();
	$kvm_server_resource->get_instance_by_id($kvm_server_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$kvm_server_icon="/openqrm/base/plugins/kvm/img/plugin.png";
	$state_icon="/openqrm/base/img/$kvm_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$kvm_server_icon)) {
		$resource_icon_default=$kvm_server_icon;
	}
	$kvm_server_create_button="<a href=\"kvm-create.php?kvm_server_id=$kvm_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'kvm_server_state' => "<img src=$state_icon>",
		'kvm_server_icon' => "<img width=24 height=24 src=$resource_icon_default><input type='hidden' name='kvm_server_id' value=$appliance_id>",
		'kvm_server_id' => $kvm_server_tmp->id,
		'kvm_server_name' => $kvm_server_tmp->name,
		'kvm_server_resource_id' => $kvm_server_resource->id,
		'kvm_server_resource_ip' => $kvm_server_resource->ip,
		'kvm_server_comment' => $kvm_server_tmp->comment,
		'kvm_server_create' => $kvm_server_create_button,
	);
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
		$table->identifier = 'kvm_server_id';
	}
	$table->max = $kvm_server_count;

    // table 1
    $table1 = new htmlobject_db_table('kvm_vm_name');
	$arHead1 = array();
	$arHead1['kvm_vm_state'] = array();
	$arHead1['kvm_vm_state']['title'] ='State';

	$arHead1['kvm_vm_res'] = array();
	$arHead1['kvm_vm_res']['title'] ='Res.';

	$arHead1['kvm_vm_name'] = array();
	$arHead1['kvm_vm_name']['title'] ='Name';

	$arHead1['kvm_vm_ip'] = array();
	$arHead1['kvm_vm_ip']['title'] ='IP';

	$arHead1['kvm_vm_mac'] = array();
	$arHead1['kvm_vm_mac']['title'] ='MAC';

	$arHead1['kvm_vm_actions'] = array();
	$arHead1['kvm_vm_actions']['title'] ='Actions';
    $arBody1 = array();

    $kvm_server_vm_list_file="kvm-stat/$kvm_server_resource->id.vm_list";
	$kvm_vm_registered=array();
    $kvm_vm_count=0;
	if (file_exists($kvm_server_vm_list_file)) {
		$kvm_server_vm_list_content=file($kvm_server_vm_list_file);
		foreach ($kvm_server_vm_list_content as $index => $kvm_server_name) {
			// find the vms
			if (!strstr($kvm_server_name, "#")) {
				// vms
				$kvm_short_name=basename($kvm_server_name);
                // check if active
                $kvm_vm_state = trim(substr($kvm_short_name, strlen($kvm_short_name)-2, 2));
                $kvm_vm_mac = trim(substr($kvm_short_name, strlen($kvm_short_name)-21, 18));
                $kvm_short_name = trim(substr($kvm_short_name, 0, strlen($kvm_short_name)-21));
                // get ip
                $kvm_resource = new resource();
                $kvm_resource->get_instance_by_mac($kvm_vm_mac);
                $kvm_vm_ip = $kvm_resource->ip;
                $kvm_vm_id = $kvm_resource->id;

                // fill the actions and set state icon
                $vm_actions = "";
                if (!strcmp($kvm_vm_state, "1")) {
                    $state_icon="/openqrm/base/img/active.png";
                    $vm_actions = $vm_actions."<a href=\"$thisfile?identifier[]=$kvm_short_name&action=stop&kvm_server_id=$kvm_server_tmp->id\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                    $vm_actions = $vm_actions."<a href=\"$thisfile?identifier[]=$kvm_short_name&action=reboot&kvm_server_id=$kvm_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"> Reboot</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                } else {
                    $state_icon="/openqrm/base/img/off.png";
    				$vm_actions = $vm_actions."<a href=\"$thisfile?identifier[]=$kvm_short_name&action=start&kvm_server_id=$kvm_server_tmp->id\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    				$vm_actions = $vm_actions."<a href=\"kvm-vm-config.php?kvm_server_name=$kvm_short_name&kvm_server_id=$kvm_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"> Config</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    				$vm_actions = $vm_actions."<a href=\"$thisfile?identifier[]=$kvm_short_name&action=delete&kvm_server_id=$kvm_server_tmp->id&kvm_vm_mac=$kvm_vm_mac\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Delete</a>&nbsp;&nbsp;";
                }

				$kvm_vm_registered[] = $kvm_short_name;
                $kvm_vm_count++;

                $arBody1[] = array(
                    'kvm_vm_state' => "<img src=$state_icon><input type='hidden' name='kvm_server_id' value=$appliance_id><input type='hidden' name='kvm_vm_mac' value=$kvm_vm_mac>",
                    'kvm_vm_id' => $kvm_vm_id,
                    'kvm_vm_name' => $kvm_short_name,
                    'kvm_vm_ip' => $kvm_vm_ip,
                    'kvm_vm_mac' => $kvm_vm_mac,
                    'kvm_vm_actions' => $vm_actions,
                );

			}
		}
	}
	$table1->id = 'Tabelle';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
	$table1->sort = '';
	$table1->identifier_type = "checkbox";
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	if ($OPENQRM_USER->role == "administrator") {
		$table1->bottom = array('start', 'stop', 'restart', 'delete');
		$table1->identifier = 'kvm_vm_name';
	}
	$table1->max = $kvm_vm_count;

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-vms.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'kvm_server_table' => $table->get_string(),
        'kvm_server_id' => $kvm_server_resource->id,
        'kvm_server_name' => $kvm_server_resource->hostname,
        'kvm_vm_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




$output = array();
$kvm_server_id = $_REQUEST["kvm_server_id"];
if(htmlobject_request('action') != '') {
    if (isset($_REQUEST['identifier'])) {
        switch (htmlobject_request('action')) {
            case 'select':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_display($id));
                }
                break;
            case 'refresh':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_display($id));
                }
                break;
        }
    } else {
    	$output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_select());
    }
} else if (strlen($kvm_server_id)) {
	$output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_display($kvm_server_id));
} else  {
	$output[] = array('label' => 'Kvm-Server Admin', 'value' => kvm_server_select());
}

echo htmlobject_tabmenu($output);

?>
