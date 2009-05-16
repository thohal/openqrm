<!doctype html>
<html lang="en">
<head>
	<title>Select ZFS Storage</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
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
$action = $_REQUEST["action"];
$kvm_server_id = $_REQUEST["kvm_server_id"];
$kvm_server_name = $_REQUEST["kvm_server_name"];
$kvm_server_mac = $_REQUEST["kvm_server_mac"];
$kvm_server_ram = $_REQUEST["kvm_server_ram"];
$kvm_server_disk = $_REQUEST["kvm_server_disk"];

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $RESOURCE_INFO_TABLE;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();

global $OPENQRM_SERVER_IP_ADDRESS;

function redirect_mgmt($strMsg, $file, $kvm_server_id) {
    global $thisfile;
    global $action;
    $url = $file.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&kvm_server_id='.$kvm_server_id;
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}


function show_progressbar() {
?>
    <script type="text/javascript">
        $("#progressbar").progressbar({
			value: 100
		});
        var options = {};
        $("#progressbar").effect("shake",options,1000,null);
	</script>
<?php
        flush();
}


$event->log("$action", $_SERVER['REQUEST_TIME'], 5, "kvm-action", "Processing command $action", "", "", 0, 0, 0);
if(htmlobject_request('action') != '') {
    switch ($action) {
        case 'new':
            show_progressbar();
            if (!strlen($kvm_server_mac)) {
                $strMsg="Got empty mac-address. Not creating new KVM vm";
                redirect_mgmt($strMsg, $thisfile, $kvm_server_id);
                exit(1);
            }
            if (!strlen($kvm_server_ram)) {
                $strMsg="Got empty Memory size. Not creating new KVM vm";
                redirect_mgmt($strMsg, $thisfile, $kvm_server_id);
                exit(1);
            }

            // send command to kvm_server-host to create the new vm
            $kvm_appliance = new appliance();
            $kvm_appliance->get_instance_by_id($kvm_server_id);
            $kvm_server = new resource();
            $kvm_server->get_instance_by_id($kvm_appliance->resources);
            if (strlen($kvm_server_disk)) {
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm create -n $kvm_server_name -m $kvm_server_mac -r $kvm_server_ram -d $kvm_server_disk -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
            } else {
                $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm create -n $kvm_server_name -m $kvm_server_mac -r $kvm_server_ram -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
            }
            $kvm_server->send_command($kvm_server->ip, $resource_command);
            // add resource + type + vhostid
            $resource = new resource();
            $resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
            $resource_ip="0.0.0.0";
            // send command to the openQRM-server
            $openqrm_server->send_command("openqrm_server_add_resource $resource_id $kvm_server_mac $resource_ip");
            // set resource type
            $virtualization = new virtualization();
            $virtualization->get_instance_by_type("kvm-vm");
            // add to openQRM database
            $resource_fields["resource_id"]=$resource_id;
            $resource_fields["resource_mac"]=$kvm_server_mac;
            $resource_fields["resource_localboot"]=0;
            $resource_fields["resource_vtype"]=$virtualization->id;
            $resource_fields["resource_vhostid"]=$kvm_server->id;
            $resource->add($resource_fields);
            // + redirect to the kvm manager
            $strMsg="Created new KVM vm resource $resource_id";
            redirect_mgmt($strMsg, "kvm-manager.php", $kvm_server_id);
            break;

        default:
            $event->log("$action", $_SERVER['REQUEST_TIME'], 3, "kvm-create", "No such kvm command ($action)", "", "", 0, 0, 0);
            break;
    }
}


function kvm_server_create($kvm_server_id) {

	$kvm_server_appliance = new appliance();
	$kvm_server_appliance->get_instance_by_id($kvm_server_id);
	$kvm_server = new resource();
	$kvm_server->get_instance_by_id($kvm_server_appliance->resources);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;
	
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'kvm-create.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'kvm_server_id' => $kvm_server_id,
		'kvm_server_name' => htmlobject_input('kvm_server_name', array("value" => '', "label" => 'VM name'), 'text', 20),
		'kvm_server_mac' => htmlobject_input('kvm_server_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20),
		'kvm_server_ram' => htmlobject_input('kvm_server_ram', array("value" => '512', "label" => 'Memory (MB)'), 'text', 10),
		'kvm_server_disk' => htmlobject_input('kvm_server_disk', array("value" => '2000', "label" => 'Disk (MB)'), 'text', 10),
		'hidden_kvm_server_id' => "<input type=hidden name=kvm_server_id value=$kvm_server_id>",
		'submit' => htmlobject_input('action', array("value" => 'new', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
    if (isset($kvm_server_id)) {
        $output[] = array('label' => 'Kvm-server Create VM', 'value' => kvm_server_create($kvm_server_id));
    }
}

echo htmlobject_tabmenu($output);

?>


