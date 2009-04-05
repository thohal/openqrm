
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="xen.css" />

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
$refresh_delay=3;
$command_delay=1;

$xen_id = $_REQUEST["xen_id"];
$xen_migrate_to_id = $_REQUEST["xen_migrate_to_id"];
$xen_migrate_type = $_REQUEST["xen_migrate_type"];



function xen_htmlobject_select($name, $value, $title = '', $selected = '') {
    $html = new htmlobject_select();
    $html->name = $name;
    $html->title = $title;
    $html->selected = $selected;
    $html->text_index = array("value" => "value", "text" => "label");
    $html->text = $value;
    return $html->get_string();
}



function redirect($strMsg, $currenttab = 'tab0', $url = '') {
    global $thisfile;
    global $xen_id;
    if($url == '') {
        $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&xen_id='.$xen_id;
    }
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
    exit;
}


// xen vm actions
// registered vms
if(htmlobject_request('action_table1') != '') {
	switch (htmlobject_request('action_table1')) {
		case 'start':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
					$strMsg .="Starting $xen_name <br>";
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen start -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    sleep($command_delay);
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'stop':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
					$strMsg .="Stopping $xen_name <br>";
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen stop -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    sleep($command_delay);
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'kill':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
					$strMsg .="Force stopping $xen_name <br>";
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen kill -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    sleep($command_delay);
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'reboot':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
					$strMsg .="Rebooting $xen_name <br>";
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen reboot -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    sleep($command_delay);
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'remove':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
					$strMsg .="Removing $xen_name <br>";
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen remove -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    sleep($command_delay);
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'migrate':
			if (isset($_REQUEST['identifier_table1'])) {
				foreach($_REQUEST['identifier_table1'] as $xen_name) {
					$strMsg .="Migrating $xen_name <br>";
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $destination = new resource();
                    $destination->get_instance_by_id($xen_migrate_to_id);
                    if ("$xen_migrate_type" == "1") {
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen migrate -n $xen_name -i $destination->ip -t live -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    } else {
                        $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen migrate -n $xen_name -i $destination->ip -t regular -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    }
                    $xen->send_command($xen->ip, $resource_command);
                    sleep($command_delay);
                }
                redirect($strMsg, "tab0");
            }
			break;
    }
}



// unregistered vms
if(htmlobject_request('action_table2') != '') {
	switch (htmlobject_request('action_table2')) {
		case 'add':
			if (isset($_REQUEST['identifier_table2'])) {
				foreach($_REQUEST['identifier_table2'] as $xen_name) {
					$strMsg .="Adding $xen_name <br>";
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen add -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    sleep($command_delay);
                }
                redirect($strMsg, "tab0");
            }
			break;

		case 'delete':
			if (isset($_REQUEST['identifier_table2'])) {
				foreach($_REQUEST['identifier_table2'] as $xen_name) {
                    $xen_appliance = new appliance();
                    $xen_appliance->get_instance_by_id($xen_id);
                    $xen = new resource();
                    $xen->get_instance_by_id($xen_appliance->resources);
                    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen delete -n $xen_name -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
                    $xen->send_command($xen->ip, $resource_command);
                    sleep($command_delay);
                }
                redirect($strMsg, "tab0");
            }
			break;
    }
}







