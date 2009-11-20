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

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/cloudipgroups.png"> Load ip-adresses into Cloud IpGroup {ig_name}</h1>
<br>
<form action={thisfile} method=post>
<table><tr><td>
</td><td>
Please cut-and-paste a block of ip-addresses for the IpGroup {ig_name} into the box on the right and click 'Load'. to activate them in the Cloud Ip-Pool.
</td><td>
<textarea name="cloud_ips" cols="20" rows="20"></textarea>
<input type=hidden name=ig_id value={ipgroup}>
<input type=hidden name='action' value='load_ipgroup'>
</td><td>
</td></tr><tr><td>
</td><td>
</td><td>
<input type=submit value='Load'>
</td><td>
</td></tr></table>
</form>

