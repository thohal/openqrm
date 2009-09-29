
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="cloud.css" />

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


$iptables = $_REQUEST['ig_id'];


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special clouduser class
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudnat.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $CLOUD_IPGROUP_TABLE;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

// gather iptables parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cn_", 3) == 0) {
		$cloudnat_fields[$key] = $value;
	}
}


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'update':
           if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $upnat = new cloudnat();
                    $new_internal = htmlobject_request('cn_internal_network');
                    $new_external = htmlobject_request('cn_external_network');
                    $up_entry['cn_internal_net'] = $new_internal;
                    $up_entry['cn_external_net'] = $new_external;
                    $upnat->update($id, $up_entry);
                    $strMsg = "Updated Cloud NAT table internal : $new_internal external $new_external<br>";
                    redirect($strMsg, tab0);
                }
           }
			break;

	}
}






function cloud_nat_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	$table = new htmlobject_table_identifiers_checked('ip_id');
	$arHead = array();

	$arHead['cn_id'] = array();
	$arHead['cn_id']['title'] ='ID';

	$arHead['cn_internal_net'] = array();
	$arHead['cn_internal_net']['title'] ='Internal Network';

	$arHead['cn_external_net'] = array();
	$arHead['cn_external_net']['title'] ='External Network';

	$arBody = array();

	// db select
	$nat = new cloudnat();
    // check if we have the initial entry, if not create it
    if ($nat->is_id_free(1)) {
        $init_entry['cn_id'] = 1;
        $init_entry['cn_internal_net'] = "0.0.0.0";
        $init_entry['cn_external_net'] = "0.0.0.0";
        $nat->add($init_entry);
    }
    // display
    $ip_array = array();
	$ip_array = $nat->display_overview(0, 1, 'cn_id', 'ASC');
	foreach ($ip_array as $index => $ipg) {
        $internal=$ipg["cn_internal_net"];
        $external=$ipg["cn_external_net"];
		$arBody[] = array(
			'cn_id' => $ipg["cn_id"],
			'cn_internal_net' => htmlobject_input('cn_internal_network', array("value" => $internal, "label" => 'Internal Net'), 'text', 20),
			'cn_external_net' => htmlobject_input('cn_external_network', array("value" => $external, "label" => 'External Net'), 'text', 20),
		);
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'cn_id';
	}
	$table->max = 1;

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-nat-manager-tpl.php');
	$t->setVar(array(
		'cloud_nat_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
$output[] = array('label' => "Cloud NAT", 'value' => cloud_nat_manager());
echo htmlobject_tabmenu($output);

?>
