<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once ($RootDir.'/class/plugin.class.php');
require_once ($RootDir.'/include/htmlobject.inc.php');
require_once ($RootDir.'/include/user.inc.php');
require_once ($RootDir.'include/openqrm-server-config.php');

$plugin = new plugin();
$plugins_available = $plugin->available();
$plugins_enabled = $plugin->enabled();

$imgDir = '/openqrm/base/plugins/aa_plugins/img/';

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
global $thisfile;

	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	sleep(1);
	header("Location: $url");
	exit;
}


//--------------------------------------------------- action

if(htmlobject_request('action') != '' && $OPENQRM_USER->role == "administrator") {
require_once ($RootDir.'/class/event.class.php');
require_once ($RootDir.'/class/openqrm_server.class.php');
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
$strMsg = '';
$identifier = htmlobject_request('identifier');

	if($identifier == '') {
		$identifier = array();
	}

	switch (htmlobject_request('action')) {
		case 'enable':
			$event = new event();
			foreach($identifier as $id) {
				$return = $openqrm_server->send_command("openqrm_server_plugin_command $id init $OPENQRM_USER->name $OPENQRM_USER->password");
				if($return === true) {
					$strMsg .= 'enabled '.$id.'<br>';
				} else {
					$strMsg .= $id.' not enabled <br>';
				}
			}
			redirect($strMsg);
			break;
		case 'disable':
			$event = new event();
			foreach($identifier as $id) {
				$return = $openqrm_server->send_command("openqrm_server_plugin_command $id uninstall $OPENQRM_USER->name $OPENQRM_USER->password");
				if($return === true) {
					$strMsg .= 'disabled '.$id.'<br>';
				} else {
					$strMsg .= $id.' not disabled <br>';
				}
			}
			redirect($strMsg);
			break;
		case 'start':
			$event = new event();
			foreach($identifier as $id) {
				if (in_array($id, $plugins_enabled)) {
					$return = $openqrm_server->send_command("openqrm_server_plugin_command $id start");
					if($return === true) {
						$strMsg .= 'started '.$id.'<br>';
					} else {
						$strMsg .= $id.' not started <br>';
					}
				} else {
					$strMsg .= $id.' must be enabled first <br>';
				}
			}
			redirect($strMsg);
			break;
		case 'stop':
			$event = new event();
			foreach($identifier as $id) {
				if (in_array($id, $plugins_enabled)) {
					$return = $openqrm_server->send_command("openqrm_server_plugin_command $id stop");
					if($return === true) {
						$strMsg .= 'stoped '.$id.'<br>';
					} else {
						$strMsg .= $id.' not soped <br>';
					}
				} else {
					$strMsg .= $id.' must be enabled first <br>';
				}
			}
			redirect($strMsg);
			break;
	}
}

//--------------------------------------------------- output

$arHead = array();
$arHead['plugin_icon'] = array();
$arHead['plugin_icon']['title'] ='&#160;';
$arHead['plugin_icon']['sortable'] = false;

$arHead['plugin_name'] = array();
$arHead['plugin_name']['title'] ='Plugin';

$arHead['plugin_enabled'] = array();
$arHead['plugin_enabled']['title'] ='Enabled';

$arBody = array();
$i = 0;
foreach ($plugins_available as $index => $plugin_name) {
	$arBody[$i] = array();
	if (!in_array($plugin_name, $plugins_enabled)) {
			
		$arBody[$i]['plugin_icon'] = '<img src="'.$imgDir.'plugin.png">';
		$arBody[$i]['plugin_name'] = $plugin_name;
		$arBody[$i]['plugin_enabled'] = 'false';

	} else {
	
		$plugin_icon_path="$RootDir/plugins/$plugin_name/img/plugin.png";
		$plugin_icon="/openqrm/base/plugins/$plugin_name/img/plugin.png";
		$plugin_icon_default="/openqrm/base/plugins/aa_plugins/img/plugin.png";
			if (file_exists($plugin_icon_path)) {
			$plugin_icon_default=$plugin_icon;
			}
			
		$arBody[$i]['plugin_icon'] = '<img src="'.$plugin_icon_default.'">';
		$arBody[$i]['plugin_name'] = $plugin_name;
		$arBody[$i]['plugin_enabled'] = 'true';

	}

$i++;
}

$table_1 = new htmlobject_db_table('plugin_enabled', 'DESC');
$table_1->id = 'Tabelle';
$table_1->css = 'htmlobject_table';
$table_1->border = 1;
$table_1->cellspacing = 0;
$table_1->cellpadding = 3;
$table_1->form_action = $thisfile;
$table_1->head = $arHead;
$table_1->body = $arBody;
if ($OPENQRM_USER->role == "administrator") {
	$table_1->bottom = array('enable', 'disable', 'start', 'stop');
	$table_1->identifier = 'plugin_name';
}

$disp = "<h1>Plugin Manager</h1>";
$disp .= '<div style="float:left;">'.$table_1->get_string().'</div>';
$disp .= '<br style="clear:both;">';

$output = array();
$output[] = array('label' => 'Plugin Manager', 'value' => $disp);
?>

<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="style.css" />
<style>
.htmlobject_tab_box {
	text-decoration: none;
}
</style>


<?php
echo htmlobject_tabmenu($output);
?>

<script>
parent.NaviFrame.location.href='../../menu.php';
</script>
