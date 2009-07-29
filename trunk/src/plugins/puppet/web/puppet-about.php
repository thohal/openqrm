
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";

function puppet_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/puppet/img/plugin.png\"> Puppet plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<b>The Puppet-plugin</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."The Puppet plugin provides automated configuration-management for appliances in openQRM.";
	$disp = $disp." It seamlessly integrates <a href=\"http://reductivelabs.com/projects/puppet/\" target=\"_BLANK\">Puppet</a> within the openQRM GUI and assists to put specific appliances into pre-made or custom Puppet-classes.";
	$disp = $disp." By enabling the plugin the puppet-environment (server and client) is pre-configured and initialyzed automatically according to best-practice experiences e.g. by keeping the puppet-configuration within a svn-repsitory.";
	$disp = $disp." This puppet-configuration repository is also available for external svn clients. To check out the puppet-repo please run :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b><i>svn co svn+ssh://[user]@[openqrm-server-ip]/usr/lib/openqrm/plugins/puppet/etc/puppet/ .</i></b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Commits the this repository will automatically the puppet configuration at /etc/puppet/*";
	$disp = $disp."<br>";
	$disp = $disp."The puppet-configuration is organized in 'classes', 'goups' and 'appliances'. Own custom classes should be added to the class-directory.";
	$disp = $disp." Classes should be then combined in 'groups' which will be automatically displayed in the puppet-manager.";
	$disp = $disp." The 'appliances' section is fully managed via the puppet-manager user-interface.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."The default puppet-plugin configuration comes with a set of pre-made puppet-classes and groups.";
	$disp = $disp." The available groups are :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."basic-server";
	$disp = $disp."</li><li>";
	$disp = $disp."webserver";
	$disp = $disp."</li><li>";
	$disp = $disp."database-server";
	$disp = $disp."</li><li>";
	$disp = $disp."lamp";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";

	$disp = $disp."<br>";
	$disp = $disp."Those pre-defined groups can of course be adapted and enhanced via the puppet svn repository.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Please notice that the puppet-plugin depends on the dns-plugin !</b>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Make sure to have the dns-plugin enabled and started before the puppet-plugin.</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Go to the 'puppet-manager' in the puppet-plugin menu";
	$disp = $disp."</li><li>";
	$disp = $disp."Select an appliance to automatic configure via puppet";
	$disp = $disp."</li><li>";
	$disp = $disp."Select the puppet-groups the appliance should belong to";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."Within short time the puppet-server will distribute the new configuration to the appliance automatically.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}



$output = array();
$output[] = array('label' => 'About', 'value' => puppet_about());
echo htmlobject_tabmenu($output);

?>


