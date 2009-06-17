<!doctype html>
<html lang="en">
<head>
	<title>Citrix XenServer Create VM</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="citrix.css" />
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
global $RESOURCE_INFO_TABLE;
$refresh_delay=1;
$refresh_loop_max=40;

$citrix_server_id = htmlobject_request('citrix_server_id');
$citrix_command = htmlobject_request('citrix_command');
$citrix_name = htmlobject_request('citrix_name');
$citrix_ram = htmlobject_request('citrix_ram');
$citrix_mac = htmlobject_request('citrix_mac');

// place for the citrix stat files
$CitrixDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/citrix/citrix-stat';


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function citrix_server_htmlobject_select($name, $value, $title = '', $selected = '') {
		$html = new htmlobject_select();
		$html->name = $name;
		$html->title = $title;
		$html->selected = $selected;
		$html->text_index = array("value" => "value", "text" => "label");
		$html->text = $value;
		return $html->get_string();
}



function redirect_mgmt($strMsg, $currenttab = 'tab0') {
    global $citrix_server_id;
    $url = 'citrix-manager.php?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&action=refresh&identifier[]='.$citrix_server_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
    global $thisfile;
    global $citrix_server_id;
    if($url == '') {
        $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&citrix_server_id='.$citrix_server_id;
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



// check user input
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



// Dom0 actions
if(htmlobject_request('citrix_command') != '') {
	switch (htmlobject_request('citrix_command')) {

		case 'new':
            if (!strlen($citrix_server_id)) {
                $strMsg .= "Citrix XenServer server-id not set. Not adding new VM!";
                redirect($strMsg, "tab0", $thisfile);
            }
            if (!strlen($citrix_name)) {
                $strMsg .= "Citrix XenServer VM name not set. Not adding new VM!";
                redirect($strMsg, "tab0", $thisfile);
            } else if (!validate_input($citrix_name, 'string')) {
                $strMsg .= "Invalid Citrix XenServer VM name. Not adding new VM!";
                redirect($strMsg, "tab0", $thisfile);
            }
            if (!strlen($citrix_ram)) {
                $strMsg .= "Citrix XenServer VM memory not set. Not adding new VM!";
                redirect($strMsg, "tab0", $thisfile);
            } else if (!validate_input($citrix_ram, 'number')) {
                $strMsg .= "Invalid Citrix XenServer VM memory. Not adding new VM!";
                redirect($strMsg, "tab0", $thisfile);
            }
            if (!strlen($citrix_mac)) {
                $strMsg .= "Citrix XenServer mac-address not set. Not adding new VM!";
                redirect($strMsg, "tab0", $thisfile);
            }
            show_progressbar();
            $citrix_appliance = new appliance();
            $citrix_appliance->get_instance_by_id($citrix_server_id);
            $citrix = new resource();
            $citrix->get_instance_by_id($citrix_appliance->resources);
            $citrix_server_ip = $citrix->ip;
             // already authenticated ?
            $citrix_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix/citrix-stat/citrix-host.pwd.".$citrix_server_ip;
            if (!file_exists($citrix_auth_file)) {
                $strMsg .= "Citrix XenServer not yet authenticated. Please authenticate !";
                redirect($strMsg, "tab0");
            }
            // remove current stat file
            $statfile="citrix-stat/citrix-vm.lst.".$citrix_server_ip;
            if (file_exists($statfile)) {
                unlink($statfile);
            }
            // add resource + type + vhostid
            $resource = new resource();
            $resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
            $resource_ip="0.0.0.0";
            // send command
			$citrix_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix/bin/openqrm-citrix create -i $citrix_server_ip -n $citrix_name -r $citrix_ram -m $citrix_mac";
            // set resource type
            $virtualization = new virtualization();
            $virtualization->get_instance_by_type("citrix-vm");
            // add to openQRM database
            $resource_fields["resource_id"]=$resource_id;
            $resource_fields["resource_ip"]=$resource_ip;
            $resource_fields["resource_mac"]=$citrix_mac;
            $resource_fields["resource_localboot"]=0;
            $resource_fields["resource_vtype"]=$virtualization->id;
            $resource_fields["resource_vhostid"]=$citrix->id;
            $resource->add($resource_fields);
            // give some time for the new-resource hooks
            sleep(5);
			$openqrm_server->send_command($citrix_command);
            // wait for statfile to appear again
            if (!wait_for_statfile($statfile)) {
                $strMsg .= "Error while creating Citrix XenServer VM $citrix_name! Please check the Event-Log<br>";
            } else {
                $strMsg .= "Created Citrix XenServer VM $citrix_name<br>";
            }
            redirect_mgmt($strMsg, "tab0");
			break;



    }
}



function citrix_create() {
	global $citrix_server_id;
	$citrix = new resource();
	$citrix->get_instance_by_id($citrix_server_id);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;
    $back_link = "<a href=\"citrix-manager.php?action=refresh&identifier[]=$citrix_server_id\">Back</a>";
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-create.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'backlink' => $back_link,
		'citrix_server_id' => $citrix_server_id,
		'citrix_server_name' => htmlobject_input('citrix_name', array("value" => '', "label" => 'VM name'), 'text', 20),
		'citrix_server_mac' => htmlobject_input('citrix_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20),
		'citrix_server_ram' => htmlobject_input('citrix_ram', array("value" => '256', "label" => 'Memory (MB)'), 'text', 10),
//		'citrix_server_disk' => htmlobject_input('citrix_disk', array("value" => '', "label" => 'Disk (MB)'), 'text', 10),
//		'citrix_server_swap' => htmlobject_input('citrix_swap', array("value" => '', "label" => 'Swap (MB)'), 'text', 10),
		'hidden_citrix_server_id' => "<input type=hidden name=citrix_server_id value=$citrix_server_id><input type=hidden name=citrix_command value='new'>",
		'submit' => htmlobject_input('action', array("value" => 'new', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Citrix Create VM', 'value' => citrix_create());
}

echo htmlobject_tabmenu($output);

?>


