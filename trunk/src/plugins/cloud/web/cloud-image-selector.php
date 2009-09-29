
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="cloud.css" />

<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special clouduser class
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudprivateimage.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
// get the cu_id array
$private_id_arr = htmlobject_request('cu_id');


function redirect_private($strMsg, $currenttab = 'tab0') {
	global $thisfile;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab."&redirect=yes";
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


// check if we got some actions to do
if (htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'set':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        $pimage = new image();
                        $pimage->get_instance_by_id($id);
                        $private_cu_id = $private_id_arr[$id];
                        if ($private_cu_id == -1) {
                            $private_name = "Hide";
                        } else if ($private_cu_id == 0) {
                            $private_name = "All";
                        } else {
                            $pclouduser = new clouduser();
                            $pclouduser->get_instance_by_id($private_cu_id);
                            $private_name = $pclouduser->name;
                        }
                        $strMsg .= "Setting image $pimage->name to $private_name ( $private_cu_id )....<br>";

                        // check if existing, if not create, otherwise update
                        unset($cloud_private_image);
                        $cloud_private_image = new cloudprivateimage();
                        $cloud_private_image->get_instance_by_image_id($id);
                        if (strlen($cloud_private_image->cu_id)) {
                            if ($private_cu_id == -1) {
                                // remove from table
                                $cloud_private_image->remove($cloud_private_image->id);
                            } else {
                                // update
                                $private_cloud_image_fields["co_cu_id"] = $private_cu_id;
                                $cloud_private_image->update($cloud_private_image->id, $private_cloud_image_fields);
                                unset($private_cloud_image_fields);
                            }

                        } else {
                            // create
                            if ($private_cu_id >= 0) {
                                // create array for add
                                $private_cloud_image_fields["co_id"]=openqrm_db_get_free_id('co_id', $cloud_private_image->_db_table);
                                $private_cloud_image_fields["co_image_id"] = $id;
                                $private_cloud_image_fields["co_cu_id"] = $private_cu_id;
                                $private_cloud_image_fields["co_state"] = 1;
                                $cloud_private_image->add($private_cloud_image_fields);
                                unset($private_cloud_image_fields);
                            }
                        }


                    }
                    redirect_private($strMsg, 'tab0');
                }
                break;



        }
    }
}


function cloud_image_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;

    // private-image enabled ?
    $cp_conf = new cloudconfig();
    $show_private_image = $cp_conf->get_value(21);	// show_private_image
    if (strcmp($show_private_image, "true")) {
        $strMsg = "<strong>Private image feature is not enabled in this Cloud !</strong>";
        return $strMsg;
        exit(0);
    }

	$table = new htmlobject_table_identifiers_checked('image_id');
	$arHead = array();

	$arHead['image_id'] = array();
	$arHead['image_id']['title'] ='ID';

	$arHead['image_name'] = array();
	$arHead['image_name']['title'] ='Name';

	$arHead['image_version'] = array();
	$arHead['image_version']['title'] ='Version';

	$arHead['image_type'] = array();
	$arHead['image_type']['title'] ='Deployment type';

	$arHead['image_selector'] = array();
	$arHead['image_selector']['title'] ='Assign to';
	$arHead['image_selector']['sortable'] = false;

	$arBody = array();

    // prepare selector array
    $cloud_user_sel = new clouduser();
    $cloud_user_arr = $cloud_user_sel->get_list();
    $cloud_user_arr = array_reverse($cloud_user_arr);
    $cloud_user_arr[] = array('value'=> '0', 'label'=> 'All');
    $cloud_user_arr[] = array('value'=> '-1', 'label'=> 'Hide');
    $cloud_user_arr = array_reverse($cloud_user_arr);

	// db select
    $image_count = 0;
	$image_list = new image();
	$image_array = $image_list->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($image_array as $index => $im) {
		$image_id = $im["image_id"];
        $image = new image();
        $image->get_instance_by_id($image_id);
        // is a private image already ?
        $private_image = new cloudprivateimage();
        $private_image->get_instance_by_image_id($image->id);
        if (strlen($private_image->cu_id)) {
            if ($private_image->cu_id > 0) {
                $cloud_user = new clouduser();
                $cloud_user->get_instance_by_id($private_image->cu_id);
                $pi_selected = $cloud_user->id;
            } else if ($private_image->cu_id == 0) {
                 $pi_selected = 0;
            } else {
                $pi_selected = -1;
            }
        } else {
            $pi_selected = -1;
        }

		$arBody[] = array(
			'image_id' => $image->id,
			'image_name' => $image->name,
			'image_version' => $image->version,
			'image_type' => $image->type,
			'image_selector' => htmlobject_select("cu_id[$image->id]", $cloud_user_arr, '', array($pi_selected)),
		);
        $image_count++;
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('set');
		$table->identifier = 'image_id';
	}
    $table->max = $image_list->get_count();

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-image-selector-tpl.php');
	$t->setVar(array(
		'cloud_private_image_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}








$output = array();
$output[] = array('label' => 'Cloud Image Selector', 'value' => cloud_image_selector());
echo htmlobject_tabmenu($output);

?>