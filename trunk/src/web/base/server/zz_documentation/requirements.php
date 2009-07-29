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



function documentation_requirements() {

	$disp = "<h1>Requirements</h1>";
	$disp = $disp."<br>";
	$disp = $disp."openQRM-server is fully scalable and supports a distributed setup using a remote data-base";
	$disp = $disp." and remote storage-servers. This sections explains the requirements on a 'standard' and an 'advanced' setup.";
	$disp = $disp."<br>";
	$disp = $disp."Standard setup :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."one linux box to install openQRM-server on";
	$disp = $disp."</li><li>";
	$disp = $disp."one or more physical systems to be managed by openQRM";
	$disp = $disp."<br>";
	$disp = $disp."(for limited testing this also can be (full) virtualized systems e.g. via VMware or QEMU)";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Advanced (recommended) setup :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."one linux box to install openQRM-server on";
	$disp = $disp."<br>";
	$disp = $disp."(for a HA setup of the openQRM-server two systems are needed)";
	$disp = $disp."</li><li>";
	$disp = $disp."a high-available database-server (remote)";
	$disp = $disp."</li><li>";
	$disp = $disp."one or more high-available storage-servers";
	$disp = $disp."</li><li>";
	$disp = $disp."one or more physical systems to be managed by openQRM";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."General system requirements for openQRM-server :";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."1 GB ram (or more)";
	$disp = $disp."</li><li>";
	$disp = $disp."A data-base (can be MySql, Postgres, Oracle or DB2)";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."All other (e.g. package-) dependencies are solved by openQRM automatically !";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}




$output = array();
$output[] = array('label' => 'Requirements', 'value' => documentation_requirements());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
