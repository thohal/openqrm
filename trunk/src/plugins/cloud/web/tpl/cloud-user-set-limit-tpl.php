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

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> Set Limits for Cloud User {cu_name}</h1>
(0 => infinite)
<br>
<form action={thisfile} method=post>

{cl_resource_limit}
{cl_memory_limit}
{cl_disk_limit}
{cl_cpu_limit}
{cl_network_limit}

<input type=hidden name='cl_cu_id' value={cloud_user_id}>
<input type=hidden name='action' value='limit'>
<br>
<input type=submit value='Set-Limits'>
<br>
</form>
