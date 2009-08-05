<html>
<head>
<link type="text/css" rel="stylesheet" href="../css/calendar.css">
<link rel="stylesheet" type="text/css" href="../css/mycloud.css" />

</head>

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
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudtransaction.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudtransaction.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_REQUEST_TABLE;

// who are you ?
$auth_user = $_SERVER['PHP_AUTH_USER'];
global $auth_user;


function my_cloud_transactions() {

	global $OPENQRM_USER;
	global $thisfile;
	global $auth_user;
	$table = new htmlobject_table_identifiers_checked('ct_id');

	$disp = "<h1>My Cloud Transactions</h1>";
	$arHead = array();

	$arHead['ct_id'] = array();
	$arHead['ct_id']['title'] ='ID';

	$arHead['ct_time'] = array();
	$arHead['ct_time']['title'] ='Time';

	$arHead['ct_cr_id'] = array();
	$arHead['ct_cr_id']['title'] ='Request';

	$arHead['ct_ccu_charge'] = array();
	$arHead['ct_ccu_charge']['title'] ='Charge';

	$arHead['ct_ccu_balance'] = array();
	$arHead['ct_ccu_balance']['title'] ='Balance';

	$arHead['ct_reason'] = array();
	$arHead['ct_reason']['title'] ='Reason';

	$arHead['ct_comment'] = array();
	$arHead['ct_comment']['title'] ='Comment';

	$arBody = array();

	// db select
    $transaction_count=0;
	$cl_transaction = new cloudtransaction();
	$transaction_array = $cl_transaction->display_overview($table->offset, 1000, 'ct_id', 'DESC');
	foreach ($transaction_array as $index => $ct) {
		// user name
		$cu_tmp = new clouduser();
		$cu_tmp_id = $ct["ct_cu_id"];
		$cu_tmp->get_instance_by_id($cu_tmp_id);

		// only display our own transactions
		if (strcmp($cu_tmp->name, $auth_user)) {
			continue;
		}

        $transaction_count++;
		// format time
		$timestamp=$ct["ct_time"];
		$ct_time = date("d-m-Y H-i", $timestamp);
        $ct_charge = $ct["ct_ccu_charge"];
		// fill the array for the table
		$arBody[] = array(
			'ct_id' => $ct["ct_id"],
			'ct_time' => $ct_time,
			'ct_cr_id' => $ct["ct_cr_id"],
			'ct_ccu_charge' => "-$ct_charge",
			'ct_ccu_balance' => $ct["ct_ccu_balance"],
			'ct_reason' => $ct["ct_reason"],
			'ct_comment' => $ct["ct_comment"],
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
	$table->identifier = 'ct_id';
    $table->max = $transaction_count;
    $disp .= $table->get_string();
    $disp .= "<br><a href=\"javascript:window.print()\"><small>Print this page</small></a>";
	return $disp;
}


$cloudu = new clouduser();
$cloudu->get_instance_by_name($auth_user);
if ($cloudu->status == 1) {
	$output[] = array('label' => "MyCloudTransactions", 'value' => my_cloud_transactions());
} else {
	$output[] = array('label' => 'Your account has been disabled', 'value' => "");
}


echo htmlobject_tabmenu($output);

?>
