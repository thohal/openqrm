<!doctype html>
<html lang="en">
<head>
	<title>VMware ESX Create VM</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="vmware-esx.css" />
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
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $RESOURCE_INFO_TABLE;

$vmware_esx_id = htmlobject_request('vmware_esx_id');
$vmware_esx_name = htmlobject_request('vmware_esx_name');
$vmware_esx_mac = htmlobject_request('vmware_esx_mac');
$vmware_esx_ram = htmlobject_request('vmware_esx_ram');
$vmware_esx_disk = htmlobject_request('vmware_esx_disk');
global $vmware_esx_id;
global $vmware_esx_name;
global $vmware_esx_mac;
global $vmware_esx_ram;
global $vmware_esx_disk;

$action=htmlobject_request('action');
$refresh_delay=1;
$refresh_loop_max=30;
$vmware_mac_address_space = "00:50:56:20";

$event = new event();
global $event;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;


function redirect_mgmt($strMsg, $currenttab = 'tab0', $vmware_esx_id) {
    $url = 'vmware-esx-manager.php?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&action=refresh&vmware_esx_id='.$vmware_esx_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

function redirect($strMsg, $currenttab = 'tab0', $vmware_esx_id) {
	global $thisfile;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&vmware_esx_id='.$vmware_esx_id;
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



// running the actions
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
        // vmware-server-actions
		case 'new':
			if (strlen($vmware_esx_id)) {
                // name check
    			if (!strlen($vmware_esx_name)) {
                    $strMsg .= "Empty vm name. Not creating the vm on VMware ESX Host $vmware_esx_id<br>";
                    redirect($strMsg, "tab0", $vmware_esx_id);
                } else if (!validate_input($vmware_esx_name, 'string')) {
                    $strMsg .= "Invalid vm name. Not creating the vm on VMware ESX Host $vmware_esx_id<br>";
                    redirect($strMsg, "tab0", $vmware_esx_id);
                }
                // mach check
    			if (!strlen($vmware_esx_mac)) {
                    $strMsg .= "Empty vm mac-address. Not creating the vm on VMware ESX Host $vmware_esx_id<br>";
                    redirect($strMsg, "tab0", $vmware_esx_id);
                }
                // check for wrong vmware mac address space
                $posted_mac_address_space = substr($vmware_esx_mac, 0, 11);
                if (strcmp($vmware_mac_address_space, $posted_mac_address_space)) {
                    $strMsg .= "Please notice that VMware is using the special mac-address space $vmware_mac_address_space:xx:yy !<br>Other mac-addresses are not supported.<br>";
                    redirect($strMsg, "tab0", $vmware_esx_id);
                }
                // ram check
    			if (!strlen($vmware_esx_ram)) {
                    $strMsg .= "Empty vm memory. Not creating the vm on VMware ESX Host $vmware_esx_id<br>";
                    redirect($strMsg, "tab0", $vmware_esx_id);
                } else if (!validate_input($vmware_esx_ram, 'number')) {
                    $strMsg .= "Invalid vm memory $vmware_esx_ram. Not creating the vm on VMware ESX Host $vmware_esx_id<br>";
                    redirect($strMsg, "tab0", $vmware_esx_id);
                }
                // check for disk size is int
                if (strlen($vmware_esx_disk)) {
                    if (!validate_input($vmware_esx_disk, 'number')) {
                        $strMsg .= "Invalid vm disk size. Not creating the vm on VMware ESX Host $vmware_esx_id<br>";
                        redirect($strMsg, "tab0", $vmware_esx_id);
                    }
                }
                // send command to vmware_esx-host to create the new vm
                show_progressbar();
                $vmware_appliance = new appliance();
                $vmware_appliance->get_instance_by_id($vmware_esx_id);
                $vmware_esx = new resource();
                $vmware_esx->get_instance_by_id($vmware_appliance->resources);
                if (strlen($vmware_esx_disk)) {
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx create -n $vmware_esx_name -m $vmware_esx_mac -r $vmware_esx_ram -d $vmware_esx_disk -i $vmware_esx->ip";
                } else {
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx create -n $vmware_esx_name -m $vmware_esx_mac -r $vmware_esx_ram -i $vmware_esx->ip";
                }
                // remove current stat file
                $vmware_esx_resource_id = $vmware_esx->id;
                $statfile="vmware-esx-stat/".$vmware_esx_resource_id.".vm_list";
                if (file_exists($statfile)) {
                    unlink($statfile);
                }
                // send command
                $openqrm_server->send_command($resource_command);

                // add resource + type + vhostid
                $resource = new resource();
                $resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
                $resource_ip="0.0.0.0";
                // send command to the openQRM-server
                $openqrm_server->send_command("openqrm_esx_add_resource $resource_id $vmware_esx_mac $resource_ip");
                // set resource type
                $virtualization = new virtualization();
                $virtualization->get_instance_by_type("vmware-esx-vm");
                // add to openQRM database
                $resource_fields["resource_id"]=$resource_id;
                $resource_fields["resource_mac"]=$vmware_esx_mac;
                $resource_fields["resource_localboot"]=0;
                $resource_fields["resource_vtype"]=$virtualization->id;
                $resource_fields["resource_vhostid"]=$vmware_esx->id;
                $resource->add($resource_fields);

                // and wait for the resulting statfile
                if (!wait_for_statfile($statfile)) {
                    $strMsg .= "Error during creating the vm on VMware ESX Host $vmware_esx_id ! Please check the Event-Log<br>";
                } else {
                    $strMsg .="Created vm $vmware_esx_name on VMware server 2 Host $vmware_esx_id<br>";
                }
                redirect_mgmt($strMsg, "tab0", $vmware_esx_id);
            }
            break;


        default:
            $event->log("$vmware_esx_command", $_SERVER['REQUEST_TIME'], 3, "vmware-esx-action", "No such vmware-esx command ($vmware_esx_command)", "", "", 0, 0, 0);
            break;
    }

}



function vmware_esx_create() {
	global $vmware_esx_id;
    global $vmware_mac_address_space;

	$vmware_esx_appliance = new appliance();
	$vmware_esx_appliance->get_instance_by_id($vmware_esx_id);
	$vmware_esx = new resource();
	$vmware_esx->get_instance_by_id($vmware_esx_appliance->resources);

    // suggest a mac in the "manual configured mac address" space of vmware
    // please notice that "other" mac address won't work !
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;
    $suggested_last_two_bytes = substr($suggested_mac, 12);
    $suggested_vmware_mac = $vmware_mac_address_space.":".$suggested_last_two_bytes;

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'vmware-esx-create.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'vmware_esx_id' => $vmware_esx_id,
        'vmware_vm_name' => htmlobject_input('vmware_esx_name', array("value" => '', "label" => 'VM name'), 'text', 20),
        'vmware_vm_mac' => htmlobject_input('vmware_esx_mac', array("value" => $suggested_vmware_mac, "label" => 'Mac address'), 'text', 20),
        'vmware_vm_ram' => htmlobject_input('vmware_esx_ram', array("value" => '512', "label" => 'Memory (MB)'), 'text', 10),
        'vmware_vm_disk' => htmlobject_input('vmware_esx_disk', array("value" => '2000', "label" => 'Disk (MB)'), 'text', 10),
        'hidden_vmware_esx_id' => "<input type=hidden name=vmware_esx_id value=$vmware_esx_id>",
		'submit' => htmlobject_input('action', array("value" => 'new', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'VMware ESX Create VM', 'value' => vmware_esx_create());
}

echo htmlobject_tabmenu($output);

?>


