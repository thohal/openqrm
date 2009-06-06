<h1>NFS Volumes on storage {storage_name}</h1>
{storage_table}
<br>
<br>
{lun_table}
<form action="{formaction}" method="GET">
{add_export_header}
<div style="float:left;">
{nfs_lun_name}
</div>
{hidden_nfs_storage_id}
<div style="text-align:center;">{submit}</div>
</form>
