<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/include/htmlobject.inc.php";


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the resource and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
}


if(htmlobject_request('action') != '') {
$openqrm = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;

$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'map':
			$openqrm->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/bin/openqrm-nagios-manager map");
			$strMsg .= "Now scanning the openQRM network to automatically (re-) create the Nagios configuration. This will take some time ....";
			redirect($strMsg);
			break;
	}

}


function nagios_display() {
	global $OPENQRM_USER;
	global $thisfile;


	$disp = '<h1>Automatic Nagios configuration</h1>';
	$disp .= '<br>';

	$disp .= "<form action=\"$thisfile\" method=\"POST\">";
	$disp .= '<br>';
	$disp .= '<br>';
	$disp .= '<br>';
	$disp .= '<br>';
	$disp .= '<br>';
	$disp .= "<input type='hidden' name='action' value='map'>";
	$disp .= "<input type='submit' value='Map openQRM Network'>";
	$disp .= '<br>';
	$disp .= '</form>';


	
	return $disp;
}


$output = array();
$output[] = array('label' => 'Nagios-List', 'value' => nagios_display());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="nagios.css" />
<style>
.htmlobject_tab_box {
	width:400px;
}
</style>
<?php
echo htmlobject_tabmenu($output);
?>

