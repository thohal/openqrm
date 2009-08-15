
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

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
// special collectd classes
require_once "$RootDir/plugins/collectd/class/collectdconfig.class.php";
require_once "$RootDir/plugins/collectd/class/collectd.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;



function collectd_select() {
	global $OPENQRM_USER;
    global $RootDir;
	global $thisfile;
	$table = new htmlobject_db_table('collectd_id');

	$arHead = array();
	$arHead['collectd_state'] = array();
	$arHead['collectd_state']['title'] ='';

	$arHead['collectd_icon'] = array();
	$arHead['collectd_icon']['title'] ='';

	$arHead['collectd_id'] = array();
	$arHead['collectd_id']['title'] ='ID';

	$arHead['collectd_name'] = array();
	$arHead['collectd_name']['title'] ='Name';

	$arHead['collectd_resource_id'] = array();
	$arHead['collectd_resource_id']['title'] ='Res.ID';

	$arHead['collectd_resource_ip'] = array();
	$arHead['collectd_resource_ip']['title'] ='Ip';

	$arHead['collectd_graph'] = array();
	$arHead['collectd_graph']['title'] ='Graphs';

	$collectd_count=0;
	$arBody = array();
	$collectd_tmp = new appliance();
	$collectd_array = $collectd_tmp->display_overview(0, 100, 'appliance_id', 'ASC');

	foreach ($collectd_array as $index => $collectd_db) {
		$collectd_app = new appliance();
		$collectd_app->get_instance_by_id($collectd_db["appliance_id"]);
		$collectd_app_resources=$collectd_db["appliance_resources"];		
		$collectd_resource = new resource();
		$collectd_resource->get_instance_by_id($collectd_app_resources);

        // openqrm ?
        $appliance_name = $collectd_app->name;
        if ($collectd_app_resources == 0) {
            $appliance_name="openqrm";
        }

		// active or inactive
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";
		$resource_icon_default="/openqrm/base/img/resource.png";
        // graphs available already ?
        $graph_html = "$RootDir/plugins/collectd/graphs/".$appliance_name."/index.html";
        $graph_link = "/openqrm/base/plugins/collectd/graphs/".$appliance_name;
        if (file_exists($graph_html)) {
            $collectd_graph = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$graph_link\"><img src=\"img/graphs.png\" border=\"0\" width=\"30\" height=\"30\" alt=\"System Graphs\" title=\"System Graphs\"/></a>";
        } else {
            $collectd_graph = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"/openqrm/base/img/progress.gif\" width=\"30\" height=\"30\" alt=\"Collecting Data, Graphs will be available soon\" title=\"Collecting Data, Graphs will be available soon\"/>";
        }

		if ($collectd_app->stoptime == 0 || $collectd_app_resources == 0)  {
			$state_icon=$active_state_icon;
    		$collectd_count++;
            $arBody[] = array(
                'collectd_state' => "<img src=$state_icon>",
                'collectd_icon' => "<img width=24 height=24 src=$resource_icon_default>",
                'collectd_id' => $collectd_db["appliance_id"],
                'collectd_name' => $collectd_db["appliance_name"],
                'collectd_resource_id' => $collectd_resource->id,
                'collectd_resource_ip' => $collectd_resource->ip,
                'collectd_graph' => $collectd_graph,
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
	$table->max = $collectd_count;
	return $disp.$table->get_string();
}



$output = array();
$output[] = array('label' => 'Collectd Graphs', 'value' => collectd_select());
echo htmlobject_tabmenu($output);

?>
