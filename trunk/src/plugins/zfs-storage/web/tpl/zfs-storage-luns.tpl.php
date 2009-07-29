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
<h1>iSCSI Luns of ZFS zpool {zpool_name} on storage {storage_name}</h1>
{lun_table}
<form action="{formaction}" method="GET">
<h4>Add new Lun on ZFS zpool {zpool_name}</h4>
<div style="float:left;">
{zfs_lun_name}
{zfs_lun_size}
</div>
{hidden_zpool_name}
{hidden_zfs_storage_id}
<div style="text-align:center;">{submit}</div>
</form>