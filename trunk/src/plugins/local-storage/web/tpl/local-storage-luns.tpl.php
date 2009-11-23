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
<h1>Logical Volumes of Volume group {local_volume_group} on storage {storage_name}</h1>
{lun_table}
<form action="{formaction}" method="GET">
<h1><img border=0 src="/openqrm/base/plugins/local-storage/img/volumes.png"> Add new Local-Storage location to Volume group {local_volume_group}</h1>
<div style="float:left;">
{local_lun_name}
{local_lun_size}
</div>
{hidden_local_volume_group}
{hidden_local_storage_id}
<div style="text-align:center;">{submit}</div>
</form>

