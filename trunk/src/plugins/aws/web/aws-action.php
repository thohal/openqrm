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
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "aws-action", "Un-Authorized access to aws-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$aws_command = htmlobject_request('aws_command');

// main
$event->log("$aws_command", $_SERVER['REQUEST_TIME'], 5, "aws-action", "Processing aws command $aws_command", "", "", 0, 0, 0);
switch ($aws_command) {

    case 'init':
        // this command creates the following table
        // -> aws_locations
        // aws_id INT(5)
        // aws_account_name VARCHAR(20)
        // aws_account_number VARCHAR(20)
        // aws_java_home VARCHAR(255)
        // aws_ec2_home VARCHAR(255)
        // aws_ami_home VARCHAR(255)
        // aws_ec2_private_key VARCHAR(255)
        // aws_ec2_cert VARCHAR(255)
        // aws_ec2_region VARCHAR(255)
        // aws_ec2_ssh_key VARCHAR(255)
        // aws_access_key VARCHAR(255)
        // aws_secret_access_key VARCHAR(255)
        $create_aws_table = "create table openqrm_aws(aws_id INT(5), aws_account_name VARCHAR(20), aws_account_number VARCHAR(20), aws_java_home VARCHAR(255), aws_ec2_home VARCHAR(255), aws_ami_home VARCHAR(255), aws_ec2_private_key VARCHAR(255), aws_ec2_cert VARCHAR(255), aws_ec2_region VARCHAR(255), aws_ec2_ssh_key VARCHAR(255), aws_access_key VARCHAR(255), aws_secret_access_key VARCHAR(255))";
        $db=openqrm_get_db_connection();
        $recordSet = &$db->Execute($create_aws_table);

        $db->Close();
        break;

    case 'uninstall':
        $drop_aws_table = "drop table openqrm_aws";
        $db=openqrm_get_db_connection();
        $recordSet = &$db->Execute($drop_aws_table);
        $db->Close();
        break;


    default:
        $event->log("$aws_command", $_SERVER['REQUEST_TIME'], 3, "aws-action", "No such event command ($aws_command)", "", "", 0, 0, 0);
        break;


}

?>
