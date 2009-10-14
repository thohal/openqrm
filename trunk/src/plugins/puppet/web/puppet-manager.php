<!doctype html>
<html lang="en">
<head>
	<title>Puppet manager</title>
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
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special puppet classes
require_once "$RootDir/plugins/puppet/class/puppetconfig.class.php";
require_once "$RootDir/plugins/puppet/class/puppet.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
$refresh_delay=1;
$refresh_loop_max=20;


function redirect($strMsg, $puppet_id) {
	global $thisfile;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redir=yes&puppet_id='.$puppet_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
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



// action !
if(htmlobject_request('redir') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'update':
                show_progressbar();
                $puppet_id = htmlobject_request('puppet_id');
                // get the appliance name
                $appliance = new appliance();
                $appliance->get_instance_by_id($puppet_id);
                $appliance_name = $appliance->name;
                $puppet_groups_to_activate_array = array();
                $identifier_array = htmlobject_request('identifier');
                $puppet = new puppet();
                // clean up all current puppet groups from the appliance
                $puppet->remove_appliance($appliance_name);
                // set new groups if any
                if (is_array($identifier_array)) {
                    foreach($identifier_array as $puppet_group) {
                        $puppet_groups_to_activate_array[] .= $puppet_group;
                    }
                    $puppet->set_groups($appliance_name, $puppet_groups_to_activate_array);
                    $strMsg .="Updated Puppet groups for appliance $appliance_name<br>";
                } else {
                    $strMsg .="Removed Puppet groups from appliance $appliance_name<br>";
                }
                redirect($strMsg, $puppet_id);
                break;
        }
    }
}



function puppet_select() {

	global $OPENQRM_USER;
	global $thisfile;

    $table = new htmlobject_table_builder('appliance_id', '', '', '', 'select');

	$arHead = array();
	$arHead['appliance_state'] = array();
	$arHead['appliance_state']['title'] ='';
	$arHead['appliance_state']['sortable'] = false;

	$arHead['appliance_icon'] = array();
	$arHead['appliance_icon']['title'] ='';
	$arHead['appliance_icon']['sortable'] = false;

	$arHead['appliance_id'] = array();
	$arHead['appliance_id']['title'] ='ID';

	$arHead['appliance_name'] = array();
	$arHead['appliance_name']['title'] ='Name';

	$arHead['appliance_resource_id'] = array();
	$arHead['appliance_resource_id']['title'] ='Res.ID';
	$arHead['appliance_resource_id']['sortable'] = false;

	$arHead['appliance_resource_ip'] = array();
	$arHead['appliance_resource_ip']['title'] ='Ip';
	$arHead['appliance_resource_ip']['sortable'] = false;

	$arHead['appliance_comment'] = array();
	$arHead['appliance_comment']['title'] ='Comment';

	$puppet_count=0;
	$arBody = array();
	$puppet_tmp = new appliance();
	$puppet_array = $puppet_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($puppet_array as $index => $puppet_db) {
		$puppet_app = new appliance();
		$puppet_app->get_instance_by_id($puppet_db["appliance_id"]);
		$puppet_app_resources=$puppet_db["appliance_resources"];		
		$puppet_resource = new resource();
		$puppet_resource->get_instance_by_id($puppet_app_resources);

		$puppet_count++;
		// active or inactive
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";
		$resource_icon_default="/openqrm/base/img/resource.png";
		if ($puppet_app->stoptime == 0 || $puppet_app_resources == 0)  {
			$state_icon=$active_state_icon;
		} else {
			$state_icon=$inactive_state_icon;
		}

		$arBody[] = array(
			'appliance_state' => "<img src=$state_icon>",
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'appliance_id' => $puppet_db["appliance_id"],
			'appliance_name' => $puppet_db["appliance_name"],
			'appliance_resource_id' => $puppet_resource->id,
			'appliance_resource_ip' => $puppet_resource->ip,
			'appliance_comment' => $puppet_db["appliance_comment"],
		);
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
		$table->identifier = 'appliance_id';
	}
    $table->max = $puppet_tmp->get_count();
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'puppet-select.tpl.php');
	$t->setVar(array(
        'puppet_appliance_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function puppet_display($puppet_id) {

	global $OPENQRM_USER;
	global $RootDir;
	global $thisfile;

	$table = new htmlobject_db_table('puppet_name');
	$puppet_group_array = array();
	$puppet = new puppet();
	$puppet_group_array = $puppet->get_available_groups();
	// get the appliance name
	$appliance = new appliance();
	$appliance->get_instance_by_id($puppet_id);
	$appliance_name = $appliance->name;
	$appliance_domain = $puppet->get_domain();
	// get the enabled groups
	$appliance_puppet_groups = array();
	$appliance_puppet_groups = $puppet->get_groups($appliance_name);

	$arHead = array();

	$arHead['puppet_id'] = array();
	$arHead['puppet_id']['title'] ='ID';

	$arHead['puppet_name'] = array();
	$arHead['puppet_name']['title'] ='Name';

	$arHead['puppet_info'] = array();
	$arHead['puppet_info']['title'] ='Info';

	foreach ($puppet_group_array as $index => $puppet_g) {
		$puid=$index+1;
		$puppet_info = $puppet->get_group_info($puppet_g);
		$arBody[] = array(
			'puppet_id' => $puid,
			'puppet_name' => $puppet_g,
			'puppet_info' => $puppet_info,
		);
	}

    $table->add_headrow("<input type=\"hidden\" name=\"puppet_id\" value=\"$puppet_id\">");
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->identifier_checked = $appliance_puppet_groups;
    $table->autosort = true;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'puppet_name';
	}
	$table->max = $puppet_count;
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'puppet-apply.tpl.php');
	$t->setVar(array(
        'puppet_groups_table' => $table->get_string(),
        'appliance_name' => $appliance_name.".".$appliance_domain,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


$output = array();
$puppet_id = $_REQUEST["puppet_id"];
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Puppet Manager', 'value' => puppet_display($id));
			}
			break;
		case 'update':
			$output[] = array('label' => 'Puppet Manager', 'value' => puppet_display($puppet_id));
			break;
	}
} else if (strlen($puppet_id)) {
	$output[] = array('label' => 'Puppet Manager', 'value' => puppet_display($puppet_id));
} else  {
	$output[] = array('label' => 'Puppet Manager', 'value' => puppet_select());
}

echo htmlobject_tabmenu($output);

?>
