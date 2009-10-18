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
<style>
.htmlobject_tab_box {
	width:750px;
}
</style>

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> Create new Cloud IpGroup</h1>
<br>
<form action={thisfile} method=post>

{ig_name}
{ig_network}
{ig_subnet}
{ig_gateway}
{ig_dns1}
{ig_dns2}
{ig_domain}

<input type=hidden name='action' value='create_ipgroup'>
<br>
<input type=submit value='Create'>
<br>
</form>

