
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
require_once "$RootDir/class/kernel.class.php";
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
$product_name = htmlobject_request('product_name');
$product_state = htmlobject_request('product_state');



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
echo "here $product_type  $product_quantity";
                $cloudselector = new cloudselector();
                if ($cloudselector->product_exists($product_type, $product_quantity)) {
                    $strMsg .= "Cloud $product_type Product with Quantity $product_quantity already exists. Not adding ...<br>";
                    redirect_private($strMsg, $product_type);
                }
                $new_product_id = openqrm_db_get_free_id('id', $cloudselector->_db_table);
                $next_free_sort_id = $cloudselector->get_next_free_sort_id("cpu");
                $new_product['id'] = $new_product_id;
                $new_product['type'] = $product_type;
                $new_product['sort_id'] = $next_free_sort_id;
                $new_product['quantity'] = $product_quantity;
                $new_product['price'] = $product_price;
                $new_product['name'] = $product_name;
                $new_product['description'] = $product_description;
                $new_product['state'] = 1;
                $cloudselector->add($new_product);
                $strMsg .= "Added Cloud $product_type Product $new_product_id<br>";
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

    // cloud_selector enabled ?
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

    // cloud_selector enabled ?
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

	$arHead['name'] = array();
	$arHead['name']['title'] ='Product Name';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arHead['state'] = array();
	$arHead['state']['title'] ='State';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("cpu");
    foreach ($product_array as $index => $cloudproduct) {
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $cloudproduct["quantity"],
			'price' => $cloudproduct["price"],
			'name' => $cloudproduct["name"],
			'description' => $cloudproduct["description"],
			'state' => $cloudproduct["state"],
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




function cloud_disk_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

    // cloud_selector enabled ?
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
    $table = new htmlobject_table_builder('id', '', '', '', 'disk');
	$arHead = array();

	$arHead['id'] = array();
	$arHead['id']['title'] ='ID';

	$arHead['quantity'] = array();
	$arHead['quantity']['title'] ='Disk Size';

	$arHead['price'] = array();
	$arHead['price']['title'] ='Price/h';

	$arHead['name'] = array();
	$arHead['name']['title'] ='Product Name';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arHead['state'] = array();
	$arHead['state']['title'] ='State';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("disk");
    foreach ($product_array as $index => $cloudproduct) {
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $cloudproduct["quantity"],
			'price' => $cloudproduct["price"],
			'name' => $cloudproduct["name"],
			'description' => $cloudproduct["description"],
			'state' => $cloudproduct["state"],
		);
        $product_count++;
	}

    $table->add_headrow("<input type=\"hidden\" name=\"product_type\" value=\"disk\">");
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
    $table->max = $cloudselector->get_count_by_type("disk");

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-disk-selector-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
        'product_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function cloud_quantity_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

    // cloud_selector enabled ?
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
    $table = new htmlobject_table_builder('id', '', '', '', 'quantity');
	$arHead = array();

	$arHead['id'] = array();
	$arHead['id']['title'] ='ID';

	$arHead['quantity'] = array();
	$arHead['quantity']['title'] ='Resource Quantity';

	$arHead['price'] = array();
	$arHead['price']['title'] ='Price/h';

	$arHead['name'] = array();
	$arHead['name']['title'] ='Product Name';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arHead['state'] = array();
	$arHead['state']['title'] ='State';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("quantity");
    foreach ($product_array as $index => $cloudproduct) {
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $cloudproduct["quantity"],
			'price' => $cloudproduct["price"],
			'name' => $cloudproduct["name"],
			'description' => $cloudproduct["description"],
			'state' => $cloudproduct["state"],
		);
        $product_count++;
	}

    $table->add_headrow("<input type=\"hidden\" name=\"product_type\" value=\"quantity\">");
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
    $table->max = $cloudselector->get_count_by_type("quantity");

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-quantity-selector-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
        'product_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function cloud_kernel_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

    // cloud_selector enabled ?
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

    // build the kernel select box
    $kernel = new kernel();
    $kernel_list = array();
    $kernel_list = $kernel->get_list();
    // remove the openqrm kernel from the list
    // print_r($kernel_list);
    array_shift($kernel_list);
    $kernel_select_input = htmlobject_select('product_quantity', $kernel_list, '');

    // build product table
    $table = new htmlobject_table_builder('id', '', '', '', 'kernel');
	$arHead = array();

	$arHead['id'] = array();
	$arHead['id']['title'] ='ID';

	$arHead['quantity'] = array();
	$arHead['quantity']['title'] ='Kernel';

	$arHead['price'] = array();
	$arHead['price']['title'] ='Price/h';

	$arHead['name'] = array();
	$arHead['name']['title'] ='Product Name';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arHead['state'] = array();
	$arHead['state']['title'] ='State';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("kernel");
    foreach ($product_array as $index => $cloudproduct) {
        $pkernel = new kernel();
        $pkernel->get_instance_by_id($cloudproduct["quantity"]);
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $pkernel->name,
			'price' => $cloudproduct["price"],
			'name' => $cloudproduct["name"],
			'description' => $cloudproduct["description"],
			'state' => $cloudproduct["state"],
		);
        $product_count++;
	}

    $table->add_headrow("<input type=\"hidden\" name=\"product_type\" value=\"kernel\">");
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
    $table->max = $cloudselector->get_count_by_type("kernel");

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-kernel-selector-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
        'kernel_select' => $kernel_select_input,
        'product_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function cloud_memory_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

    // cloud_selector enabled ?
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
    $table = new htmlobject_table_builder('id', '', '', '', 'memory');
	$arHead = array();

	$arHead['id'] = array();
	$arHead['id']['title'] ='ID';

	$arHead['quantity'] = array();
	$arHead['quantity']['title'] ='Memory Size';

	$arHead['price'] = array();
	$arHead['price']['title'] ='Price/h';

	$arHead['name'] = array();
	$arHead['name']['title'] ='Product Name';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arHead['state'] = array();
	$arHead['state']['title'] ='State';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("memory");
    foreach ($product_array as $index => $cloudproduct) {
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $cloudproduct["quantity"],
			'price' => $cloudproduct["price"],
			'name' => $cloudproduct["name"],
			'description' => $cloudproduct["description"],
			'state' => $cloudproduct["state"],
		);
        $product_count++;
	}

    $table->add_headrow("<input type=\"hidden\" name=\"product_type\" value=\"memory\">");
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
    $table->max = $cloudselector->get_count_by_type("memory");

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-memory-selector-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
        'product_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function cloud_network_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

    // cloud_selector enabled ?
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

    // prepare the array for the network-interface select
    $max_network_interfaces_select = array();
    $max_network_interfaces = $cl_conf->get_value(9);	// max_network_interfaces
    for ($mnet = 1; $mnet <= $max_network_interfaces; $mnet++) {
        $max_network_interfaces_select[] = array("value" => $mnet, "label" => $mnet);
    }
    $network_interfaces_select_input = htmlobject_select('product_quantity', $max_network_interfaces_select, '');

    // build product table
    $table = new htmlobject_table_builder('id', '', '', '', 'network');
	$arHead = array();

	$arHead['id'] = array();
	$arHead['id']['title'] ='ID';

	$arHead['quantity'] = array();
	$arHead['quantity']['title'] ='Network Cards';

	$arHead['price'] = array();
	$arHead['price']['title'] ='Price/h';

	$arHead['name'] = array();
	$arHead['name']['title'] ='Product Name';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arHead['state'] = array();
	$arHead['state']['title'] ='State';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("network");
    foreach ($product_array as $index => $cloudproduct) {
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $cloudproduct["quantity"],
			'price' => $cloudproduct["price"],
			'name' => $cloudproduct["name"],
			'description' => $cloudproduct["description"],
			'state' => $cloudproduct["state"],
		);
        $product_count++;
	}

    $table->add_headrow("<input type=\"hidden\" name=\"product_type\" value=\"network\">");
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
    $table->max = $cloudselector->get_count_by_type("network");

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-network-selector-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
        'network_select' => $network_interfaces_select_input,
        'product_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






