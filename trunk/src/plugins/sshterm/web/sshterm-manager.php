
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/include/htmlobject.inc.php";

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;

// get the parameters from the plugin config file
$OPENQRM_PLUGIN_CONFIG_FILE="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sshterm/etc/openqrm-plugin-sshterm.conf";
$store = openqrm_parse_conf($OPENQRM_PLUGIN_CONFIG_FILE);
extract($store);

// run actions
if(htmlobject_request('action') != '') {
    $strMsg = '';
    switch (htmlobject_request('action')) {
        case 'login':
            foreach($_REQUEST['identifier'] as $id) {
                $resource = new resource();
                $resource->get_instance_by_id($id);
                $ip = $resource->ip;
                sshterm_login($id, $ip);
            }
            break;
    }
}


function sshterm_login($id, $ip) {
    global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_PLUGIN_AJAXTERM_REVERSE_PROXY_PORT;
	$redirect_url="https://$ip:$OPENQRM_PLUGIN_AJAXTERM_REVERSE_PROXY_PORT";
	if ("$id" == 0) {
		$redirect_url="https://$OPENQRM_SERVER_IP_ADDRESS:$OPENQRM_PLUGIN_AJAXTERM_REVERSE_PROXY_PORT";
	}
	$left=50+($id*50);
	$top=100+($id*50);

?>
<script type="text/javascript">
function open_sshterm (url) {
    sshterm_window = window.open(url, "<?php echo $ip; ?>", "width=580,height=420,left=<?php echo $left; ?>,top=<?php echo $top; ?>");
    open_sshterm.focus();
}
open_sshterm("<?php echo $redirect_url; ?>");
</script>
<?php

}

function sshterm_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$resource_tmp = new resource();
	$table = new htmlobject_db_table('resource_id');

	$disp = '<h1>Resource List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['resource_state'] = array();
	$arHead['resource_state']['title'] ='';

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';

	$arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_hostname'] = array();
	$arHead['resource_hostname']['title'] ='Name';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$arHead['resource_login'] = array();
	$arHead['resource_login']['title'] ='SSH-Login';

	$arBody = array();
	$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($resource_array as $index => $resource_db) {
        $sshterm_login=false;
		// prepare the values for the array
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		$mem_total = $resource_db['resource_memtotal'];
		$mem_used = $resource_db['resource_memused'];
		$mem = "$mem_used/$mem_total";
		$swap_total = $resource_db['resource_swaptotal'];
		$swap_used = $resource_db['resource_swapused'];
		$swap = "$swap_used/$swap_total";
		if ($resource->id == 0) {
			$resource_icon_default="/openqrm/base/img/logo.png";
	        $sshterm_login=true;
    	} else {
			$resource_icon_default="/openqrm/base/img/resource.png";
		}
		$state_icon="/openqrm/base/img/$resource->state.png";
		// idle ?
		if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
			$state_icon="/openqrm/base/img/idle.png";
            $sshterm_login=false;
		}
        if ("$resource->state" == "active") {
	        $sshterm_login=true;
        }
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}

        $resource_action = "";
        if ($sshterm_login) {
            $resource_action .= "<input type=hidden name=\"sshterm_login_ip[$resource->id]\" value=\"$sshterm_login_ip\">";
            $resource_action .= "<input type=\"image\" name=\"action\" value=\"login\" src=\"img/login.png\" alt=\"login\">";
        }

		$arBody[] = array(
			'resource_state' => "<img src=$state_icon>",
			'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'resource_id' => $resource_db["resource_id"],
			'resource_hostname' => $resource_db["resource_hostname"],
			'resource_ip' => $resource_db["resource_ip"],
			'resource_login' => $resource_action,
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
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('login');
		$table->identifier = 'resource_id';
	}
	$table->max = $resource_tmp->get_count('all');
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}





$output = array();
// only if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'SshTerm Manger', 'value' => sshterm_display());
}


echo htmlobject_tabmenu($output);

?>

