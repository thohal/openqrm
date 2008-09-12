
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function sshterm_login($id, $ip) {
	$redirect_url="http://$ip/ajaxterm/";
	if ("$id" == 0) {
		$redirect_url="http://localhost:8022";
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

	$arBody = array();
	$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($resource_array as $index => $resource_db) {
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
		} else {
			$resource_icon_default="/openqrm/base/img/resource.png";
		}
		$state_icon="/openqrm/base/img/$resource->state.png";
		// idle ?
		if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
			$state_icon="/openqrm/base/img/idle.png";
		}
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}

		$arBody[] = array(
			'resource_state' => "<img src=$state_icon>",
			'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'resource_id' => $resource_db["resource_id"],
			'resource_hostname' => $resource_db["resource_hostname"],
			'resource_ip' => $resource_db["resource_ip"],
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

	if(htmlobject_request('action') != '') {
		$strMsg = '';
		switch (htmlobject_request('action')) {
			case 'login':
				foreach($_REQUEST['identifier'] as $id) {
					if ("$id" != 0) {
						$resource = new resource();
						$resource->get_instance_by_id($id);
						$ip = $resource->ip;
						sshterm_login($id, $ip);
					} else {
						sshterm_login(0, localhost);
					}
				}
				break;
		}
	}
}


echo htmlobject_tabmenu($output);

?>

