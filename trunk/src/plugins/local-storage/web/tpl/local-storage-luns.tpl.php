<h1>Logical Volumes of Volume group {local_volume_group} on storage {storage_name}</h1>
{lun_table}
<form action="{formaction}" method="GET">
<h1>Add new Local-Storage location to Volume group {local_volume_group}</h1>
<div style="float:left;">
{local_lun_name}
{local_lun_size}
</div>
{hidden_local_volume_group}
{hidden_local_storage_id}
<div style="text-align:center;">{submit}</div>
</form>
