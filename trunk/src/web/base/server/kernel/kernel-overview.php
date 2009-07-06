<?php
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/class/openqrm_server.class.php";
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	header("Location: $url");
	exit;
}


if(htmlobject_request('action') != '' && $OPENQRM_USER->role == "administrator") {
$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'remove':
			$kernel = new kernel();
            if(isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $strMsg .= $kernel->remove($id);
                }
            }
			redirect($strMsg);
			break;

		case 'set-default':
			$kernel = new kernel();
            if(isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    // update default kernel in db
                    $kernel->get_instance_by_id($id);
                    $ar_kernel_update = array(
                        'kernel_name' => "default",
                        'kernel_version' => $kernel->version,
                        'kernel_capabilities' => $kernel->capabilities,
                    );
                    $kernel->update(1, $ar_kernel_update);
                    // send set-default kernel command to openQRM
                    $openqrm_server->send_command("openqrm_server_set_default_kernel $kernel->name");
                    $strMsg .= "Set kernel ".$kernel->name." as the default kernel";
                }
                redirect($strMsg);
            }
			break;

	}

}




function kernel_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$kernel_tmp = new kernel();
	$table = new htmlobject_db_table('kernel_id');

	$disp = '<h1>Kernel List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['kernel_icon'] = array();
	$arHead['kernel_icon']['title'] ='';

	$arHead['kernel_id'] = array();
	$arHead['kernel_id']['title'] ='ID';

	$arHead['kernel_name'] = array();
	$arHead['kernel_name']['title'] ='Name';

	$arHead['kernel_version'] = array();
	$arHead['kernel_version']['title'] ='Version';

	$arHead['kernel_capabilities'] = array();
	$arHead['kernel_capabilities']['title'] ='Capabilities';

	$arBody = array();
	$kernel_array = $kernel_tmp->display_overview(1, $table->limit, $table->sort, $table->order);

	$kernel_icon = "/openqrm/base/img/kernel.png";
	foreach ($kernel_array as $index => $kernel_db) {
		$kernel = new kernel();
		$kernel->get_instance_by_id($kernel_db["kernel_id"]);
		$arBody[] = array(
			'kernel_icon' => "<img width=20 height=20 src=$kernel_icon>",
			'kernel_id' => $kernel_db["kernel_id"],
			'kernel_name' => $kernel_db["kernel_name"],
			'kernel_version' => $kernel_db["kernel_version"],
			'kernel_capabilities' => $kernel_db["kernel_capabilities"],
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
		$table->bottom = array('remove', 'edit', 'set-default');
		$table->identifier = 'kernel_id';
	}
	$table->max = $kernel_tmp->get_count();
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}


function kernel_form() {

	$disp = "<h1>New Kernel</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."New kernels should be added on the openqrm server with the following command:<br>";
    $disp = $disp."<br>";
	$disp = $disp."<br>/usr/lib/openqrm/bin/openqrm kernel add -n name -v version -u username -p password [-l location] [-i initramfs/ext2] [-t path-to-initrd-template-file]<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>name</b> can be any identifier as long as it has no spaces or other special characters; it is used as part of the filename.<br>";
	$disp = $disp."<b>version</b> should be the version for the kernel you want to install. If the filenames are called vmlinuz-2.6.26-2-amd64 then 2.6.26-2-amd64 is the version of this kernel.<br>";
	$disp = $disp."<b>username</b> and <b>password</b> are the credentials to openqrm itself.<br>";
	$disp = $disp."<b>location</b> is the root directory for the kernel you want to install. The files that are used are \${location}/boot/vmlinuz-\${version}, \${location}/boot/initrd.img-\${version} and \${location}/lib/modules/\${version}/*<br>";
	$disp = $disp."<b>initramfs/ext2</b> should specify the type of initrd image you want to generate. Most people want to use <b>initramfs</b> here.<br>";
	$disp = $disp."<b>path-to-initrd-template-file</b> is an optional parameter to specify a non-default openqrm initrd template.<br>";
	$disp = $disp."<br>";
	$disp = $disp."Example:<br>";
	$disp = $disp."/usr/lib/openqrm/bin/openqrm kernel add -n openqrm-kernel-1 -v 2.6.29 -u openqrm -p openqrm -i initramfs -l /<br>";
	$disp = $disp."<br>";
 	return $disp;
}


function kernel_edit($kernel_id) {

	if (!strlen($kernel_id))  {
		echo "No Kernel selected!";
		exit(0);
	}

	$kernel = new kernel();
	$kernel->get_instance_by_id($kernel_id);

	$disp = "<h1>Edit Kernel</h1>";
	$disp = $disp."<form action='kernel-action.php' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp.htmlobject_input('kernel_name', array("value" => $kernel->name, "label" => 'Insert Kernel name'), 'text', 20);
	$disp = $disp.htmlobject_input('kernel_version', array("value" => $kernel->version, "label" => 'Insert Kernel version'), 'text', 20);
	$disp = $disp."<input type=hidden name=kernel_id value=$kernel_id>";
	$disp = $disp."<input type=hidden name=kernel_command value='update'>";
	$disp = $disp."<input type=submit value='Update'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}





$output = array();
if($OPENQRM_USER->role == "administrator") {
	if(htmlobject_request('action') != '') {
        if(isset($_REQUEST['identifier'])) {
            switch (htmlobject_request('action')) {
                case 'edit':
                    foreach($_REQUEST['identifier'] as $id) {
                        $output[] = array('label' => 'Edit Kernel', 'value' => kernel_edit($id));
                        break;
                    }
                    break;
            }
        } else {
            $output[] = array('label' => 'Kernel-Admin', 'value' => kernel_display());
            $output[] = array('label' => 'New', 'value' => kernel_form());
        }
	} else {
        $output[] = array('label' => 'Kernel-Admin', 'value' => kernel_display());
        $output[] = array('label' => 'New', 'value' => kernel_form());
    }
}


?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="kernel.css" />
<?php
echo htmlobject_tabmenu($output);
?>