function xen_select() {
    global $OPENQRM_USER;
    global $thisfile;
    $table = new htmlobject_db_table('xen_id');

    $disp = "<h1>Select Xen-Host</h1>";
    $disp = $disp."<br>";
    $disp = $disp."Please select a Xen-Host from the list below";
    $disp = $disp."<br>";

    $arHead = array();
    $arHead['xen_state'] = array();
    $arHead['xen_state']['title'] ='';

    $arHead['xen_icon'] = array();
    $arHead['xen_icon']['title'] ='';

    $arHead['xen_id'] = array();
    $arHead['xen_id']['title'] ='ID';

    $arHead['xen_name'] = array();
    $arHead['xen_name']['title'] ='Name';

    $arHead['xen_resource_id'] = array();
    $arHead['xen_resource_id']['title'] ='Res.ID';

    $arHead['xen_resource_ip'] = array();
    $arHead['xen_resource_ip']['title'] ='Ip';

    $arHead['xen_comment'] = array();
    $arHead['xen_comment']['title'] ='Comment';

    $xen_count=0;
    $arBody = array();
    $xen_tmp = new appliance();
    $xen_array = $xen_tmp->display_overview(0, 10, 'appliance_id', 'ASC');

    foreach ($xen_array as $index => $xen_db) {
        $virtualization = new virtualization();
        $virtualization->get_instance_by_id($xen_db["appliance_virtualization"]);
        if ((strstr($virtualization->type, "xen")) && (!strstr($virtualization->type, "xen-vm"))) {
            $xen_resource = new resource();
            $xen_resource->get_instance_by_id($xen_db["appliance_resources"]);
            $xen_count++;
            $resource_icon_default="/openqrm/base/img/resource.png";
            $xen_icon="/openqrm/base/plugins/xen/img/plugin.png";
            $state_icon="/openqrm/base/img/$xen_resource->state.png";
            if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
                $state_icon="/openqrm/base/img/unknown.png";
            }
            if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$xen_icon)) {
                $resource_icon_default=$xen_icon;
            }
            $arBody[] = array(
                'xen_state' => "<img src=$state_icon>",
                'xen_icon' => "<img width=24 height=24 src=$resource_icon_default>",
                'xen_id' => $xen_db["appliance_id"],
                'xen_name' => $xen_resource->hostname,
                'xen_resource_id' => $xen_resource->id,
                'xen_resource_ip' => $xen_resource->ip,
                'xen_comment' => $xen_resource->comment,
            );
        }
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
        $table->bottom = array('select');
        $table->identifier = 'xen_id';
    }
    $table->max = $xen_count;
    return $disp.$table->get_string();
}




