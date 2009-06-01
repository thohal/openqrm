<h1>iSCSI Volumes on storage {storage_name}</h1>
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

