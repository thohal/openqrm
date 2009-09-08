<!--
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
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
body
{
	background: #fff;
	color: #000;
}
a
{
	color: #99f;
}
a:hover
{
	color: #99c;
}
.linksTooltip
{
	border: 1px solid #000;
	background-color: #555;
	padding: 10px;
	color: #ccc;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	width: 250px;
}
.linksTooltip #tooltipTitle
{
	font-weight: bold;
}


</style>
</head>

<body>
<div id="eventlist">

{event_table}

</div>
<script type="text/javascript">

$('a').ToolTip(
	{
		className: 'linksTooltip',
		position: 'mouse',
		delay: 200
	}
);


</script>
<noscript>
</noscript>


</body>
</html>
