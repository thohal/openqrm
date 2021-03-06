<!doctype html>
<html lang="en">
<head>
	<title>AWS manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="aws.css" />
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
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special aws classe
require_once "$RootDir/plugins/aws/class/aws.class.php";

// post parameters
$step = htmlobject_request('step');
$instance_id = htmlobject_request('instance_id');
$image_id = htmlobject_request('image_id');
$aws_id = htmlobject_request('aws_id');


global $OPENQRM_SERVER_BASE_DIR;
// set ip
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
// set refresh timeout
$refresh_delay=1;
$refresh_loop_max=40;
// actions
if (!strlen($step)) {
    $step=1;
}



function redirect($strMsg, $currenttab = 'tab0', $url = '', $step, $aws_id) {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&step='.$step.'&aws_id='.$aws_id;
	}
	// using meta refresh because of the java-script in the header	
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



if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    show_progressbar();
                    $aws = new aws();
                    $aws->get_instance_by_id($id);
                    $aws_java_home = $aws->java_home;
                    $aws_ec2_home = $aws->ec2_home;
                    $aws_ec2_private_key = $aws->ec2_private_key;
                    $aws_ec2_cert = $aws->ec2_cert;
                    $aws_ec2_region = $aws->ec2_region;
                    
                    // remove current log
                    $describe_instances = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aws/web/aws-stat/".$id.".ec2_describe_instances.log";
                    if (file_exists($describe_instances)) {
                        unlink($describe_instances);
                    }
                    // send command
                    $aws_run_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aws/bin/openqrm-aws describe_instances -i ".$id." -j ".$aws_java_home." -e ".$aws_ec2_home." -p ".$aws_ec2_private_key." -c ".$aws_ec2_cert." -u ".$aws_ec2_region;
                    $openqrm_server->send_command($aws_run_command);
                    // and wait for the resulting statfile
                    if (!wait_for_statfile($describe_instances)) {
                        $redir_msg = "Error during ec2-describe-instances command on AWS ! Please check the AWS Account configuration";
                    } else {
                        $redir_msg = "Displaying active instances on AWS";
                    }
                    redirect($redir_msg, '', '', 2, $id);
                    break;
                }
            }
			break;

		case 'get':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $instance_id = $id;
                    $aws_id = htmlobject_request('aws_id');
                    $step=3;
                    break;
                }
            }
			break;

		case 'put':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $image_id = $id;
                    $instance_id = htmlobject_request('instance_id');
                    $aws_id = htmlobject_request('aws_id');
                    $step=4;
                    break;
                }
            }
			break;



	}
}




