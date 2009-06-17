<!doctype html>
<html lang="en">
<head>
	<title>Citrix Authentication Manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="citrix.css" />
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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=1;
$refresh_loop_max=40;

$citrix_server_id = htmlobject_request('citrix_server_id');
$citrix_server_ip = htmlobject_request('citrix_server_ip');
$citrix_server_user = htmlobject_request('citrix_server_user');
$citrix_server_password = htmlobject_request('citrix_server_password');
$auth_action = htmlobject_request('auth_action');

// place for the citrix stat files
$CitrixDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/plugins/citrix/citrix-stat';


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
    global $thisfile;
    global $citrix_server_id;
    if($url == '') {
        $url = 'citrix-manager.php?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&citrix_server_id='.$citrix_server_id;
    } else {
        $url = $url.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&citrix_server_id='.$citrix_server_id.'&citrix_server_ip='.$citrix_server_ip;
    }
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



// Dom0 actions
if(htmlobject_request('auth_action') != '') {
    switch ($auth_action) {
		case 'authenticate':
            if (!strlen($citrix_server_user)) {
                $strMsg .= "Citrix XenServer user not set. Not setting/updating authentication!";
                redirect($strMsg, "tab0", $thisfile);
            }
            if (!strlen($citrix_server_password)) {
                $strMsg .= "Citrix XenServer password not set. Not setting/updating authentication!";
                redirect($strMsg, "tab0", $thisfile);
            }
            if (!strlen($citrix_server_ip)) {
                $strMsg .= "Citrix XenServer server-ip not set. Not setting/updating authentication!";
                redirect($strMsg, "tab0", $thisfile);
            }
            if (!strlen($citrix_server_id)) {
                $strMsg .= "Citrix XenServer server-id not set. Not setting/updating authentication!";
                redirect($strMsg, "tab0", $thisfile);
            }
			$auth_file=$CitrixDir.'/citrix-host.pwd.'.$citrix_server_ip;
			$fp = fopen($auth_file, 'w+');
			fwrite($fp, $citrix_server_user);
			fwrite($fp, "\n");
			fwrite($fp, $citrix_server_password);
			fwrite($fp, "\n");
			fclose($fp);
            $strMsg .= "Authenticated Citrix XenServer $citrix_server_ip";
            redirect($strMsg, "tab0");
			break;

        default:
            $strMsg .= "No such auth_action $auth_action <br>";
            redirect($strMsg, "tab0");
			break;

    }
}






function citrix_auth() {
	global $citrix_server_id;
    global $thisfile;

	$disp = "<b>Authenticate Citrix Server $citrix_server_id</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$citrix_server_tmp = new appliance();
	$citrix_server_tmp->get_instance_by_id($citrix_server_id);
	$citrix_server_resource = new resource();
	$citrix_server_resource->get_instance_by_id($citrix_server_tmp->resources);
    $citrix_server_ip = $citrix_server_resource->ip;

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-auth.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
        'citrix_server_user' => htmlobject_input('citrix_server_user', array("value" => '', "label" => 'Username'), 'text', 20),
        'citrix_server_password' => htmlobject_input('citrix_server_password', array("value" => '', "label" => 'Password'), 'password', 20),
        'citrix_server_id' => $citrix_server_id,
        'hidden_citrix_server_id' => "<input type=\"hidden\" name=\"citrix_server_id\" value=\"$citrix_server_id\">",
        'hidden_citrix_server_ip' => "<input type=\"hidden\" name=\"citrix_server_ip\" value=\"$citrix_server_ip\">",
        'hidden_action' => "<input type=\"hidden\" name=\"auth_action\" value=\"authenticate\">",
        'backlink' => '<a href=citrix-manager.php?citrix_server_id='.$citrix_server_id.'><strong>Back</strong></a>',
		'submit' => htmlobject_input('submit_action', array("value" => 'Set', "label" => 'Set'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Authenticate with Citrix Server', 'value' => citrix_auth());
}

echo htmlobject_tabmenu($output);

?>


