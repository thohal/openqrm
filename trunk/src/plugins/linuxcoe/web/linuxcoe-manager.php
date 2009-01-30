
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="linuxcoe.css" />

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

$refresh_delay=2;
global $OPENQRM_SERVER_BASE_DIR;
// set ip
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();



function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	// using meta refresh because of the java-script in the header	
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}




function linuxcoe_display($page) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	$install_lock_file = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linuxcoe/web/lock/install-lock";

	$table = new htmlobject_db_table('imageshelf_id');

	$disp = "<h1>LinuxCOE</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	// check if the user reads the setup/install page 
	if (file_exists($install_lock_file)) {
		$page = "setup";
	}
	switch ($page) {
		case 'about':
			$lcoe_url="./linuxcoe-about.php";
			break;
		case 'main':
			$lcoe_url="http://$OPENQRM_SERVER_IP_ADDRESS/systemdesigner/";
			break;
		case 'create':
			$lcoe_url="http://$OPENQRM_SERVER_IP_ADDRESS/systemdesigner-cgi-bin/coe_bootimage";
			break;
		case 'profile':
			$lcoe_url="http://$OPENQRM_SERVER_IP_ADDRESS/systemdesigner-cgi-bin/coe_profiles/";
			break;
		case 'retrofit':
			$lcoe_url="http://$OPENQRM_SERVER_IP_ADDRESS/systemdesigner-cgi-bin/coe_retrofit";
			break;
		case 'setup':
			$lcoe_url="./linuxcoe-install.php";
			break;
		default:
			exit(1);
	}

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'linuxcoe-tpl.php');
	$t->setVar(array(
		'thisfile' => $thisfile,
		'currentab' => htmlobject_input('currenttab', array("value" => 'tab1', "label" => ''), 'hidden'),
		'lcoe_url' => $lcoe_url,
	));

	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






$output = array();

$page = htmlobject_request('page');
$output[] = array('label' => 'LinuxCOE Admin', 'value' => linuxcoe_display($page));

echo htmlobject_tabmenu($output);

?>