function aws_select_account() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

    $table = new htmlobject_table_builder('aws_id', '', '', '', 'select');
	$arHead = array();

	$arHead['aws_id'] = array();
	$arHead['aws_id']['title'] ='Id';

	$arHead['aws_account_name'] = array();
	$arHead['aws_account_name']['title'] ='Name';

	$arHead['aws_ec2_home'] = array();
	$arHead['aws_ec2_home']['title'] ='EC2 Home';

	$arHead['aws_ec2_region'] = array();
	$arHead['aws_ec2_region']['title'] ='Region';

	$aws_count=1;
	$aws_tmp = new aws();
	$aws_array = $aws_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	$arBody = array();
	foreach ($aws_array as $index => $aws_db) {
		$arBody[] = array(
			'aws_id' => $aws_db["aws_id"],
			'aws_account_name' => $aws_db["aws_account_name"],
			'aws_ec2_home' => $aws_db["aws_ec2_home"],
			'aws_ec2_region' => $aws_db["aws_ec2_region"],
		);
		$aws_count++;
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
		$table->identifier = 'aws_id';
	}
	$table->max = $aws_tmp->get_count();
    // is there at least one account setup already ?
    if ($aws_count == 0) {
        $aws_account_hint = "<h4>No AWS account configured yet.<br>Click <a href='aws-setup.php'><strong>here</strong></a> to setup an AWS account in openQRM</h4>";
    }
   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aws-select-account.tpl.php');
	$t->setVar(array(
		'aws_table' => $table->get_string(),
		'aws_account_hint' => $aws_account_hint,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function aws_select_instance($aws_id) {
	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$aws = new aws();
	$aws->get_instance_by_id($aws_id);
	$aws_account_name = $aws->account_name;
	$describe_instances = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aws/web/aws-stat/".$aws_id.".ec2_describe_instances.log";

    $table = new htmlobject_table_builder('instance_id', '', '', '', 'select');
	$arHead = array();

	$arHead['instance_id'] = array();
	$arHead['instance_id']['title'] ='Id';

	$arHead['instance_hostname'] = array();
	$arHead['instance_hostname']['title'] ='Hostname';

    $arHead['instance_ami'] = array();
	$arHead['instance_ami']['title'] ='AMI';

    $arHead['instance_state'] = array();
	$arHead['instance_state']['title'] ='State';

	$instance_count=0;
	$arBody = array();
    // be sure it is there, otherwise wait for it
    if (!wait_for_statfile($describe_instances)) {
        $redir_msg = "Error getting informations from AWS ! Please check the Event-Log";
        redirect($redir_msg, '', '', '', '');
    }
	if (file_exists($describe_instances)) {
		$aws_conf_content=file($describe_instances);
		foreach ($aws_conf_content as $value => $image) {
			$instance_parameter = explode("@", $image);
			$instance_type = $instance_parameter[0];
			$instance_id = $instance_parameter[1];
			$instance_hostname = $instance_parameter[2];
			$instance_ami = $instance_parameter[3];
			$instance_state = $instance_parameter[5];

            $arBody[] = array(
                'instance_id' => "$instance_id",
                'instance_ami' => "$instance_ami",
                'instance_hostname' => "$instance_hostname",
                'instance_state' => "$instance_state",
            );
            $instance_count++;
		}
	} else {
        $redir_msg = "Could not connect to AWS using account $aws_account_name ! Please check the Event-Log";
        redirect($redir_msg, '', '', '', '');

	}

    $table->add_headrow("<input type=\"hidden\" name=\"aws_id\" value=\"$aws_id\"><input type=\"hidden\" name=\"step\" value=\"2\">");
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
    $table->autosort = true;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('get');
		$table->identifier = 'instance_id';
	}
	$table->max = $instance_count;
    // are there any active aws instances ? if not give a hint
    if ($instance_count == 0) {
        $aws_start_instance_hint = "<h4>There are no active AWS EC2 Instances available";
        $aws_start_instance_hint .= "<br>You can launch EC2 Instances via the <a href=\"https://console.aws.amazon.com/ec2/\" target=\"_BLANK\"><strong>AWS Web-Concole</strong></a></h4>";
    }

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aws-get.tpl.php');
	$t->setVar(array(
		'aws_get_table' => $table->get_string(),
        'aws_start_instance_hint' => $aws_start_instance_hint,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function image_storage_select($instance_id, $aws_id) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$image_tmp = new image();
	$image_icon = "/openqrm/base/img/image.png";
    // nfs table
    $table = new htmlobject_table_builder('image_id', '', '', '', 'nfs');

	$arHead = array();
	$arHead['image_icon'] = array();
	$arHead['image_icon']['title'] ='';
	$arHead['image_icon']['sortable'] = false;

	$arHead['image_id'] = array();
	$arHead['image_id']['title'] ='ID';

	$arHead['image_name'] = array();
	$arHead['image_name']['title'] ='Name';

	$arHead['image_version'] = array();
	$arHead['image_version']['title'] ='Version';

	$arHead['image_type'] = array();
	$arHead['image_type']['title'] ='Deployment Type';

	$arHead['image_comment'] = array();
	$arHead['image_comment']['title'] ='Comment';

    $image_nfs_count=0;
	$arBody = array();
	$image_array = $image_tmp->display_overview_per_type("nfs-deployment", $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_db["image_type"]);
        $arBody[] = array(
            'image_icon' => "<img width=20 height=20 src=$image_icon>",
            'image_id' => $image_db["image_id"],
            'image_name' => $image_db["image_name"],
            'image_version' => $image_db["image_version"],
            'image_type' => "$image_deployment->description  <input type=\"hidden\" name=\"aws_id\" value=\"$aws_id\"><input type=\"hidden\" name=\"instance_id\" value=\"$instance_id\">",
            'image_comment' => $image_db["image_comment"],
        );
        $image_nfs_count++;

	}

    // are there any active aws instances ? if not give a hint
    if ($image_nfs_count == 0) {
        $create_nfs_image_hint = "<h4>There are no NFS-Server-Images available.";
        $create_nfs_image_hint .= " Please create a <a href=\"/openqrm/base/server/image/image-new.php?currenttab=tab1\"><strong>NFS-Server-Image</strong></a></h4>";
    }

    $table->add_headrow("<input type=\"hidden\" name=\"aws_id\" value=\"$aws_id\"><input type=\"hidden\" name=\"step\" value=\"3\">");
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
    $table->autosort = true;
	$table->identifier_type = "radio";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('put');
		$table->identifier = 'image_id';
	}
    $table->max = $image_tmp->get_count_per_type("nfs-deployment");


    // lvm nfs table
    $table1 = new htmlobject_table_builder('image_id', '', '', '', 'lvmnfs');

	$arHead1 = array();
	$arHead1['image_icon'] = array();
	$arHead1['image_icon']['title'] ='';
	$arHead1['image_icon']['sortable'] = false;

	$arHead1['image_id'] = array();
	$arHead1['image_id']['title'] ='ID';

	$arHead1['image_name'] = array();
	$arHead1['image_name']['title'] ='Name';

	$arHead1['image_version'] = array();
	$arHead1['image_version']['title'] ='Version';

	$arHead1['image_type'] = array();
	$arHead1['image_type']['title'] ='Deployment Type';

	$arHead1['image_comment'] = array();
	$arHead1['image_comment']['title'] ='Comment';

    $image_lvmnfs_count=0;
	$arBody1 = array();
	$image_array = $image_tmp->display_overview_per_type("lvm-nfs-deployment", $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_db["image_type"]);
        $arBody1[] = array(
            'image_icon' => "<img width=20 height=20 src=$image_icon>",
            'image_id' => $image_db["image_id"],
            'image_name' => $image_db["image_name"],
            'image_version' => $image_db["image_version"],
            'image_type' => "$image_deployment->description",
            'image_comment' => $image_db["image_comment"],
        );
        $image_lvmnfs_count++;

	}

    // are there any active aws instances ? if not give a hint
    if ($image_lvmnfs_count == 0) {
        $create_lvn_nfs_image_hint = "<h4>There are no LVM-NFS-Server-Images available.";
        $create_lvn_nfs_image_hint .= " Please create a <a href=\"/openqrm/base/server/image/image-new.php?currenttab=tab1\"><strong>LVM-NFS Server-Image</strong></a></h4>";
    }

    $table1->add_headrow("<input type=\"hidden\" name=\"aws_id\" value=\"$aws_id\"><input type=\"hidden\" name=\"instance_id\" value=\"$instance_id\"><input type=\"hidden\" name=\"step\" value=\"3\">");
	$table1->id = 'Tabelle';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
    $table1->autosort = true;
	$table1->identifier_type = "radio";
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	if ($OPENQRM_USER->role == "administrator") {
		$table1->bottom = array('put');
		$table1->identifier = 'image_id';
	}
    $table1->max = $image_tmp->get_count_per_type("lvm-nfs-deployment");

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aws-put.tpl.php');
	$t->setVar(array(
		'image_nfs_table' => $table->get_string(),
        'create_nfs_image_hint' => $create_nfs_image_hint,
		'image_lvm_nfs_table' => $table1->get_string(),
        'create_lvn_nfs_image_hint' => $create_lvn_nfs_image_hint,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}




function aws_final($image_id, $instance_id, $aws_id) {
	global $openqrm_server;
	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;
	// here we execute the request !
	$image_count=1;
	$aws = new aws();
	$aws->get_instance_by_id($aws_id);
    $aws_java_home = $aws->java_home;
    $aws_ec2_home = $aws->ec2_home;
    $aws_ec2_private_key = $aws->ec2_private_key;
    $aws_ec2_cert = $aws->ec2_cert;
    $aws_ec2_region = $aws->ec2_region;
    $aws_ec2_ssh_key = $aws->ec2_ssh_key;

    $image = new image();
    $image->get_instance_by_id($image_id);
    $storage = new storage();
    $storage->get_instance_by_id($image->storageid);
    $resource = new resource();
    $resource->get_instance_by_id($storage->resource_id);
    $image_store = $resource->ip.":".$image->rootdevice;
    // send command
    $aws_run_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aws/bin/openqrm-aws import_instance -i ".$aws_id." -j ".$aws_java_home." -e ".$aws_ec2_home." -p ".$aws_ec2_private_key." -c ".$aws_ec2_cert." -u ".$aws_ec2_region." -k ".$aws_ec2_ssh_key." -x ".$instance_id." -s ".$image_store;
    // send command
	$openqrm_server->send_command($aws_run_command);

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aws-final.tpl.php');
	$t->setVar(array(
        'image_id' => $image_id,
        'image_name' => $image->name,
		'instance_id' => $instance_id,
        'aws_id' => $aws_id,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
switch ($step) {
	case 1:
		$output[] = array('label' => 'AWS EC2 Import', 'value' => aws_select_account());
		break;
	case 2:
		$output[] = array('label' => 'AWS EC2 Import', 'value' => aws_select_instance($aws_id));
		break;
	case 3:
		$output[] = array('label' => 'AWS EC2 Import', 'value' => image_storage_select($instance_id, $aws_id));
		break;
	case 4:
		$output[] = array('label' => 'AWS EC2 Import', 'value' => aws_final($image_id, $instance_id, $aws_id));
		break;
	default:
		$output[] = array('label' => 'AWS EC2 Import', 'value' => aws_select_account());
		break;
}

echo htmlobject_tabmenu($output);

?>