function xen_display($appliance_id) {
    global $OPENQRM_SERVER_BASE_DIR;
    global $OPENQRM_USER;
    global $thisfile;
    global $refresh_delay;

    // refresh
    $xen_appliance = new appliance();
    $xen_appliance->get_instance_by_id($appliance_id);
    $xen = new resource();
    $xen->get_instance_by_id($xen_appliance->resources);
    $resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm-xen post_vm_list -u $OPENQRM_USER->name -p $OPENQRM_USER->password";
    $xen->send_command($xen->ip, $resource_command);
    sleep($refresh_delay);

    // fill the arrays for the 3 tables
    $arBody = array();
    $arBody1 = array();
    $arBody2 = array();

    $active_vms[] = array();
    $registerd_vms[] = array();
    $unregisterd_vms[] = array();
    $openqrm_vm[] = array();


    $loop=0;
    $xen_vm_list_file="xen-stat/$xen->id.vm_list";
    if (file_exists($xen_vm_list_file)) {
        $xen_vm_list_content=file($xen_vm_list_file);

        foreach ($xen_vm_list_content as $index => $xenxmoutput) {

            $loop++;
            // Dom0 informations
            if ($loop == 1 && strcmp($xenxmoutput, "Dom0")) {

                $resource_icon_default="/openqrm/base/img/resource.png";
                $xen_icon="/openqrm/base/plugins/xen/img/plugin.png";
                $state_icon="/openqrm/base/img/$xen->state.png";
                if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
                    $state_icon="/openqrm/base/img/unknown.png";
                }
                if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$xen_icon)) {
                    $resource_icon_default=$xen_icon;
                }
                $xen_create_button="<a href=\"xen-create.php?xen_id=$xen_appliance->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
                // here we take the resource id as the identifier because
                // we need to run commands on the resource ip
                $arBody[] = array(
                    'xen_state' => "<img src=$state_icon>",
                    'xen_icon' => "<img width=24 height=24 src=$resource_icon_default>",
                    'xen_id' => $xen_appliance->id,
                    'xen_name' => $xen->hostname,
                    'xen_resource_id' => $xen->id,
                    'xen_resource_ip' => $xen->ip,
                    'xen_resource_memory' => $xen->memtotal,
                    'xen_create' => $xen_create_button,
                );
                continue;

            } else {

                // registered vms -> from xm list output
                if (strstr($xenxmoutput, "#")) {

                    $xenxmoutput = str_replace("#", "", $xenxmoutput);
                    $first_at_pos = strpos($xenxmoutput, "@");
                    $first_at_pos++;
                    $xen_name_first_at_removed = substr($xenxmoutput, $first_at_pos, strlen($xenxmoutput)-$first_at_pos);
                    $second_at_pos = strpos($xen_name_first_at_removed, "@");
                    $second_at_pos++;
                    $xen_name_second_at_removed = substr($xen_name_first_at_removed, $second_at_pos, strlen($xen_name_first_at_removed)-$second_at_pos);
                    $third_at_pos = strpos($xen_name_second_at_removed, "@");
                    $third_at_pos++;
                    $xen_name_third_at_removed = substr($xen_name_second_at_removed, $third_at_pos, strlen($xen_name_second_at_removed)-$third_at_pos);

                    $xen_name = trim(substr($xenxmoutput, 0, $first_at_pos-1));
                    $registerd_vms[] = $xen_name;

                    // check if on- or offline
                    if (strstr($xenxmoutput, "---")) {
                        $active_vms[] = $xen_name;
                    }
                    continue;


                // all/registered + unregistered vms
                // -> from ls *.cfg output
                } else {

                    $first_at_pos = strpos($xenxmoutput, "@");
                    $first_at_pos++;
                    $xen_name_first_at_removed = substr($xenxmoutput, $first_at_pos, strlen($xenxmoutput)-$first_at_pos);
                    $second_at_pos = strpos($xen_name_first_at_removed, "@");
                    $second_at_pos++;
                    $xen_name_second_at_removed = substr($xen_name_first_at_removed, $second_at_pos, strlen($xen_name_first_at_removed)-$second_at_pos);
                    $third_at_pos = strpos($xen_name_second_at_removed, "@");
                    $third_at_pos++;
                    $xen_name_third_at_removed = substr($xen_name_second_at_removed, $third_at_pos, strlen($xen_name_second_at_removed)-$third_at_pos);
                    $fourth_at_pos = strpos($xen_name_third_at_removed, "@");
                    $fourth_at_pos++;
                    $xen_name_fourth_at_removed = substr($xen_name_third_at_removed, $fourth_at_pos, strlen($xen_name_third_at_removed)-$fourth_at_pos);
                    $fivth_at_pos = strpos($xen_name_fourth_at_removed, "@");
                    $fivth_at_pos++;
                    $xen_name_fivth_at_removed = substr($xen_name_fourth_at_removed, $fivth_at_pos, strlen($xen_name_fourth_at_removed)-$fivth_at_pos);
                    $sixth_at_pos = strpos($xen_name_fivth_at_removed, "@");
                    $sixth_at_pos++;
                    $xen_name_sixth_at_removed = substr($xen_name_fivth_at_removed, $fivth_at_pos, strlen($xen_name_fivth_at_removed)-$fivth_at_pos);



                    $xen_openqrm_vm = trim(substr($xenxmoutput, 0, $first_at_pos-1));
                    $xen_name = trim(substr($xen_name_first_at_removed, 0, $second_at_pos-1));
                    $xen_vm_memory = trim(substr($xen_name_second_at_removed, 0, $third_at_pos-1));
                    $xen_vm_mac = trim(substr($xen_name_third_at_removed, 0, $fourth_at_pos-1));
                    $xen_vm_bridge = trim(substr($xen_name_fourth_at_removed, 0, $fivth_at_pos-1));
                    $xen_vm_vnc = trim(substr($xen_name_fivth_at_removed, 0, $sixth_at_pos-1));

                    $xen_vm_resource = new resource();
                    $xen_vm_resource->get_instance_by_mac($xen_vm_mac);
                    $xen_vm_id = $xen_vm_resource->id;
                    $xen_vm_ip = $xen_vm_resource->ip;

                    switch ($xen_openqrm_vm) {
                        case '1':
                            $openqrm_vm[] = $xen_name;
                            break;
                    }

                }

            }


            // here we fill table 1 + 2
            // check if in registered_vm array
            if (in_array($xen_name, $registerd_vms)) {
                if (in_array($xen_name, $active_vms)) {

                    $xen_vm_state_icon = "/openqrm/base/img/active.png";
                    $xen_vm_actions = "";
                    $xen_vm_actions= $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=stop&xen_id=$xen_appliance->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"></a>&nbsp;";
                    $xen_vm_actions = $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=reboot&xen_id=$xen_appliance->id\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"></a>&nbsp;";
                    $xen_vm_actions = $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=kill&xen_id=$xen_appliance->id\"><img height=16 width=16 src=\"/openqrm/base/img/off.png\" border=\"0\"></a>&nbsp;";

                    // if it is an openqrm vm -> plus migration
                    $xen_vm_migrate_actions = "";
                    if (in_array($xen_name, $openqrm_vm)) {
                        $xen_vm_migrate_actions = $xen_vm_migrate_actions."<b><input type='checkbox' name='xen_migrate_type' value='1'> live</b>";
                        // we need a select with the ids/ips from all resources which
                        // are used by appliances with xen capabilities
                        $xen_host_resource_list = array();
                        $appliance_list = new appliance();
                        $appliance_list_array = $appliance_list->get_list();
                        foreach ($appliance_list_array as $index => $app) {
                            $appliance_xen_host_check = new appliance();
                            $appliance_xen_host_check->get_instance_by_id($app["value"]);
                            $virtualization = new virtualization();
                            $virtualization->get_instance_by_id($appliance_xen_host_check->virtualization);
                            if ((strstr($virtualization->type, "xen")) && (!strstr($virtualization->type, "xen-vm"))) {
                                $xen_host_resource = new resource();
                                $xen_host_resource->get_instance_by_id($appliance_xen_host_check->resources);
                                $xen_host_resource_list[] = array("value"=>$xen_host_resource->id, "label"=>$xen_host_resource->ip,);
                            }
                        }

                        $migrateion_select = xen_htmlobject_select('xen_migrate_to_id', $xen_host_resource_list, '', $xen_host_resource_list);
                        $xen_vm_migrate_actions = $xen_vm_migrate_actions.$migrateion_select;
                    }

                } else {

                    // offline
                    $xen_vm_state_icon = "/openqrm/base/img/off.png";
                    $xen_vm_actions = "";
                    $xen_vm_actions= $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=start&xen_id=$xen_appliance->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"></a>&nbsp;";
                    $xen_vm_actions = $xen_vm_actions."<a href=\"$thisfile?identifier_table1[]=$xen_name&action_table1=remove&xen_id=$xen_appliance->id\"><img height=16 width=16 src=\"/openqrm/base/img/error.png\" border=\"0\"></a>&nbsp;";
                    $xen_vm_migrate_actions = "";
                }

                // add to table1
                $arBody1[] = array(
                    'xen_vm_state' => "<img src=$xen_vm_state_icon><input type='hidden' name='xen_id' value=$xen_appliance->id>",
                    'xen_vm_id' => $xen_vm_id,
                    'xen_vm_name' => $xen_name,
                    'xen_vm_vnc' => $xen_vm_vnc,
                    'xen_vm_ip' => $xen_vm_ip,
                    'xen_vm_mac' => $xen_vm_mac,
                    'xen_vm_bridge' => $xen_vm_bridge,
                    'xen_vm_memory' => $xen_vm_memory,
                    'xen_vm_actions' => $xen_vm_actions,
                    'xen_vm_migrate_actions' => $xen_vm_migrate_actions,
                );


            } else {
                // add to array for table->max
                $unregisterd_vms[] = $xen_name;
                // offline
                $xen_vm_state_icon = "/openqrm/base/img/off.png";
                $xen_vm_actions = "";
                $xen_vm_actions = $xen_vm_actions."<a href=\"$thisfile?identifier_table2[]=$xen_name&action_table2=add&xen_id=$xen_appliance->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"></a>&nbsp;";
                $xen_vm_actions = $xen_vm_actions."<a href=\"$thisfile?identifier_table2[]=$xen_name&action_table2=delete&xen_id=$xen_appliance->id\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"></a>&nbsp;";
                $xen_vm_migrate_actions = "";

                // add to table2
                $arBody2[] = array(
                    'xen_vm_state' => "<img src=$xen_vm_state_icon><input type='hidden' name='xen_id' value=$xen_appliance->id>",
                    'xen_vm_id' => $xen_vm_id,
                    'xen_vm_name' => $xen_name,
                    'xen_vm_vnc' => $xen_vm_vnc,
                    'xen_vm_ip' => $xen_vm_ip,
                    'xen_vm_mac' => $xen_vm_mac,
                    'xen_vm_bridge' => $xen_vm_bridge,
                    'xen_vm_memory' => $xen_vm_memory,
                    'xen_vm_actions' => $xen_vm_actions,
                    'xen_vm_migrate_actions' => $xen_vm_migrate_actions,
                );

            }

        }
    }




    // main output section
    // ############################ Xen Host table #############################
    $disp = "<h1>Xen-Admin</h1>";
    $disp = $disp."<br>";

    $table = new htmlobject_table_identifiers_checked('xen_id');

    $arHead = array();
    $arHead['xen_state'] = array();
    $arHead['xen_state']['title'] ='';

    $arHead['xen_icon'] = array();
    $arHead['xen_icon']['title'] ='';

    $arHead['xen_id'] = array();
    $arHead['xen_id']['title'] ='ID';

    $arHead['xen_name'] = array();
    $arHead['xen_name']['title'] ='Name';

    $arHead['xen_resource_id'] = array();
    $arHead['xen_resource_id']['title'] ='Res.ID';

    $arHead['xen_resource_ip'] = array();
    $arHead['xen_resource_ip']['title'] ='Ip';

    $arHead['xen_resource_memory'] = array();
    $arHead['xen_resource_memory']['title'] ='Memory';

    $arHead['xen_create'] = array();
    $arHead['xen_create']['title'] ='';

    $table->id = 'Tabelle';
    $table->css = 'htmlobject_table';
    $table->border = 1;
    $table->cellspacing = 0;
    $table->cellpadding = 3;
    $table->form_action = $thisfile;
    $table->sort = '';
    $table->head = $arHead;
    $table->body = $arBody;
    if ($OPENQRM_USER->role == "administrator") {
        $table->bottom = array('refresh');
        $table->identifier = 'xen_id';
    }
    $table->max = 1;
    $disp = $disp.$table->get_string();


    // ############################ Xen registered vms table ###################
    $disp = $disp."<h1>Registerd VMs on Xen Host $xen->id/$xen->hostname</h1>";
    $table1 = new htmlobject_db_table('xen_vm_name');
    $arHead1 = array();
    $arHead1['xen_vm_state'] = array();
    $arHead1['xen_vm_state']['title'] ='';

    $arHead1['xen_vm_res'] = array();
    $arHead1['xen_vm_res']['title'] ='Res.';

    $arHead1['xen_vm_name'] = array();
    $arHead1['xen_vm_name']['title'] ='Name';

    $arHead1['xen_vm_vnc'] = array();
    $arHead1['xen_vm_vnc']['title'] ='vnc';

    $arHead1['xen_vm_ip'] = array();
    $arHead1['xen_vm_ip']['title'] ='IP';

    $arHead1['xen_vm_mac'] = array();
    $arHead1['xen_vm_mac']['title'] ='MAC';

    $arHead1['xen_vm_bridge'] = array();
    $arHead1['xen_vm_bridge']['title'] ='Bridge';

    $arHead1['xen_vm_memory'] = array();
    $arHead1['xen_vm_memory']['title'] ='Memory';

    $arHead1['xen_vm_actions'] = array();
    $arHead1['xen_vm_actions']['title'] ='Actions';

    $arHead1['$xen_vm_migrate_actions'] = array();
    $arHead1['$xen_vm_migrate_actions']['title'] ='Migration';



    $table1->id = 'Tabelle';
    $table1->css = 'htmlobject_table';
    $table1->border = 1;
    $table1->cellspacing = 0;
    $table1->cellpadding = 3;
    $table1->form_action = $thisfile;
    $table1->sort = '';
    $table1->identifier_type = "checkbox";
    $table1->bottom_buttons_name = "action_table1";
    $table1->identifier_name = "identifier_table1";
    $table1->head = $arHead1;
    $table1->body = $arBody1;
    if ($OPENQRM_USER->role == "administrator") {
        $table1->bottom = array('start', 'stop', 'restart', 'remove', 'migrate');
        $table1->identifier = 'xen_vm_name';
    }
    $table1->max = count($registerd_vms);
    $disp = $disp.$table1->get_string();

    // ######################### Xen unregistered vms table ####################
    $disp = $disp."<br>";
    $disp = $disp."<br>";
    $disp = $disp."<h1>Unregistered VMs on Xen Host $xen->id/$xen->hostname</h1>";
    $table2 = new htmlobject_db_table('xen_vm_name');
    $arHead2 = array();
    $arHead2['xen_vm_state'] = array();
    $arHead2['xen_vm_state']['title'] ='';

    $arHead2['xen_vm_res'] = array();
    $arHead2['xen_vm_res']['title'] ='Res.';

    $arHead2['xen_vm_name'] = array();
    $arHead2['xen_vm_name']['title'] ='Name';

    $arHead2['xen_vm_vnc'] = array();
    $arHead2['xen_vm_vnc']['title'] ='vnc';

    $arHead2['xen_vm_ip'] = array();
    $arHead2['xen_vm_ip']['title'] ='IP';

    $arHead2['xen_vm_mac'] = array();
    $arHead2['xen_vm_mac']['title'] ='MAC';

    $arHead2['xen_vm_bridge'] = array();
    $arHead2['xen_vm_bridge']['title'] ='Bridge';

    $arHead2['xen_vm_memory'] = array();
    $arHead2['xen_vm_memory']['title'] ='Memory';

    $arHead2['xen_vm_actions'] = array();
    $arHead2['xen_vm_actions']['title'] ='Actions';

    $arHead2['$xen_vm_migrate_actions'] = array();
    $arHead2['$xen_vm_migrate_actions']['title'] ='';

    $table2->id = 'Tabelle';
    $table2->css = 'htmlobject_table';
    $table2->border = 1;
    $table2->cellspacing = 0;
    $table2->cellpadding = 3;
    $table2->form_action = $thisfile;
    $table2->sort = '';
    $table2->identifier_type = "checkbox";
    $table2->bottom_buttons_name = "action_table2";
    $table2->identifier_name = "identifier_table2";
    $table2->head = $arHead2;
    $table2->body = $arBody2;
    if ($OPENQRM_USER->role == "administrator") {
        $table2->bottom = array('add', 'delete');
        $table2->identifier = 'xen_vm_name';
    }
    $table2->max = count($unregisterd_vms);
    $disp = $disp.$table2->get_string();


    return $disp;
}



$output = array();
if(htmlobject_request('action') != '') {
    if (isset($_REQUEST['identifier'])) {
        switch (htmlobject_request('action')) {
            case 'select':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Xen Admin', 'value' => xen_display($id));
                }
                break;
            case 'refresh':
                foreach($_REQUEST['identifier'] as $id) {
                    $output[] = array('label' => 'Xen Admin', 'value' => xen_display($id));
                }
                break;
        }

    } else {
        $output[] = array('label' => 'Xen Admin', 'value' => xen_select());
    }

} else if (strlen($xen_id)) {
    $output[] = array('label' => 'Xen Admin', 'value' => xen_display($xen_id));
} else  {
    $output[] = array('label' => 'Xen Admin', 'value' => xen_select());
}

echo htmlobject_tabmenu($output);

?>


