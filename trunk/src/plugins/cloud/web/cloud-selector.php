
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
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudselector.class.php";
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_WEB_PROTOCOL;

// gather post
$product_type = htmlobject_request('product_type');
$product_quantity = htmlobject_request('product_quantity');
$product_price = htmlobject_request('product_price');
$product_description = htmlobject_request('product_description');



function redirect_private($strMsg, $type) {
	global $thisfile;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&product_type='.$type."&redirect=yes";
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

// check if we got some actions to do
if (htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'Remove':
                if (isset($_REQUEST['identifier'])) {
                    $cloudselector = new cloudselector();
                    foreach($_REQUEST['identifier'] as $id) {
                        $cloudselector->remove($id);
                        $strMsg .= "Removed Cloud $product_type Product $id<br>";
                    }
                    redirect_private($strMsg, $product_type);
                }
                break;

            case 'add':
                $cloudselector = new cloudselector();
                if ($cloudselector->product_exists("cpu", $product_quantity)) {
                    $strMsg .= "Cloud $product_type Product with Quantity $product_quantity already exists. Not adding ...<br>";
                    redirect_private($strMsg, $product_type);
                }

                $new_product_id = openqrm_db_get_free_id('id', $cloudselector->_db_table);
                $next_free_sort_id = $cloudselector->get_next_free_sort_id("cpu");
                $new_product['id'] = $new_product_id;
                $new_product['type'] = "cpu";
                $new_product['sort_id'] = $next_free_sort_id;
                $new_product['quantity'] = $product_quantity;
                $new_product['price'] = $product_price;
                $new_product['description'] = $product_description;
                $cloudselector->add($new_product);
                $strMsg .= "Added Cloud CPU Product $new_product_id<br>";
                redirect_private($strMsg, $product_type);
                break;


        }
    }
}








function cloud_selector() {

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
	$t->setFile('tplfile', './tpl/' . 'cloud-selector-tpl.php');
	$t->setVar(array(
        'external_portal_name' => $external_portal_name,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


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

    // build product table
    $table = new htmlobject_table_builder('id', '', '', '', 'cpus');
	$arHead = array();

	$arHead['id'] = array();
	$arHead['id']['title'] ='ID';

	$arHead['quantity'] = array();
	$arHead['quantity']['title'] ='CPU Quantity';

	$arHead['price'] = array();
	$arHead['price']['title'] ='Price/h';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("cpu");
    foreach ($product_array as $index => $cloudproduct) {
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $cloudproduct["quantity"],
			'price' => $cloudproduct["price"],
			'description' => $cloudproduct["description"],
		);
        $product_count++;
	}

    $table->add_headrow("<input type=\"hidden\" name=\"product_type\" value=\"cpu\">");
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	$table->identifier = 'id';
    $table->sort = false;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('Remove');
	}
    $table->max = $cloudselector->get_count_by_type("cpu");

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-cpu-selector-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
        'product_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}




$output = array();
if (strlen($product_type)) {
    switch ($product_type) {
         case 'cpu':
            $output[] = array('label' => 'CPU Selector', 'value' => cloud_cpu_selector());
            break;

    }
} else {
    $output[] = array('label' => 'Cloud Selector', 'value' => cloud_selector());
}

echo htmlobject_tabmenu($output);

?>