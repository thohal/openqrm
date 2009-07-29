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



function documentation_development() {

	$disp = "<h1>Development</h1>";

	$disp = $disp."<br>";
	$disp = $disp."We (the openQRM-Team) made it easy for you to help developing openQRM and especially creating new plugins.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Features of the openQRM build-system :";

	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Automatically solves build-dependencies";
	$disp = $disp."</li><li>";
	$disp = $disp."Automatically creates binary packages (rpm + dep)";
	$disp = $disp."</li><li>";
	$disp = $disp."Automatically solves run-time dependencies";
	$disp = $disp."</li><li>";
	$disp = $disp."Automatically downloads and caches third-party components";
	$disp = $disp."</li><li>";
	$disp = $disp."Automatically compiles and caches third-party components";
	$disp = $disp."</li><li>";
	$disp = $disp."It's fast";
	$disp = $disp."</li><li>";
	$disp = $disp."Creating new plugin is made very easy";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";

	$disp = $disp."To start developing openQRM or openQRM-plugin first step is to get the latest sources from the svn repository and compile it yourself.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Compile (and install) openQRM from its sources :</b>";


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
	$disp = $disp."<br>";
	$disp = $disp."Now you are ready to start developing :)";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Quick re-install after changing the sources</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."To make re-installation easy and fast we included a 're-install' target in the Makefile. To quickly re-install openQRM after changing the sources you can use the following commands :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Change to the source directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>cd openqrm/trunk/src</i>";
	$disp = $disp."</li><li>";
	$disp = $disp."run <b>make reinstall</b>";
	$disp = $disp."<br>";
	$disp = $disp."<i>make reinstall</i>";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to create an openQRM plugin</b>";
	$disp = $disp."<br>";
	$disp = $disp."To make plugin-creation easy please find a 'openqrm-create-plugin' script in the openQRM's bin directory (default /usr/lib/openqrm/bin/openqrm-create-plugin).";
	$disp = $disp."<br>";
	$disp = $disp."This script requires 2 commandline parameter :";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<i>./openqrm-create-plugin [source-plugin-name] [new-plugin-name]</i>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."It creates a copy of the [source-plugin-name] and renames it  to [new-plugin-name].";
	$disp = $disp." The re-naming procedure includes dir- and file-names plus file-content.";
	$disp = $disp." After running the script you will have a 'working' template for the new plugin and";
	$disp = $disp." you can then leverage it to your needs.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>Creating binary packages</b>";
	$disp = $disp."<br>";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."Change to the source directory";
	$disp = $disp."<br>";
	$disp = $disp."<i>cd openqrm/trunk/src</i>";
	$disp = $disp."</li><li>";
	$disp = $disp."run <b>make package</b>";
	$disp = $disp."<br>";
	$disp = $disp."<i>make package</i>";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."The result/output of this command are binary packages (rpm or dep packages depending on which distribution you are building) located in the defined openQRM package dir (/tmp/ by default, defined in [source-dir]/etc/openqrm-server.conf)";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Development', 'value' => documentation_development());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
