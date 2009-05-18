<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<h1>iSCSI Volumes on storage {storage_name}</h1>

{lun_table}

<form action="{formaction}" method="GET">
Add new AOE Volume group
<br>
<br>
<div style="float:left;">
{iscsi_lun_name}
{iscsi_lun_size}
</div>
{hidden_iscsi_storage_id}
<div style="text-align:center;">{submit}</div>

</form>

