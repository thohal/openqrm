<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_concept() {

	$disp = "<h1>Concept</h1>";
	$disp = $disp."<br>";
	$disp = $disp."openQRM's main concept is to separate the data-center components into modules";
	$disp = $disp." and to then manage 'combinations of modules'. Sounds confusing ? It will get more clear soon";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Components of a common data-center :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Physical Hardware";
	$disp = $disp."</li><li>";
	$disp = $disp."Operation Systems";
	$disp = $disp."</li><li>";
	$disp = $disp."Applications/Services";
	$disp = $disp."</li><li>";
	$disp = $disp."Network Switches";
	$disp = $disp."</li><li>";
	$disp = $disp."SLA's (Service Level Agreements)";
	$disp = $disp."</li><li>";
	$disp = $disp."Storage Systems";
	$disp = $disp."</li><li>";
	$disp = $disp."Virtualization/Virtual Hardware";
	$disp = $disp."</li><li>";
	$disp = $disp."Monitoring";
	$disp = $disp."</li><li>";
	$disp = $disp."High-Availability";
	$disp = $disp."</li><li>";
	$disp = $disp."New Installations and Deployment";
	$disp = $disp."</li><li>";
	$disp = $disp."Resource planning and Provisioning";
	$disp = $disp."</li><li>";
	$disp = $disp."Automatism";
	$disp = $disp."</li><li>";
	$disp = $disp."... and some more";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."Managing 'everything' with a single application sounds like a difficult task but with";
	$disp = $disp." openQRM's plugg-able architecture each component is simply hooked into the openQRM-server API";
	$disp = $disp." to interact with the main framework.";
	$disp = $disp."<br><br>";
	$disp = $disp."openQRM focus is on managing Linux-server environments so let's have a more detailed look on linux itself.";
	$disp = $disp."<br><br>";
	$disp = $disp."'What' is a linux-system ?";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Kernel file (vmlinuz)";
	$disp = $disp."</li><li>";
	$disp = $disp."Ramdisk file (initrd.img)";
	$disp = $disp."</li><li>";
	$disp = $disp."Kernel modules files (/lib/modules/[kernel-version]/";
	$disp = $disp."</li><li>";
	$disp = $disp."Root-filesystem (/)";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."So basically a linux-system is 'just' a bunch of files.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."... so when linux-servers are 'just' a bunch of files we should treat them in the same way as files.";
	$disp = $disp."<br>";
	$disp = $disp."openQRM's rapid-deployment mechanism is therefore concetrating on 'server-imaging' (packaging servers into files) and";
	$disp = $disp." storage-system integration (using modern storage technologies like fast-cloning/snapshotting to manage files).";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."openQRM also abstracts the deployment to allow provisioning of the captured server-images to available physical- or virtual hardware in a generic way.";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Concept', 'value' => documentation_concept());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
