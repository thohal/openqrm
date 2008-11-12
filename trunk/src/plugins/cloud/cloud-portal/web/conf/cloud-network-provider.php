<?php

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
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudipgroup.class.php";
require_once "$RootDir/plugins/cloud/class/cloudiptables.class.php";
global $OPENQRM_SERVER_BASE_DIR;


// example request 
// http://[openqrm-server-ip]/cloud-portal/conf/cloud-network-provider.php?appliance_id=1&howmany=4


function provide_ip_config($appliance_id, $howmany) {

	$iptable = new cloudiptables();
	$ip_ids_arr = $iptable->get_all_ids();
	$loop = 0;
	// still need to get this
	$cr_id = 0;
	
	foreach($ip_ids_arr as $id_arr) {
		foreach($id_arr as $id) {
			$ipt = new cloudiptables();
			$ipt->get_instance_by_id($id);
			// check if the ip is free
			if (($ipt->ip_active == 1) && ($ipt->ip_appliance_id == 0)) {
				$loop++;
				echo "$ipt->ip_address:$ipt->ip_subnet:$ipt->ip_gateway:$ipt->ip_dns1:$ipt->ip_dns2\n";
				$ipt->activate($id, false);
				$ipt->assign_to_appliance($id, $appliance_id, $cr_id);
				if ($loop == $howmany) {
					return;
				}
			}
		}
	}
}


$appliance_id = $_REQUEST['appliance_id'];
$howmany = $_REQUEST['howmany'];
if ((strlen($appliance_id)) && (strlen($howmany))) {
	provide_ip_config($appliance_id, $howmany);
}

?>
