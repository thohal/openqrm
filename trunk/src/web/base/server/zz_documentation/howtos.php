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



function documentation_howtos() {

	$disp = "<h1>HowTo's</h1>";
	$disp = $disp."<br>";
	$disp = $disp."This section provides HowTo's (and links to HowTo's) guiding how to manage different scenarios with openQRM.";
	$disp = $disp."<ul>";
	$disp = $disp."<li>";
	$disp = $disp."<a href='quickstart.php'>Get started</a>";
	$disp = $disp."</li><li>";
	$disp = $disp."(more soon)";
	$disp = $disp."</li>";
	$disp = $disp."</ul>";
	$disp = $disp."<br>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	return $disp;
}




$output = array();
$output[] = array('label' => 'HowTos', 'value' => documentation_howtos());

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="documentation.css" />
<?php
echo htmlobject_tabmenu($output);
?>
