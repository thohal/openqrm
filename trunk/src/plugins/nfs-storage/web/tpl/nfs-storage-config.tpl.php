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
<h1><img border=0 src="/openqrm/base/plugins/nfs-storage/img/volumes.png"> NFS Volumes on storage {storage_name}</h1>
In case the NFS Storage server is not managed by openQRM please use this form to
 manually create the list of exported paths to server-images on the NFS Storage server.
<br>
<br>
<strong>Please notice that in case a manual configuration exist openQRM will not
 send any automated Storage-authentication commands to this NFS Storage-server !</strong>
<br>
<br>
{back_link}
{storage_table}
<br>
<form action="{formaction}">
<div>
	<div style="float:left;">
        <h4>Exported paths</h4>
        {export_list}
	</div>
	<div style="float:right;">
        <br>
        {exports_list_update_input}
        {hidden_nfs_storage_id}
    </div>
	<div style="clear:both;line-height:0px;">&#160;</div>

	<div style="float:left;">
	{remove}
	</div>
	<div style="float:right;">
	{submit}
	</div>

	<div style="clear:both;line-height:0px;">&#160;</div>
</div>

</form>
