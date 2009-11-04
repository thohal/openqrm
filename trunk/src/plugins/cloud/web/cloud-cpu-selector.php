
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
global $OPENQRM_WEB_PROTOCOL;
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


function cloud_cpu_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

    // private-image enabled ?
    $cl_conf = new cloudconfig();
    $show_cloud_selector = $cl_conf->get_value(22);	// cloud_selector
    if (strcmp($show_cloud_selector, "true")) {
        $strMsg = "<strong>Cloud-Selector feature is not enabled in this Cloud !</strong>";
        return $strMsg;
        exit(0);
    }
	// get external name
	$external_portal_name = $cl_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "$OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-cpu-selector-tpl.php');
	$t->setVar(array(
        'external_portal_name' => $external_portal_name,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



$output = array();
$output[] = array('label' => 'CPU Selector', 'value' => cloud_cpu_selector());
echo htmlobject_tabmenu($output);

?>