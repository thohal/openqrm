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
<h1><img border=0 src="/openqrm/base/plugins/iscsi-storage/img/plugin.png"> iSCSI Volumes on storage {storage_name}</h1>
{lun_table}
<form action="{formaction}" method="GET">
<h1>Add new iSCSI Volume group</h1>
<div style="float:left;">
{iscsi_lun_name}
{iscsi_lun_size}
</div>
{hidden_iscsi_storage_id}
<div style="text-align:center;">{submit}</div>
</form>