function cloud_puppet_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;
    global $RootDir;

    // cloud_selector enabled ?
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

    // build the puppet class select box
    // check if to show puppet
    $puppet_list = array();
    $show_puppet_groups = $cl_conf->get_value(11);	// show_puppet_groups
    if (!strcmp($show_puppet_groups, "true")) {
        // is puppet enabled ?
        if (file_exists("$RootDir/plugins/puppet/.running")) {
            require_once "$RootDir/plugins/puppet/class/puppet.class.php";
            $puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
            global $puppet_group_dir;
            $puppet_group_array = array();
            $puppet = new puppet();
            $puppet_group_array = $puppet->get_available_groups();
            foreach ($puppet_group_array as $index => $puppet_g) {
                $puid=$index+1;
                // $puppet_info = $puppet->get_group_info($puppet_g);
                $puppet_list[] = array("value" => $puppet_g, "label" => $puppet_g);
            }
        }
    }
    $puppet_select_input = htmlobject_select('product_quantity', $puppet_list, '');

    // build product table
    $table = new htmlobject_table_builder('id', '', '', '', 'puppet');
	$arHead = array();

	$arHead['id'] = array();
	$arHead['id']['title'] ='ID';

	$arHead['quantity'] = array();
	$arHead['quantity']['title'] ='Puppet Class';

	$arHead['price'] = array();
	$arHead['price']['title'] ='Price/h';

	$arHead['name'] = array();
	$arHead['name']['title'] ='Product Name';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arHead['state'] = array();
	$arHead['state']['title'] ='State';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("puppet");
    foreach ($product_array as $index => $cloudproduct) {
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $cloudproduct["quantity"],
			'price' => $cloudproduct["price"],
			'name' => $cloudproduct["name"],
			'description' => $cloudproduct["description"],
			'state' => $cloudproduct["state"],
		);
        $product_count++;
	}

    $table->add_headrow("<input type=\"hidden\" name=\"product_type\" value=\"puppet\">");
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
    $table->max = $cloudselector->get_count_by_type("puppet");

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-puppet-selector-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
        'puppet_select' => $puppet_select_input,
        'product_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




