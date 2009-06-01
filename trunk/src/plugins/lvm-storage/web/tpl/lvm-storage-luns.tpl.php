<h1>Logical Volumes of Volume group {lvm_volume_group} on storage {storage_name}</h1>
{lun_table}
<form action="{formaction}" method="GET">
<h1>Add new logical volume to Volume group {lvm_volume_group}</h1>
<div style="float:left;">
{lvm_lun_name}
{lvm_lun_size}
</div>
{hidden_lvm_volume_group}
{hidden_lvm_storage_id}
<div style="text-align:center;">{submit}</div>
</form>

