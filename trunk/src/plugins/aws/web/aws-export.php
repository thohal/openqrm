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
                    redirect($redir_msg, '', '', 2, $id);
                    break;
                }
            }
			break;

		case 'next':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $image_id = $id;
                    $aws_id = htmlobject_request('aws_id');
                    $step=3;
                    break;
                }
                break;
            }
            $redir_msg = "No Server Image selected. Skipping export ...";
            redirect($redir_msg, '', '', 1, 0);
			break;

		case 'export':
            $image_id = htmlobject_request('image_id');
            $aws_id = htmlobject_request('aws_id');
            $aws_ami_name = htmlobject_request('aws_ami_name');
            $aws_ami_size = htmlobject_request('aws_ami_size');
            $aws_ami_arch = htmlobject_request('aws_ami_arch');
            $step=4;
            if (!strlen($image_id)) {
                $step=1;
                $redir_msg = "No Server Image selected. Skipping export ...";
                redirect($redir_msg, '', '', 1, 0);
            }
            if (strlen($aws_ami_name) < 8) {
                $step=1;
                $redir_msg = "AMI Name empty or too short (min. 8 character). Skipping export ...";
                redirect($redir_msg, '', '', 1, 0);
            }
			break;

	}
}




function aws_select_account() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$table = new htmlobject_db_table('aws_id');

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
	$aws_array = $aws_tmp->display_overview(0, $table->limit, "aws_id", $table->order);

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
	$table->max = $aws_count;
    // is there at least one account setup already ?
    if ($aws_count == 1) {
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





function image_storage_select($aws_id) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$image_tmp = new image();
	$table = new htmlobject_db_table('image_id');

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

	$arHead['image_edit'] = array();
	$arHead['image_edit']['title'] ='';
	$arHead['image_edit']['sortable'] = false;
	if(strtolower(OPENQRM_USER_ROLE_NAME) != 'administrator') {
		$arHead['image_edit']['hidden'] = true;
	}

	$arBody = array();
	$image_array = $image_tmp->display_overview(1, $table->limit, $table->sort, $table->order);
	$image_icon = "/openqrm/base/img/image.png";
    $image_count = 1;
	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_db["image_type"]);

		// for now we only support nfs-images
		if ((!strcmp($image_deployment->type, "nfs-deployment")) || (!strcmp($image_deployment->type, "lvm-nfs-deployment"))) {

			$arBody[] = array(
				'image_icon' => "<img width=20 height=20 src=$image_icon>",
				'image_id' => $image_db["image_id"],
				'image_name' => $image_db["image_name"],
				'image_version' => $image_db["image_version"],
				// use the image_type to transport image_id + aws_id
				'image_type' => "$image_deployment->description  <input type=\"hidden\" name=\"aws_id\" value=\"$aws_id\">",
				'image_comment' => $image_db["image_comment"],
			);
            $image_count++;
		}
	}

    // are there any active aws instances ? if not give a hint
    if ($image_count == 1) {
        $create_image_hint = "<h4>There are no (NFS- or LVM-NFS based) openQRM Server-Images available";
        $create_image_hint .= "<br>Please create a <a href=\"/openqrm/base/server/image/image-new.php?currenttab=tab1\"><strong>NFS- or LVM-NFS Server-Image</strong></a></h4>";
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
		$table->bottom = array('next');
		$table->identifier = 'image_id';
	}
	$table->max = count($image_array);
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aws-export.tpl.php');
	$t->setVar(array(
		'image_put_table' => $table->get_string(),
        'create_image_hint' => $create_image_hint,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}






function aws_ami_setup($image_id, $aws_id) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

    $hidden_aws_id = "<input type=\"hidden\" name=\"aws_id\" value=\"$aws_id\">";
    $hidden_image_id = "<input type=\"hidden\" name=\"image_id\" value=\"$image_id\">";
    $aws_ami_name = htmlobject_input('aws_ami_name', array("value" => htmlobject_request('aws_ami_name'), "label" => 'AMI Name'), 'text', 20);
    $aws_ami_size = "AMI Size <select name=\"aws_ami_size\" size=\"1\"><option value=\"500\">500 MB</option><option value=\"1000\">1 GB</option><option value=\"2000\">2 GB</option><option value=\"5000\">5 GB</option><option value=\"10000\">10 GB</option></select>";
    $aws_ami_arch = "AMI Arch <select name=\"aws_ami_arch\" size=\"1\"><option value=\"x86_64\">x86_64</option><option value=\"i386\">i386</option></select>";

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aws-export-setup.tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
		'hidden_aws_id' => $hidden_aws_id,
		'hidden_image_id' => $hidden_image_id,
		'aws_ami_name' => $aws_ami_name,
		'aws_ami_size' => $aws_ami_size,
		'aws_ami_arch' => $aws_ami_arch,
        'submit_save' => htmlobject_input('action', array("value" => 'export', "label" => 'export'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}





function aws_export_final($image_id, $aws_id, $aws_ami_name, $aws_ami_size, $aws_ami_arch) {
	global $openqrm_server;
	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;
	// here we execute the request !
	$image_count=1;
	$aws = new aws();
	$aws->get_instance_by_id($aws_id);
    $aws_java_home = $aws->java_home;
    $aws_account_number = $aws->account_number;
    $aws_ec2_home = $aws->ec2_home;
    $aws_ami_home = $aws->ami_home;
    $aws_ec2_private_key = $aws->ec2_private_key;
    $aws_ec2_cert = $aws->ec2_cert;
    $aws_ec2_region = $aws->ec2_region;
    $aws_ec2_ssh_key = $aws->ec2_ssh_key;
    $aws_access_key = $aws->access_key;
    $aws_secret_access_key = $aws->secret_access_key;

    $aws_s3_bucket = $aws_ami_name;

    $image = new image();
    $image->get_instance_by_id($image_id);
    $storage = new storage();
    $storage->get_instance_by_id($image->storageid);
    $resource = new resource();
    $resource->get_instance_by_id($storage->resource_id);
    $image_store = $resource->ip.":".$image->rootdevice;
    // send command
    $aws_run_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/aws/bin/openqrm-aws export_image -i ".$aws_id." -j ".$aws_java_home." -e ".$aws_ec2_home." -a ".$aws_ami_home." -p ".$aws_ec2_private_key." -c ".$aws_ec2_cert." -u ".$aws_ec2_region." -k ".$aws_ec2_ssh_key." -s ".$image_store." -m ".$aws_ami_size." -n ".$aws_ami_name." -r ".$aws_ami_arch." -b ".$aws_s3_bucket." -w ".$aws_account_number." -y ".$aws_access_key." -z ".$aws_secret_access_key;
    // send command
	$openqrm_server->send_command($aws_run_command);

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aws-export-final.tpl.php');
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
		$output[] = array('label' => 'AWS EC2 Export', 'value' => aws_select_account());
		break;
	case 2:
		$output[] = array('label' => 'AWS EC2 Export', 'value' => image_storage_select($aws_id));
		break;
	case 3:
		$output[] = array('label' => 'AWS EC2 Export', 'value' => aws_ami_setup($image_id, $aws_id));
		break;
	case 4:
		$output[] = array('label' => 'AWS EC2 Export', 'value' => aws_export_final($image_id, $aws_id, $aws_ami_name, $aws_ami_size, $aws_ami_arch));
		break;
	default:
		$output[] = array('label' => 'AWS EC2 Export', 'value' => aws_select_account());
		break;
}

echo htmlobject_tabmenu($output);

?>
