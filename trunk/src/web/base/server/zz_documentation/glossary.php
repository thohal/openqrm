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
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_glossary() {

	$disp = "<h1>Glossary</h1>";
	$disp = $disp."<br>";
	$disp = $disp."This glossary explains the terms used in this documentation.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";

	$disp = $disp."<b>appliance</b>";
	$disp = $disp."<br>";
	$disp = $disp."- an appliance in openQRM means the combination of a kernel, an image, a resource, application-requiremetns and the service-level agreements of the service (application) running on the rootfs (image).";
	$disp = $disp." Basically appliances is abstracting the service to be deployed. A configured appliance then can be managed in an easy way by a couple of mouse-clicks (e.g. to start or stop them).";
	$disp = $disp."</li><li>";

	$disp = $disp."<b>golden-image</b>";
	$disp = $disp."<br>";
	$disp = $disp."- a server image which is just used as a template but never be deployed itself. openQRM's storage-integration allows to create clones from the ";
	$disp = $disp."'golden-image' server template within seconds using modern storage technologies like lvm2.";
	$disp = $disp."</li><li>";

	$disp = $disp."<b>image</b>";
	$disp = $disp."<br>";
	$disp = $disp."- copy of a servers root-filesystems, can be on a block-device (e.g. for iscsi- and aoe-deployment) or in a directory (e.g. for nfs-root deployment)";
	$disp = $disp."</li><li>";

	$disp = $disp."<b>kernel</b>";
	$disp = $disp."<br>";
	$disp = $disp."- a kernel in openQRM is the combination of the linux-kernel file (vmlinuz), its kernel-modules (/lib/moduels/[kernel-version]), the System-map and";
	$disp = $disp." a special openQRM-initrd ramdisk. Especially the openQRM-initrd provides the generic deployment functions next to the 'hooks' for plugins to interact with the starting system.";
	$disp = $disp."</li><li>";

	$disp = $disp."<b>plugin</b>";
	$disp = $disp."<br>";
	$disp = $disp."- a plugin provides additional functionality for the openQRM-server and/or changes existing behaviour to support addtional features.";
	$disp = $disp." It 'plugs' into openQRM via a well defined API and can interact with the base-server objects to allow a deep-integration of third-party components.";
	$disp = $disp."</li><li>";

	$disp = $disp."<b>resource</b>";
	$disp = $disp."<br>";
	$disp = $disp."- physical or virtual server hardware available in the data-center";
	$disp = $disp."</li><li>";

	$disp = $disp."<b>storage</b>";
	$disp = $disp."<br>";
	$disp = $disp."- a storage-system in your data-center used to store the server images. Can be from diffrent types like e.g. Nfs, Iscsi, Aeo/Coraid, NetApp-Filer, etc.";

	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";

	return $disp;
}




$output = array();
$output[] = array('label' => 'Glossary', 'value' => documentation_glossary());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
