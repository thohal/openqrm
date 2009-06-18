<!doctype html>
<html lang="en">
<head>
	<title>Xen create vm</title>
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
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
$refresh_delay=1;
$refresh_loop_max=20;

$xen_command = htmlobject_request('xen_command');
$xen_id = htmlobject_request('xen_id');
$xen_name = htmlobject_request('xen_name');
$xen_mac = htmlobject_request('xen_mac');
$xen_ip = htmlobject_request('xen_ip');
$xen_ram = htmlobject_request('xen_ram');
$xen_disk = htmlobject_request('xen_disk');
$xen_swap = htmlobject_request('xen_swap');
$xen_migrate_to_id = htmlobject_request('xen_migrate_to_id');
$xen_migrate_type = htmlobject_request('xen_migrate_type');
$xen_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "xen_", 4) == 0) {
		$xen_fields[$key] = $value;
	}
}
unset($xen_fields["xen_command"]);


function redirect_mgmt($strMsg, $file, $xen_id) {
    global $thisfile;
    global $action;
    $url = $file.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&xen_id='.$xen_id;
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


function validate_input($var, $type) {
    switch ($type) {
        case 'string':
            for ($i = 0; $i<strlen($var); $i++) {
                if (!ctype_alpha($var[$i])) {
                    if (!ctype_digit($var[$i])) {
                        return false;
                    }
                }
            }
            return true;
            break;
        case 'number';
            for ($i = 0; $i<strlen($var); $i++) {
                if (!ctype_digit($var[$i])) {
                    return false;
                }
            }
            return true;
            break;
    }
}


$event->log("$action", $_SERVER['REQUEST_TIME'], 5, "xen-create", "Processing command $action", "", "", 0, 0, 0);
if(htmlobject_request('xen_command') != '') {
    switch ($xen_command) {
        case 'new':
            // send command to xen-host to create the new vm
            show_progressbar();
            if (!strlen($xen_name)) {
                $strMsg="Got empty vm name. Not creating new vm on Xen Host $xen_id";
                redirect_mgmt($strMsg, $thisfile, $xen_id);
            } else if (!validate_input($xen_name, 'string')) {
                $strMsg= "Invalid vm name. Not creating new vm on Xen Host $xen_id";
                redirect_mgmt($strMsg, $thisfile, $xen_id);
            }
            if (!strlen($xen_mac)) {
                $strMsg="Got empty mac-address. Not creating new vm on Xen Host $xen_id";
                redirect_mgmt($strMsg, $thisfile, $xen_id);
            }
            if (!strlen($xen_ram)) {
                $strMsg="Got empty Memory size. Not creating new vm on Xen Host $xen_id";
                redirect_mgmt($strMsg, $thisfile, $xen_id);
            } else if (!validate_input($xen_ram, 'number')) {
                $strMsg .= "Invalid vm memory $xen_ram. Not creating new vm on Xen Host $xen_id";
                redirect_mgmt($strMsg, $thisfile, $xen_id);
            }
            // disk + swap
            // check for disk size is int
            if (strlen($xen_disk)) {
                if (!validate_input($xen_disk, 'number')) {
                    $strMsg .= "Invalid vm disk size. Not creating new vm on Xen Host $xen_id";
                    redirect_mgmt($strMsg, $thisfile, $xen_id);
                }
            }
            if (strlen($xen_swap)) {
                if (!validate_input($xen_swap, 'number')) {
                    $strMsg .= "Invalid vm swap size. Not creating new vm on Xen Host $xen_id";
                    redirect_mgmt($strMsg, $thisfile, $xen_id);
                }
            }

            $xen_appliance = new appliance();
            $xen_appliance->get_instance_by_id($xen_id);
            $xen = new resource();
            $xen->get_instance_by_id($xen_appliance->resources);
            // disk + swap
            if (strlen($xen_disk)) {
                $xen_vm_disk_param = "-d $xen_disk";
            }
            if (strlen($xen_swap)) {
                $xen_vm_swap_param = "-s $xen_swap";
            }
            // unlink stat file
            $statfile="xen-stat/".$xen->id.".vm_list";
            if (file_exists($statfile)) {
                unlink($statfile);
            }
            // add resource + type + vhostid
            $resource = new resource();
            $resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
            $resource_ip="0.0.0.0";
            // send command to the openQRM-server
            $openqrm_server->send_command("openqrm_server_add_resource $resource_id $xen_mac $resource_ip");
            // set resource type
            $virtualization = new virtualization();
            $virtualization->get_instance_by_type("xen-vm");
            // add to openQRM database
            $resource_fields["resource_id"]=$resource_id;
            $resource_fields["resource_ip"]=$resource_ip;
            $resource_fields["resource_mac"]=$xen_mac;
            $resource_fields["resource_localboot"]=0;
            $resource_fields["resource_vtype"]=$virtualization->id;
            $resource_fields["resource_vhostid"]=$xen->id;
            $resource->add($resource_fields);

            // send command
            $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen create -n $xen_name -m $xen_mac -r $xen_ram $xen_vm_disk_param $xen_vm_swap_param -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
            $xen->send_command($xen->ip, $resource_command);
            // and wait for the resulting statfile
            if (!wait_for_statfile($statfile)) {
                $strMsg .= "Error during creating new Xen vm ! Please check the Event-Log<br>";
            } else {
                $strMsg .="Created new Xen vm resource $resource_id<br>";
            }
            redirect_mgmt($strMsg, "xen-manager.php", $xen_id);
            break;


        default:
            $event->log("$xen_command", $_SERVER['REQUEST_TIME'], 3, "xen-create", "No such event command ($xen_command)", "", "", 0, 0, 0);
            break;

    }
}





function xen_create() {
	global $xen_id;

	$xen_appliance = new appliance();
	$xen_appliance->get_instance_by_id($xen_id);
	$xen = new resource();
	$xen->get_instance_by_id($xen_appliance->resources);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;
    $back_link = "<a href=\"xen-manager.php?action=refresh&xen_id=$xen_id\">Back</a>";
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'xen-create.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'backlink' => $back_link,
		'xen_server_id' => $xen_id,
		'xen_server_name' => htmlobject_input('xen_name', array("value" => '', "label" => 'VM name'), 'text', 20),
		'xen_server_mac' => htmlobject_input('xen_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20),
		'xen_server_ip' => htmlobject_input('xen_ip', array("value" => 'dhcp', "label" => 'Ip address'), 'text', 20),
		'xen_server_ram' => htmlobject_input('xen_ram', array("value" => '256', "label" => 'Memory (MB)'), 'text', 10),
		'xen_server_disk' => htmlobject_input('xen_disk', array("value" => '', "label" => 'Disk (MB)'), 'text', 10),
		'xen_server_swap' => htmlobject_input('xen_swap', array("value" => '', "label" => 'Swap (MB)'), 'text', 10),
		'hidden_xen_server_id' => "<input type=hidden name=xen_id value=$xen_id><input type=hidden name=xen_command value='new'>",
		'submit' => htmlobject_input('action', array("value" => 'new', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Xen Create VM', 'value' => xen_create());
}

echo htmlobject_tabmenu($output);

?>


