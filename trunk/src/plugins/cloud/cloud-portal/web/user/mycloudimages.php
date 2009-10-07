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
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudtransaction.class.php";
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudprivateimage.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_REQUEST_TABLE;
global $CLOUD_IMAGE_TABLE;

// who are you ?
$auth_user = $_SERVER['PHP_AUTH_USER'];
global $auth_user;

function redirect2image($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	} else {
		$url = $url.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
    }
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}



function check_allowed_input($text) {
	for ($i = 0; $i<strlen($text); $i++) {
		if (!ctype_alpha($text[$i])) {
			if (!ctype_digit($text[$i])) {
				if (!ctype_space($text[$i])) {
					return false;
				}
			}
		}
	}
	return true;
}

// private-image enabled ?
$private_image_enabled = false;
$cp_conf = new cloudconfig();
$show_private_image = $cp_conf->get_value(21);	// show_private_image
if (!strcmp($show_private_image, "true")) {
    $private_image_enabled = true;
}
global $private_image_enabled;



// check if we got some actions to do
if (htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {

		case 'remove':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
                    $pimage = new cloudprivateimage();
                    $pimage->get_instance_by_id($id);
                    $cp_user = new clouduser();
                    $cp_user->get_instance_by_name("$auth_user");
                    if ($cp_user->id != $pimage->cu_id) {
                        $strMsg = "Private image $id is not owned by $auth_user  $cp_user->id  ! Skipping ... <br>";
                        redirect2image($strMsg, tab5, "mycloud.php");
                        exit(0);
                    }
                    if (!$private_image_enabled) {
                        $strMsg = "Private image feature is not enabled in this Cloud ! Skipping ... <br>";
                        redirect2image($strMsg, tab5, "mycloud.php");
                        exit(0);
                    }
                    // register a new cloudimage for removal
                    $cloud_image_id  = openqrm_db_get_free_id('ci_id', $CLOUD_IMAGE_TABLE);
                    $cloud_image_arr = array(
                            'ci_id' => $cloud_image_id,
                            'ci_cr_id' => 0,
                            'ci_image_id' => $pimage->image_id,
                            'ci_appliance_id' => 0,
                            'ci_resource_id' => 0,
                            'ci_disk_size' => 0,
                            'ci_state' => 0,
                    );
                    $cloud_image = new cloudimage();
                    $cloud_image->add($cloud_image_arr);
                    // remove logic cloudprivateimage
                    $pimage->remove($id);
                    $strMsg .= "Removed private Cloud image $id.";

                }
            }
            redirect2image($strMsg, tab5, "mycloud.php");
			break;

		case 'comment':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
                    $pimage = new cloudprivateimage();
                    $pimage->get_instance_by_id($id);
                    $cp_user = new clouduser();
                    $cp_user->get_instance_by_name("$auth_user");
                    if ($cp_user->id != $pimage->cu_id) {
                        $strMsg = "Private image $id is not owned by $auth_user  $cp_user->id  ! Skipping ... <br>";
                        redirect2image($strMsg, tab5, "mycloud.php");
                        exit(0);
                    }
                    if (!$private_image_enabled) {
                        $strMsg = "Private image feature is not enabled in this Cloud ! Skipping ... <br>";
                        redirect2image($strMsg, tab5, "mycloud.php");
                        exit(0);
                    }
                    $updated_image_comment_arr = htmlobject_request('image_comment');
                    $updated_image_comment = $updated_image_comment_arr["$id"];
                    $updated_image_comment_check = trim($updated_image_comment);
                    // remove any non-violent characters
                    $updated_image_comment_check = str_replace(" ", "", $updated_image_comment_check);
                    $updated_image_comment_check = str_replace(".", "", $updated_image_comment_check);
                    $updated_image_comment_check = str_replace(",", "", $updated_image_comment_check);
                    $updated_image_comment_check = str_replace("-", "", $updated_image_comment_check);
                    $updated_image_comment_check = str_replace("_", "", $updated_image_comment_check);
                    $updated_image_comment_check = str_replace("(", "", $updated_image_comment_check);
                    $updated_image_comment_check = str_replace(")", "", $updated_image_comment_check);
                    $updated_image_comment_check = str_replace("/", "", $updated_image_comment_check);
                    if(!check_allowed_input($updated_image_comment_check)){
                        $strMsg = "Comment contains special characters, skipping update <br>";
                        redirect2image($strMsg, tab5, "mycloud.php");
                        exit(0);
                    }
                    $cloud_pimage = new cloudprivateimage();
                    $ar_request = array(
                        'co_comment' => "$updated_image_comment",
                    );
                    $cloud_pimage->update($id, $ar_request);
                    $strMsg .= "Updated comment on private Cloud image $id";

                }
            }
            redirect2image($strMsg, tab5, "mycloud.php");
			break;

// ######################## end of cloud-image actions #####################



	}
}






function mycloud_images() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	global $auth_user;
    global $private_image_enabled;

    if (!$private_image_enabled) {
        $strMsg = "<strong>Private image feature is not enabled in this Cloud !</strong>";
        return $strMsg;
    }

	$table = new htmlobject_table_builder('co_id', 'DESC', '', '', 'priv');
 
	$arHead = array();

    $arHead['image_icon'] = array();
    $arHead['image_icon']['title'] ='';
	$arHead['image_icon']['sortable'] = false;

	$arHead['co_id'] = array();
	$arHead['co_id']['title'] ='ID';

	$arHead['image_name'] = array();
	$arHead['image_name']['title'] ='Name';

	$arHead['co_comment'] = array();
	$arHead['co_comment']['title'] ='Comment';

	$arBody = array();
    $private_image_count = 0;
    $active_state_icon="/cloud-portal/img/active.png";

    $cl_user = new clouduser();
    $cl_user->get_instance_by_name("$auth_user");
    $private_image = new cloudprivateimage();
	$private_image_array = $private_image->display_overview_per_user($cl_user->id, $table->order);
    foreach ($private_image_array as $index => $private_image_db) {
		$private_image_t = new cloudprivateimage();
		$private_image_t->get_instance_by_id($private_image_db["co_id"]);
        // get the image name
        $pimage = new image();
        $pimage->get_instance_by_id($private_image_t->image_id);
        $pco_id = $private_image_db["co_id"];
        $pcomment = $private_image_db["co_comment"];
        $arBody[] = array(
            'image_icon' => "<img width=16 height=16 src=$active_state_icon><input type=hidden name=\"currenttab\" value=\"tab5\">",
            'co_id' => $pco_id,
            'image_name' => $pimage->name,
            'co_comment' => "<input type=text name=\"image_comment[$pco_id]\" value=\"$pcomment\">",
        );
        $private_image_count++;
    }

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
    $table->autosort = true;
    #$table->sort = "";
    $table->bottom = array('remove', 'comment');
    $table->identifier = 'co_id';
	$table->max = $private_image_count;


	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'mycloudimages-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'currentab' => htmlobject_input('currenttab', array("value" => 'tab5', "label" => ''), 'hidden'),
        'private_image_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



?>
