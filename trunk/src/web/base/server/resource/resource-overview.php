<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	//	using meta refresh here because the resource and resourc class pre-sending header output
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
}


if(htmlobject_request('action') != '' && $OPENQRM_USER->role == "administrator") {
$strMsg = '';

	if(isset($_REQUEST['identifier'])) { 
	
		switch (htmlobject_request('action')) {
			case 'reboot':
				foreach($_REQUEST['identifier'] as $id) {
					if($id != 0) {
						$resource = new resource();
						$resource->get_instance_by_id($id);
						$ip = $resource->ip;
						$strMsg .= $resource->send_command("$ip", "reboot");
						// set state to transition
						$resource_fields=array();
						$resource_fields["resource_state"]="transition";
						$resource->update_info($id, $resource_fields);
					}
				}
				redirect($strMsg);
				break;
	
			case 'poweroff':
				foreach($_REQUEST['identifier'] as $id) {
					if($id != 0) {
						$resource = new resource();
						$resource->get_instance_by_id($id);
						$ip = $resource->ip;
						$strMsg .= $resource->send_command("$ip", "halt");
						// set state to transition
						$resource_fields=array();
						$resource_fields["resource_state"]="off";
						$resource->update_info($id, $resource_fields);
					}
				}
				redirect($strMsg);
				break;
	
			case 'remove':
				foreach($_REQUEST['identifier'] as $id) {
					if($id != 0) {
						$resource = new resource();
						$resource->get_instance_by_id($id);
						$mac = $resource->mac;
						$openqrm_server->send_command("openqrm_remove_resource $id, $mac");
						$strMsg .= $resource->remove($id, $mac);
					}
				}
				redirect($strMsg);
				break;


			case 'update':
				foreach($_REQUEST['identifier'] as $id) {
					if($id != 0) {
						$resource = new resource();
						$resource->get_instance_by_id($id);
						$resource_type = $_REQUEST['resource_type'];

						$virtualization = new virtualization();
						$virtualization->get_instance_by_id($resource_type[$id]);
						$resource_fields = array();

						if (strlen($resource->capabilities)) {
							if (strstr($resource->capabilities, "VIRTUAL")) {
								// edit
								$olds = str_replace("VIRTUAL='", '', $resource->capabilities);
								$nspos = strpos($olds, "'");
								$oldt = substr($olds, 0, $nspos);
								$oldstr = "VIRTUAL='$oldt'";
								$newstr = "VIRTUAL='$virtualization->name'";
								$new_resource_caps = str_replace($oldstr, $newstr, $resource->capabilities);
								$resource_fields["resource_capabilities"] = "$new_resource_caps";
							} else {
								// add
								$resource_fields["resource_capabilities"] = "$resource->capabilities VIRTUAL='$virtualization->name'";
							}
						} else {
							// new
							$resource_fields["resource_capabilities"] = "VIRTUAL='$virtualization->name'";
						}
						$resource->update_info($id, $resource_fields);
						$strMsg .= "Updated resource $id with resource-type $virtualization->name";
					}
				}
				redirect($strMsg);
				break;
	
		}

	} //identifier
	#else { redirect('Please select a resource'); }
}


function resource_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$virtualization = new virtualization();
	$virtualization_list = array();
	$v_list_select = array();
	$virtualization_list_select = array();
	$virtualization_list = $virtualization->get_list();

	// filter out the virtualization hosts
	foreach ($virtualization_list as $id => $virt) {
		if (!strstr($virt[label], "Host")) {
			$virtualization_list_select[] = array("value" => $virt[value], "label" => $virt[label]);
			
		}
	}

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

	$arHead['resource_mac'] = array();
	$arHead['resource_mac']['title'] ='Mac';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$arHead['resource_type'] = array();
	$arHead['resource_type']['title'] ='Type';


	$arHead['resource_memtotal'] = array();
	$arHead['resource_memtotal']['title'] ='Memory';

	$arHead['resource_load'] = array();
	$arHead['resource_load']['title'] ='Load';

	$arBody = array();
	$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($resource_array as $index => $resource_db) {
		// prepare the values for the array
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		$res_id = $resource->id;
		$mem_total = $resource_db['resource_memtotal'];
		$mem_used = $resource_db['resource_memused'];
		$mem = "$mem_used/$mem_total";
		$swap_total = $resource_db['resource_swaptotal'];
		$swap_used = $resource_db['resource_swapused'];
		$swap = "$swap_used/$swap_total";
		if ($resource->id == 0) {
			$resource_icon_default="/openqrm/base/img/logo.png";
			$resource_type_select = "openQRM-server";
		} else {
			$resource_icon_default="/openqrm/base/img/resource.png";
			// select box for the resource_type
			if (strstr($resource->capabilities, "VIRTUAL")) {
				// find out what should be preselected
				$olds = str_replace("VIRTUAL='", '', $resource->capabilities);
				$nspos = strpos($olds, "'");
				$resource_type_name = substr($olds, 0, $nspos);
				// translate name to id
				$virtualization->get_instance_by_name($resource_type_name);
				$resource_type_select = htmlobject_select("resource_type[$res_id]", $virtualization_list_select, '', array($virtualization->id));
			} else {
				$resource_type_select = htmlobject_select("resource_type[$res_id]", $virtualization_list_select, '', array(0));
			}
		
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
			'resource_mac' => $resource_db["resource_mac"],
			'resource_ip' => $resource_db["resource_ip"],
			'resource_type' => $resource_type_select,
			'resource_memtotal' => $mem,
			'resource_load' => $resource_db["resource_load"],
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
		$table->bottom = array('update', 'reboot', 'poweroff', 'remove');
		$table->identifier = 'resource_id';
		$table->identifier_disabled = array(0);
	}
	$table->max = $resource_tmp->get_count('all') + 1; // adding openqrmserver
	
	return $disp.$table->get_string();
}



function resource_form() {

	$disp = "<h1>New Resource</h1>";
	$disp .= "<form action='resource-action.php' method=post>";
	$disp .= "<br>";
	$disp .= "<br>";
	$disp .= htmlobject_input('resource_mac', array("value" => 'XX:XX:XX:XX:XX:XX', "label" => 'Mac-address'), 'text', 17);
	$disp .= htmlobject_input('resource_ip', array("value" => '0.0.0.0', "label" => 'Ip-address'), 'text', 20);
	$disp .= "<input type=hidden name=resource_id value='-1'>";
	$disp .= "<input type=hidden name=resource_command value='new_resource'>";
	$disp .= "<input type=submit value='add'>";
	$disp .= "";
	$disp .= "";
	$disp .= "";
	$disp .= "";
	$disp .= "";
	$disp .= "</form>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'Resource List', 'value' => resource_display());
if($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'New', 'value' => resource_form());
}

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="resource.css" />
<?php
echo htmlobject_tabmenu($output);
?>

