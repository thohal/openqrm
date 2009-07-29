<!doctype html>
<html lang="en">
<head>
	<title>Resource overview</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="resource.css" />
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

$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the resource and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
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


if(htmlobject_request('action') != '' && $OPENQRM_USER->role == "administrator") {
    $strMsg = '';
	if(isset($_REQUEST['identifier'])) { 
		switch (htmlobject_request('action')) {
			case 'reboot':
                show_progressbar();
				foreach($_REQUEST['identifier'] as $id) {
					if($id != 0) {
						$resource = new resource();
						$resource->get_instance_by_id($id);
						$ip = $resource->ip;
						$return_msg .= $resource->send_command("$ip", "reboot");
                        $strMsg .= "Rebooted resource $id <br>";
						// set state to transition
						$resource_fields=array();
						$resource_fields["resource_state"]="transition";
						$resource->update_info($id, $resource_fields);
					}
				}
                sleep(1);
				redirect($strMsg);
				break;
	
			case 'poweroff':
                show_progressbar();
				foreach($_REQUEST['identifier'] as $id) {
					if($id != 0) {
						$resource = new resource();
						$resource->get_instance_by_id($id);
						$ip = $resource->ip;
						$return_msg .= $resource->send_command("$ip", "halt");
                        $strMsg .= "Shutdown resource $id <br>";
						// set state to transition
						$resource_fields=array();
						$resource_fields["resource_state"]="off";
						$resource->update_info($id, $resource_fields);
					}
				}
                sleep(1);
				redirect($strMsg);
				break;
	
			case 'remove':
                show_progressbar();
				foreach($_REQUEST['identifier'] as $id) {
					if($id != 0) {
						$resource = new resource();
						$resource->get_instance_by_id($id);
						$mac = $resource->mac;
						$openqrm_server->send_command("openqrm_remove_resource $id, $mac");
						$return_msg .= $resource->remove($id, $mac);
                        $strMsg .= "Removed resource $id <br>";
					}
				}
                sleep(1);
				redirect($strMsg);
				break;


	
		}

	} //identifier
	#else { redirect('Please select a resource'); }
}


function resource_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$resource_tmp = new resource();
	$table = new htmlobject_db_table('resource_id');

	$arHead = array();
	$arHead['resource_state'] = array();
	$arHead['resource_state']['title'] ='';

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';

	$arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_hostname'] = array();
	$arHead['resource_hostname']['title'] ='Name';

	$arHead['resource_mac'] = array();
	$arHead['resource_mac']['title'] ='Mac';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$arHead['resource_type'] = array();
	$arHead['resource_type']['title'] ='Type';


	$arHead['resource_memtotal'] = array();
	$arHead['resource_memtotal']['title'] ='Memory';

	$arHead['resource_load'] = array();
	$arHead['resource_load']['title'] ='Load';

	$arBody = array();
	$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($resource_array as $index => $resource_db) {
		// prepare the values for the array
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		$res_id = $resource->id;
		$mem_total = $resource_db['resource_memtotal'];
		$mem_used = $resource_db['resource_memused'];
		$mem = "$mem_used/$mem_total";
		$swap_total = $resource_db['resource_swaptotal'];
		$swap_used = $resource_db['resource_swapused'];
		$swap = "$swap_used/$swap_total";
		if ($resource->id == 0) {
			$resource_icon_default="/openqrm/base/img/logo.png";
			$resource_type = "openQRM";
		} else {
			$resource_icon_default="/openqrm/base/img/resource.png";
			// the resource_type
			if ((strlen($resource->vtype)) && (!strstr($resource->vtype, "NULL"))){
				// find out what should be preselected
            	$virtualization = new virtualization();
				$virtualization->get_instance_by_id($resource->vtype);
				$resource_type = "<nobr>".$virtualization->name." on Res. ".$resource->vhostid."</nobr>";
			} else {
				$resource_type = "Unknown";
			}
		
		}
		$state_icon="/openqrm/base/img/$resource->state.png";
		// idle ?
		if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
			$state_icon="/openqrm/base/img/idle.png";
		}
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}

		$arBody[] = array(
			'resource_state' => "<img src=$state_icon>",
			'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'resource_id' => $resource_db["resource_id"],
			'resource_hostname' => $resource_db["resource_hostname"],
			'resource_mac' => $resource_db["resource_mac"],
			'resource_ip' => $resource_db["resource_ip"],
			'resource_type' => $resource_type,
			'resource_memtotal' => $mem,
			'resource_load' => $resource_db["resource_load"],
		);

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
		$table->bottom = array('reboot', 'poweroff', 'remove');
		$table->identifier = 'resource_id';
		$table->identifier_disabled = array(0);
	}
	$table->max = $resource_tmp->get_count('all') + 1; // adding openqrmserver
	
  // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './resource-overview.tpl.php');
	$t->setVar(array(
		'resource_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



function resource_form() {

	$virtualization = new virtualization();
	$virtualization_list = array();
	$v_list_select = array();
	$virtualization_list_select = array();
	$virtualization_list = $virtualization->get_list();

	// filter out the virtualization hosts
	foreach ($virtualization_list as $id => $virt) {
		if (!strstr($virt[label], "Host")) {
			$virtualization_list_select[] = array("value" => $virt[value], "label" => $virt[label]);

		}
	}

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './resource-create.tpl.php');
	$t->setVar(array(
		'formaction' => "resource-action.php",
        'hidden_resource_id' => "<input type=hidden name=resource_id value='-1'>",
        'hidden_resource_command' => "<input type=hidden name=resource_command value='new_resource'>",
        'resource_mac' => htmlobject_input('resource_mac', array("value" => 'XX:XX:XX:XX:XX:XX', "label" => 'Mac-address'), 'text', 17),
        'resource_ip' => htmlobject_input('resource_ip', array("value" => '0.0.0.0', "label" => 'Ip-address'), 'text', 20),
		'submit' => htmlobject_input('action', array("value" => 'new', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}



$output = array();
$output[] = array('label' => 'Resource List', 'value' => resource_display());
if($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => resource_form());
}

echo htmlobject_tabmenu($output);

?>