function cloud_virtualization_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

    // cloud_selector enabled ?
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

    // build the virtualization type select box
    $virtualization = new virtualization();
    $virtualization_list = array();
    $virtualization_list_select = array();
    $virtualization_list = $virtualization->get_list();
    // check if to show physical system type
    $cc_request_physical_systems = $cl_conf->get_value(4);	// request_physical_systems
    if (!strcmp($cc_request_physical_systems, "false")) {
        array_shift($virtualization_list);
    }
    // filter out the virtualization hosts
    foreach ($virtualization_list as $id => $virt) {
        if (!strstr($virt[label], "Host")) {
            $virtualization_list_select[] = array("value" => $virt[value], "label" => $virt[label]);

        }
    }
    $virtualization_select_input = htmlobject_select('product_quantity', $virtualization_list_select, '');

    // build product table
    $table = new htmlobject_table_builder('id', '', '', '', 'resource');
	$arHead = array();

	$arHead['id'] = array();
	$arHead['id']['title'] ='ID';

	$arHead['quantity'] = array();
	$arHead['quantity']['title'] ='Resource Type';

	$arHead['price'] = array();
	$arHead['price']['title'] ='Price/h';

	$arHead['name'] = array();
	$arHead['name']['title'] ='Product Name';

	$arHead['description'] = array();
	$arHead['description']['title'] ='Description';

	$arHead['state'] = array();
	$arHead['state']['title'] ='State';

	$arBody = array();
    $product_count=0;
    $cloudselector = new cloudselector();
    $product_array = $cloudselector->display_overview_per_type("resource");
    foreach ($product_array as $index => $cloudproduct) {
        $pvirtualization = new virtualization();
        $pvirtualization->get_instance_by_id($cloudproduct["quantity"]);
		$arBody[] = array(
			'id' => $cloudproduct["id"],
			'quantity' => $pvirtualization->name,
			'price' => $cloudproduct["price"],
			'name' => $cloudproduct["name"],
			'description' => $cloudproduct["description"],
			'state' => $cloudproduct["state"],
		);
        $product_count++;
	}

    $table->add_headrow("<input type=\"hidden\" name=\"product_type\" value=\"resource\">");
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
    $table->max = $cloudselector->get_count_by_type("resource");

    //------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-resource-selector-tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
        'external_portal_name' => $external_portal_name,
        'virtualization_select' => $virtualization_select_input,
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

         case 'disk':
            $output[] = array('label' => 'Disk Selector', 'value' => cloud_disk_selector());
            break;

         case 'quantity':
            $output[] = array('label' => 'Quantity Selector', 'value' => cloud_quantity_selector());
            break;

         case 'kernel':
            $output[] = array('label' => 'Kernel Selector', 'value' => cloud_kernel_selector());
            break;

         case 'memory':
            $output[] = array('label' => 'Memory Selector', 'value' => cloud_memory_selector());
            break;

         case 'network':
            $output[] = array('label' => 'Network Selector', 'value' => cloud_network_selector());
            break;

         case 'puppet':
            $output[] = array('label' => 'Puppet Class Selector', 'value' => cloud_puppet_selector());
            break;

         case 'resource':
            $output[] = array('label' => 'Resource Type Selector', 'value' => cloud_virtualization_selector());
            break;

    }
} else {
    $output[] = array('label' => 'Cloud Selector', 'value' => cloud_selector());
}

echo htmlobject_tabmenu($output);

?>