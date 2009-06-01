<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<h1>NFS Volumes on storage {storage_name}</h1>

{lun_table}

<form action="{formaction}" method="GET">
<br>
<br>
Add new NFS export

<br>
<div style="float:left;">
{nfs_lun_name}
</div>
{hidden_nfs_storage_id}
<div style="text-align:center;">{submit}</div>

</form>
