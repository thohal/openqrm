<!doctype html>
<html lang="en">
<head>
	<title>openQRM plugin manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="style.css" />
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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once ($RootDir.'/class/plugin.class.php');
require_once ($RootDir.'/include/htmlobject.inc.php');
require_once ($RootDir.'/include/user.inc.php');
require_once ($RootDir.'include/openqrm-server-config.php');

$plugin = new plugin();
$plugins_available = $plugin->available();
$plugins_enabled = $plugin->enabled();
$plugins_started = $plugin->started();
$refresh_delay=1;
$refresh_loop_max=20;

$imgDir = '/openqrm/base/plugins/aa_plugins/img/';
global $OPENQRM_SERVER_BASE_DIR;

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
    global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&plugin_filter='.htmlobject_request('plugin_filter');
	}
	//header("Location: $url");
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


// checks the state of a plugin after an action
// waits for plugin to get to the new state
function check_plugin_state($cmd, $plugin) {
    global $refresh_delay;
    global $refresh_loop_max;
    $refresh_loop=0;
    switch($cmd) {
        case "start";
            $pfile = $_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/".$plugin."/.running";
            while (!file_exists($pfile)) {
                sleep($refresh_delay);
                $refresh_loop++;
                flush();
                if ($refresh_loop > $refresh_loop_max)  {
                    return false;
                }
            }
            return true;
            break;
        case "stop";
            $pfile = $_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/".$plugin."/.running";
            while (file_exists($pfile)) {
                sleep($refresh_delay);
                $refresh_loop++;
                flush();
                if ($refresh_loop > $refresh_loop_max)  {
                    return false;
                }
            }
            return true;
            break;
        case "enable";
            $pdir = $_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/".$plugin;
            while (!file_exists($pdir)) {
                sleep($refresh_delay);
                $refresh_loop++;
                flush();
                if ($refresh_loop > $refresh_loop_max)  {
                    return false;
                }
            }
            return true;
            break;
        case "disable";
            $pdir = $_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/".$plugin;
            while (file_exists($pdir)) {
                sleep($refresh_delay);
                $refresh_loop++;
                flush();
                if ($refresh_loop > $refresh_loop_max)  {
                    return false;
                }
            }
            return true;
            break;
    }
    return false;
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


//--------------------------------------------------- action

if(htmlobject_request('action') != '' && $OPENQRM_USER->role == "administrator") {
require_once ($RootDir.'/class/event.class.php');
require_once ($RootDir.'/class/deployment.class.php');
require_once ($RootDir.'/class/storage.class.php');
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
            show_progressbar();
			foreach($identifier as $plugin_name) {
				$error = false;
				$tmp = $plugin->get_config($plugin_name);
				switch($tmp['type']) {
					//------------------------- check if storage allready enabled
					case 'storage':
						$deployment = new deployment();
						$dep = $deployment->get_id_by_storagetype($plugin_name);
						if(count($dep) > 0) {
							$strMsg .= $plugin_name.' allready enabled<br>';
							$error = true;
						}
					break;
				}

				if($error === false) {
					$return = $openqrm_server->send_command("openqrm_server_plugin_command $plugin_name init $OPENQRM_USER->name $OPENQRM_USER->password");
					if($return === true) {
                        if (check_plugin_state("enable", $plugin_name)) {
    						$strMsg .= 'enabled '.$plugin_name.'<br>';
                        } else {
    						$strMsg .= 'Timeout while enabling '.$plugin_name.'<br>';
                        }
					} else {
						$strMsg .= $plugin_name.' not enabled <br>';
					}
				}
			}
			redirect($strMsg);
			break;
		case 'disable':
			$event = new event();
            show_progressbar();
			foreach($identifier as $plugin_name) {
				$error = false;
				$tmp = $plugin->get_config($plugin_name);
				switch($tmp['type']) {
					//------------------------- check if storage in use
					case 'storage':
						$storage = new storage();
						$types = $storage->get_storage_types();
						$deployment = new deployment();
						$dep = $deployment->get_id_by_storagetype($plugin_name);
						foreach($dep as $val) {
							if(in_array($val['value'], $types)) {
								$strMsg .= $plugin_name.' in use by storage<br>';
								$error = true;
							}
						}
					break;
				}

				if($error === false) {
					$return = $openqrm_server->send_command("openqrm_server_plugin_command $plugin_name uninstall $OPENQRM_USER->name $OPENQRM_USER->password");
					if($return === true) {
                        if (check_plugin_state("disable", $plugin_name)) {
    						$strMsg .= 'disabled '.$plugin_name.'<br>';
                        } else {
    						$strMsg .= 'Timeout while disabling '.$plugin_name.'<br>';
                        }
					} else {
						$strMsg .= $plugin_name.' not disabled <br>';
					}
				}
			}
			redirect($strMsg);
			break;
		case 'start':
			$event = new event();
            show_progressbar();
			foreach($identifier as $id) {
				if (in_array($id, $plugins_enabled)) {
					$return = $openqrm_server->send_command("openqrm_server_plugin_command $id start");
					if($return === true) {
                        if (check_plugin_state("start", $id)) {
    						$strMsg .= 'started '.$id.'<br>';
                        } else {
    						$strMsg .= 'Timeout while starting '.$id.'<br>';
                        }
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
            show_progressbar();
			foreach($identifier as $id) {
				if (in_array($id, $plugins_enabled)) {
					$return = $openqrm_server->send_command("openqrm_server_plugin_command $id stop");
					if($return === true) {
                        if (check_plugin_state("stop", $id)) {
    						$strMsg .= 'stopped '.$id.'<br>';
                        } else {
    						$strMsg .= 'Timeout while stopping '.$id.'<br>';
                        }
					} else {
						$strMsg .= $id.' not stopped <br>';
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

$arHead['plugin_type'] = array();
$arHead['plugin_type']['title'] ='Type';

$arHead['plugin_description'] = array();
$arHead['plugin_description']['title'] ='Description';

$arHead['plugin_enabled'] = array();
$arHead['plugin_enabled']['title'] ='Enabled';

$arHead['plugin_started'] = array();
$arHead['plugin_started']['title'] ='Started';

$arBody = array();
$i = 0;
$plugin_started='<img src="/openqrm/base/plugins/aa_plugins/img/start.png" border="0" alt="click to stop" title="click to stop">';
$plugin_stopped='<img src="/openqrm/base/plugins/aa_plugins/img/stop.png" border="0" alt="click to start" title="click to start">';
$plugin_enabled='<img src="/openqrm/base/plugins/aa_plugins/img/disable.png" border="0" alt="click to disable" title="click to disable">';
$plugin_disabled='<img src="/openqrm/base/plugins/aa_plugins/img/enable.png" border="0" alt="click to enable" title="click to enable">';



$plugtype = array();

foreach ($plugins_available as $index => $plugin_name) {
	$tmp = $plugin->get_config($plugin_name);
	$plugin_description = $tmp['description'];
	$plugin_type =  $tmp['type'];

	$plugtype[] = $plugin_type;
	if (!strlen(htmlobject_request('plugin_filter')) || strstr(htmlobject_request('plugin_filter'), $plugin_type )) {
		$arBody[$i] = array();

		if (!in_array($plugin_name, $plugins_enabled)) {

			$arBody[$i]['plugin_icon'] = '<img src="'.$imgDir.'plugin.png">';
			$arBody[$i]['plugin_name'] = $plugin_name;
			$arBody[$i]['plugin_type'] = $plugin_type;
			$arBody[$i]['plugin_description'] = $plugin_description;
			$arBody[$i]['plugin_enabled'] = '<a href="'.$thisfile.'?action=enable&identifier[]='.$plugin_name.'&plugin_filter='.htmlobject_request('plugin_filter').'">'.$plugin_disabled.'</a>';
			$arBody[$i]['plugin_started'] = '&#160;';

		} else {
	
			$plugin_icon_path="$RootDir/plugins/$plugin_name/img/plugin.png";
			$plugin_icon="/openqrm/base/plugins/$plugin_name/img/plugin.png";
			$plugin_icon_default="/openqrm/base/plugins/aa_plugins/img/plugin.png";
					if (file_exists($plugin_icon_path)) {
				$plugin_icon_default=$plugin_icon;
				}
			
			$arBody[$i]['plugin_icon'] = '<img src="'.$plugin_icon_default.'">';
			$arBody[$i]['plugin_name'] = $plugin_name;
			$arBody[$i]['plugin_type'] = $plugin_type;
			$arBody[$i]['plugin_description'] = $plugin_description;
			$arBody[$i]['plugin_enabled'] = '<a href="'.$thisfile.'?action=disable&identifier[]='.$plugin_name.'&plugin_filter='.htmlobject_request('plugin_filter').'">'.$plugin_enabled.'</a>';
			// started ?
			if (!in_array($plugin_name, $plugins_started)) {
				$arBody[$i]['plugin_started'] = '<a href="'.$thisfile.'?action=start&identifier[]='.$plugin_name.'&plugin_filter='.htmlobject_request('plugin_filter').'">'.$plugin_stopped.'</a>';
			} else {
				$arBody[$i]['plugin_started'] = '<a href="'.$thisfile.'?action=stop&identifier[]='.$plugin_name.'&plugin_filter='.htmlobject_request('plugin_filter').'">'.$plugin_started.'</a>';
			}
		}

	}
	$i++;

}


$plugs = array();
$plugs[] = array('value' => '', 'label' => '');
$plugtype = array_unique($plugtype);
foreach($plugtype as $p) {
	$plugs[] = array('value' => $p, 'label' => $p);
}



$table_1 = new htmlobject_db_table('plugin_enabled', 'ASC');
$table_1->add_headrow(htmlobject_select('plugin_filter', $plugs, 'Filter by Type', array(htmlobject_request('plugin_filter'))));
$table_1->id = 'Tabelle';
$table_1->css = 'htmlobject_table';
$table_1->border = 1;
$table_1->limit = 50;
$table_1->cellspacing = 0;
$table_1->cellpadding = 3;
$table_1->form_action = $thisfile;
$table_1->autosort = true;
$table_1->head = $arHead;
$table_1->body = $arBody;
$table_1->max = count($arBody);
if ($OPENQRM_USER->role == "administrator") {
	$table_1->bottom = array('enable', 'disable', 'start', 'stop');
	$table_1->identifier = 'plugin_name';
}

$disp = "<h1>Plugin Manager</h1><br>";
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
