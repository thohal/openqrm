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
$image_id = htmlobject_request('image_id');
$aws_id = htmlobject_request('aws_id');


global $OPENQRM_SERVER_BASE_DIR;
// set ip
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
// set refresh timeout
$refresh_delay=1;
$refresh_loop_max=20;
// actions
if (!strlen($step)) {
    $step=1;
}
// AWS regions
$aws_region_eu_west_1 = "https://eu-west-1.ec2.amazonaws.com";
$aws_region_us_east_1 = "https://us-east-1.ec2.amazonaws.com";
$aws_region_default = $aws_region_eu_west_1;


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
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
		case 'save':
            show_progressbar();
            $aws_account_name = htmlobject_request('aws_account_name');
            $aws_java_home = htmlobject_request('aws_java_home');
            $aws_ec2_home = htmlobject_request('aws_ec2_home');
            $aws_ec2_private_key = htmlobject_request('aws_ec2_private_key');
            $aws_ec2_cert = htmlobject_request('aws_ec2_cert');
            $aws_ec2_url = htmlobject_request('aws_ec2_url');
            $aws_ec2_ssh_key = htmlobject_request('aws_ec2_ssh_key');

            // check user input
            if (!strlen($aws_account_name)) {
                $redir_msg = "AWS account name empty. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            if (!is_dir($aws_java_home)) {
                $redir_msg = "Java Home is not a directory. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            if (!is_dir($aws_ec2_home)) {
                $redir_msg = "EC2 Home is not a directory. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            if (!file_exists($aws_ec2_private_key)) {
                $redir_msg = "AWS Private key does not exist. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            if (!file_exists($aws_ec2_cert)) {
                $redir_msg = "AWS Certificate does not exist. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            if (!file_exists($aws_ec2_ssh_key)) {
                $redir_msg = "AWS ssh-key does not exist. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            switch ($aws_ec2_url) {
                case 'EU_WEST_1':
                    $aws_ec2_region = $aws_region_eu_west_1;
                    break;

                case 'US_EAST_1':
                    $aws_ec2_region = $aws_region_us_east_1;
                    break;

                default:
                    $aws_ec2_region = $aws_region_default;
                    break;
            }

            $aws = new aws();
            $fields = array();
            $fields["aws_id"] = openqrm_db_get_free_id('aws_id', $aws->_db_table);
            $fields['aws_account_name'] = $aws_account_name;
            $fields['aws_java_home'] = $aws_java_home;
            $fields['aws_ec2_home'] = $aws_ec2_home;
            $fields['aws_ec2_private_key'] = $aws_ec2_private_key;
            $fields['aws_ec2_cert'] = $aws_ec2_cert;
            $fields['aws_ec2_region'] = $aws_ec2_region;
            $fields['aws_ec2_ssh_key'] = $aws_ec2_ssh_key;
            $aws->add($fields);
            $redir_msg = "Created new AWS account configuration <br>";
            redirect($redir_msg, '', '');
            break;

		case 'remove':
            if (isset($_REQUEST['identifier'])) {
                show_progressbar();
                foreach($_REQUEST['identifier'] as $id) {
                    $aws = new aws();
                    $aws->remove($id);
                    $redir_msg .= "Removed AWS account configuration $id <br>";
                }
                redirect($redir_msg, '', '');
            }
            break;
	}
}




function aws_setup_account() {

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
	$aws_array = $aws_tmp->display_overview(0, $table->limit, $table->sort, $table->order);

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
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('remove');
		$table->identifier = 'aws_id';
	}
	$table->max = $aws_count;

    // create new aws account
    $aws_account_name = htmlobject_input('aws_account_name', array("value" => htmlobject_request('aws_account_name'), "label" => 'Account Name'), 'text', 20);
    $aws_java_home = htmlobject_input('aws_java_home', array("value" => htmlobject_request('aws_java_home'), "label" => 'Java Home (dir)'), 'text', 255);
    $aws_ec2_home = htmlobject_input('aws_ec2_home', array("value" => htmlobject_request('aws_ec2_home'), "label" => 'EC2 Home (dir)'), 'text', 255);
    $aws_ec2_private_key = htmlobject_input('aws_ec2_private_key', array("value" => htmlobject_request('aws_ec2_private_key'), "label" => 'AWS Private key (file)'), 'text', 255);
    $aws_ec2_cert = htmlobject_input('aws_ec2_cert', array("value" => htmlobject_request('aws_ec2_cert'), "label" => 'AWS Certificate (file)'), 'text', 255);
    $aws_ec2_ssh_key = htmlobject_input('aws_ec2_ssh_key', array("value" => htmlobject_request('aws_ec2_ssh_key'), "label" => 'SSH-Key (file)'), 'text', 255);
    $aws_ec2_url = "AWS Region <select name=\"aws_ec2_url\" size=\"1\"><option value=\"EU_WEST_1\">EU-WEST-1</option><option value=\"US_EAST_1\">US-EAST-1</option></select>";


   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'aws-setup-account.tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
		'aws_table' => $table->get_string(),
		'aws_account_name' => $aws_account_name,
		'aws_java_home' => $aws_java_home,
		'aws_ec2_home' => $aws_ec2_home,
		'aws_ec2_private_key' => $aws_ec2_private_key,
		'aws_ec2_cert' => $aws_ec2_cert,
		'aws_ec2_ssh_key' => $aws_ec2_ssh_key,
		'aws_ec2_url' => $aws_ec2_url,
        'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
$output[] = array('label' => 'AWS Accounts', 'value' => aws_setup_account());

echo htmlobject_tabmenu($output);

?>
