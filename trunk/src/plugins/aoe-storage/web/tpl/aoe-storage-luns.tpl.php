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
?>
<h1>AOE Volumes on storage {storage_name}</h1>
{lun_table}
<form action="{formaction}" method="GET">
<h1>Add new AOE Volume group</h1>
<div style="float:left;">
{aoe_lun_name}
{aoe_lun_size}
</div>
{hidden_aoe_storage_id}
<div style="text-align:center;">{submit}</div>
</form>

