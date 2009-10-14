
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
$PuppetDir = $_SERVER["DOCUMENT_ROOT"].'/puppet-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special puppetuser class
require_once "$RootDir/plugins/puppet/class/puppetconfig.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'update':
			foreach($_REQUEST['identifier'] as $id) {
				// update in db
				$puppet_conf = new puppetconfig();
				$puppet_conf->get_instance_by_id($id);
				$key = $puppet_conf->key;
				$value = $_REQUEST[$key];
				$puppet_conf->set_value($id, $value);
				
				// in case autosigining changed
				if ($id == 1) {
					if (!strcmp($value, "true")) {
						$puppet_cli_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/puppet/bin/openqrm-puppet-manager auto_sign_true";
					} else {
						$puppet_cli_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/puppet/bin/openqrm-puppet-manager auto_sign_false";
					}	
					$openqrm_server->send_command($puppet_cli_command);
				}				
				
			}
			break;
	}
}



function puppet_config_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
    
	$table = new htmlobject_table_identifiers_checked('cc_id');
	$arHead = array();

	$arHead['cc_id'] = array();
	$arHead['cc_id']['title'] ='ID';

	$arHead['cc_key'] = array();
	$arHead['cc_key']['title'] ='Key';

	$arHead['cc_value'] = array();
	$arHead['cc_value']['title'] ='Value';

	$arBody = array();

	// db select
    $puppet_config_count=0;
	$cc_config = new puppetconfig();
	$cc_array = $cc_config->display_overview(0, 100, $table->sort, $table->order);
	$ident_array = array();
	foreach ($cc_array as $index => $cc) {
		$key = $cc["cc_key"];
		$value = $cc["cc_value"];
		// for now only allow some parameters to be edited
		if (!strcmp($key, "ca_auto_sign")) {
			$input_value="<input type=text name=$key value=$value size=20>";
		} else {
			$input_value="$value <input type=hidden name=$key value=$value size=20>";
		}
		$ident_array[] .= $cc["cc_id"];
		$arBody[] = array(
			'cc_id' => $cc["cc_id"],
			'cc_key' => $cc["cc_key"],
			'cc_value' => $input_value,
		);
        $puppet_config_count++;
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->identifier_checked = $ident_array;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update');
		$table->identifier = 'cc_id';
	}
	$table->max = $puppet_config_count;
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'puppet-config.tpl.php');
	$t->setVar(array(
        'puppet_config_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





$output = array();


$output[] = array('label' => 'Puppet Confguration', 'value' => puppet_config_manager());
echo htmlobject_tabmenu($output);
?>
