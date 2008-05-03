<?php
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";



function documentation_installation() {

	$disp = "<h1>Installation</h1>";
	$disp = $disp."<br>";
	$disp = $disp."openQRM can be easily installed from its source or using the provided binary packages.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Install openQRM from its sources :</b>";


	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Get the latest sources from the openQRM svn-repository on sourceforge.net";
	$disp = $disp."<br>";
	$disp = $disp."<i>svn co https://openqrm.svn.sourceforge.net/svnroot/openqrm openqrm</i>";
	$disp = $disp."</li><li>";
	$disp = $disp."Change to the source directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>cd openqrm/trunk/src</i>";
	$disp = $disp."</li><li>";
	$disp = $disp."run <b>make</b>";
	$disp = $disp."<br>";
	$disp = $disp."<i>make</i>";
	$disp = $disp."</li><li>";
	$disp = $disp."run <b>make install</b>";
	$disp = $disp."<br>";
	$disp = $disp."(as root)";
	$disp = $disp."<br>";
	$disp = $disp."<i>make install</i>";
	$disp = $disp."</li><li>";
	$disp = $disp."run <b>make start</b>";
	$disp = $disp."<br>";
	$disp = $disp."(as root)";
	$disp = $disp."<br>";
	$disp = $disp."<i>make start</i>";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."Please notice that package dependencies for the build-procedure are solved automatically !";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Install openQRM from binary packages :</b>";
	$disp = $disp."<br>";
	$disp = $disp."To install openQRM from binary packages first select the package(s) to download from";
	$disp = $disp." the sourceforge-project page. openQRM is packaged in the following way :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."<b>openqrm-server-entire</b>";
	$disp = $disp."<br>";
	$disp = $disp."Contains the openQRM-server and all plugins";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>openqrm-server</b>";
	$disp = $disp."<br>";
	$disp = $disp."Contains just the base openQRM-server (no plugins)";
	$disp = $disp."</li><li>";
	$disp = $disp."<b>openqrm-plugin-[plugin-name]</b>";
	$disp = $disp."<br>";
	$disp = $disp."Each plugin as a separated package";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."Then just use the standard package-manager of your distribution to install the packages.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>After a successfull installation :</b>";
	$disp = $disp."<br>";
	$disp = $disp."After the installation you can access the openQRM-server at :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>http://[ip-address]/openqrm</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."where [ip-address] is the ip-address of the system you installed openQRM on.";
	$disp = $disp."<br>";
	$disp = $disp."You can login with the user 'openqrm' and the password 'openqrm'.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Please make sure to change the default password after first login!";
	$disp = $disp."<br>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Installation', 'value' => documentation_installation());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
