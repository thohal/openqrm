
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

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
require_once "$RootDir/class/folder.class.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/resource.class.php";
// special linuxcoeresource classe
require_once "$RootDir/plugins/linuxcoe/class/linuxcoeresource.class.php";

// some static defines
$refresh_delay=2;
$step=1;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_BASE_DIR;


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	// using meta refresh because of the java-script in the header	
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}





if(htmlobject_request('action') != '') {
	if (is_array($_REQUEST['identifier'])) {
		switch (htmlobject_request('action')) {
			case 'select':
				foreach($_REQUEST['identifier'] as $profile_name) {
					$step=2;
					$lcoe_profile_name = $profile_name;
					break;
				}
				break;
	
			case 'apply':
				foreach($_REQUEST['identifier'] as $id) {
					$step=3;
					$lcoe_profile_name = htmlobject_request('lcoe_profile_name');
					$lcoe_resource_id = $id;
	
					$lcoe_resource = new resource();
					$lcoe_resource->get_instance_by_id($id);
					$lcoe_resource_id=$lcoe_resource->id;
					$lcoe_resource_mac=$lcoe_resource->mac;
					$lcoe_resource_ip=$lcoe_resource->ip;
					$lcoe_resource_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/bin/openqrm-linuxcoe-manager apply $lcoe_profile_name $lcoe_resource_id $lcoe_resource_ip $lcoe_resource_mac";
					$openqrm_server->send_command($lcoe_resource_cmd);
					sleep($refresh_delay);
	
					$strMsg .= $lcoe_resource->send_command($lcoe_resource_ip, "reboot");
					// set state to transition
					$resource_fields=array();
					$resource_fields["resource_state"]="transition";
					$lcoe_resource->update_info($lcoe_resource_id, $resource_fields);
	
					// create a linuxcoeresource object to monitor its state
					$lcoe_resource = new linuxcoeresource();
					$lcoe_resource_fields=array();
					$lcoe_resource_fields['linuxcoe_id'] = openqrm_db_get_free_id('linuxcoe_id', $lcoe_resource->_db_table);
					$lcoe_resource_fields['linuxcoe_resource_id'] = $lcoe_resource_id;
					$lcoe_resource_fields['linuxcoe_install_time'] = $_SERVER['REQUEST_TIME'];
					$lcoe_resource_fields['linuxcoe_profile_name'] = $lcoe_profile_name;
					$lcoe_resource->add($lcoe_resource_fields);
	
					break;
				}
				break;
	
			case 'remove':
				foreach($_REQUEST['identifier'] as $profile_name) {
					$lcoe_remove_profile_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/bin/openqrm-linuxcoe-manager remove $profile_name";
					$openqrm_server->send_command($lcoe_remove_profile_cmd);
					sleep($refresh_delay);
	
				}
				break;
	
			case 'update':
				foreach($_REQUEST['identifier'] as $profile_name) {
					$lcoe_profile_comment_param = htmlobject_request('lcoe_profile_comment');
					$lcoe_profile_comment = $lcoe_profile_comment_param[$profile_name];
					$filename = "$RootDir/plugins/linuxcoe/profiles/$profile_name/openqrm.info";
				    if (!$handle = fopen($filename, 'w+')) {
				    	$event->log("update", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-apply.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
						exit;
				    }
				    fwrite($handle, "$lcoe_profile_comment\n");
				    fclose($handle);

				}
				break;
	
		}
	}
}

// we check at every refresh if there is some new profiles available for unpacking
$lcoe_profile_check = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/bin/openqrm-linuxcoe-manager check";
$openqrm_server->send_command($lcoe_profile_check);











function linuxcoe_profile_manager() {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

	$table = new htmlobject_db_table('lcoe_profile_name');

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/linuxcoe/img/plugin.png\"> LinuxCOE Profile Manager</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select an LinuxCOE profile from the list below";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$arHead = array();

	$arHead['lcoe_profile_id'] = array();
	$arHead['lcoe_profile_id']['title'] ='Id';

	$arHead['lcoe_profile_name'] = array();
	$arHead['lcoe_profile_name']['title'] ='Name';

	$arHead['lcoe_profile_comment'] = array();
	$arHead['lcoe_profile_comment']['title'] ='Comment';

	$lcoe_profile_count=1;
	$arBody = array();
	$lcoe_profile_array = array();
	$lcoe_profile_dir = new Folder();
	if (is_dir("$RootDir/plugins/linuxcoe/profiles/")) {
		$lcoe_profile_dir->getFolders("$RootDir/plugins/linuxcoe/profiles/");
		foreach ($lcoe_profile_dir->folders as $lcoe_profile) {
				array_push($lcoe_profile_array, $lcoe_profile);
		}
	}
	
	foreach ($lcoe_profile_array as $lcoe) {
		// check if a comment exists
		if (file_exists("$RootDir/plugins/linuxcoe/profiles/$lcoe/openqrm.info")) {
			$lcoe_profile_comment_str = file_get_contents("$RootDir/plugins/linuxcoe/profiles/$lcoe/openqrm.info");
		}	
		$lcoe_profile_comment = htmlobject_input("lcoe_profile_comment[$lcoe]", array('value' => $lcoe_profile_comment_str), 'text');

		$arBody[] = array(
			'lcoe_profile_id' => $lcoe_profile_count,
			'lcoe_profile_name' => $lcoe,
			'lcoe_profile_comment' => $lcoe_profile_comment,
		);
		$lcoe_profile_count++;
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
		$table->bottom = array('select', 'update', 'remove');
		$table->identifier = 'lcoe_profile_name';
	}
	$table->max = $lcoe_profile_count;
	return $disp.$table->get_string();

}


function linuxcoe_select_resource($lcoe_profile_name) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

	$table = new htmlobject_db_table('resource_id');

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/linuxcoe/img/plugin.png\"> LinuxCOE Profile Manager</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please select a Resource to apply the LinuxCOE profile $lcoe_profile_name to :";
	$disp = $disp."<br>";

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

	$arBody = array();
	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_overview(0, 100, 'resource_id', 'ASC');

	foreach ($resource_array as $index => $resource_db) {
		// prepare the values for the array
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		$res_id = $resource->id;
		$resource_icon_default="/openqrm/base/img/resource.png";
		$state_icon="/openqrm/base/img/idle.png";
		if ($resource->id != 0) {
			// idle ?
			if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
				$arBody[] = array(
					'resource_state' => "<img src=$state_icon>",
					'resource_icon' => "<img width=24 height=24 src=$resource_icon_default><input type='hidden' name='lcoe_profile_name' value=\"$lcoe_profile_name\">",
					'resource_id' => $resource_db["resource_id"],
					'resource_hostname' => $resource_db["resource_hostname"],
					'resource_mac' => $resource_db["resource_mac"],
					'resource_ip' => $resource_db["resource_ip"],
				);

			}
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
		$table->bottom = array('apply');
		$table->identifier = 'resource_id';
		$table->identifier_disabled = array(0);
	}
	$table->max = $resource_tmp->get_count('all');
	
	return $disp.$table->get_string();
}




function linuxcoe_profile_applied($lcoe_profile_name, $lcoe_resource_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/linuxcoe/img/plugin.png\"> LinuxCOE Profile Manager</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."The LinuxCOE profile $lcoe_profile_name will now be applied to the resource $lcoe_resource_id.";
	$disp = $disp."<br>";
	$disp = $disp." It will fully automatic install the operation system as configured in the LinuxCOE profile.";
	$disp = $disp." After it is fully up and running please integrate it into openQRM via the local-server plugin";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;

}




$output = array();

switch ($step) {
	case 1:
		$output[] = array('label' => 'About', 'value' => linuxcoe_profile_manager());
		break;
	case 2:
		$output[] = array('label' => 'About', 'value' => linuxcoe_select_resource($lcoe_profile_name));
		break;
	case 3:
		$output[] = array('label' => 'About', 'value' => linuxcoe_profile_applied($lcoe_profile_name, $lcoe_resource_id));
		break;
	default:
		break;
}



echo htmlobject_tabmenu($output);

?>


