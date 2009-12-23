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

    // check if configured already
    if (file_exists("./unconfigured")) {
        header("Location: configure.php");
    }
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<html>
	<head>
		<title>openQRM-Server</title>
		<style>
			.left-border {
				border:1px solid;
			}
		</style>
	</head>


	<frameset rows="100,*" frameborder="0" framespacing="0" border="0">
		<frame src="top.php" name="TopFrame" noresize scrolling="no" marginwidth="0" marginheight="0">
		<frameset cols="200,*" frameborder="0" framespacing="0" border="0">
			<frameset rows="47,*" frameborder="0" framespacing="0" border="0">	
				<frame src="spacer.php" name="spacer" noresize scrolling="no" marginwidth="0" marginheight="0">
				<frame src="menu.php" name="NaviFrame" marginwidth="0" marginheight="0">
			</frameset>
			<frame src="server/aa_server/dc-overview.php" name="MainFrame" marginwidth="0" marginheight="0">
		</frameset>
		<noframes>
			<body>
				<h1>openQRM-Server</h1>
				<p>Please use a browser which supports HTML-frames</p>
			</body>
		</noframes>
	</frameset>
</html>