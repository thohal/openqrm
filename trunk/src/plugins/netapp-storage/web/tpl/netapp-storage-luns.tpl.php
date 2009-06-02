<h1>NetApp-Storage {storage_name}</h1>
{storage_table}

{lun_table}
<br>
<br>
<form action="{formaction}" method="GET">
<h1>Add NetApp iSCSI Lun :</h1>
<div style="float:left;">
{netapp_lun_name}
{netapp_lun_size}
</div>
{hidden_netapp_storage_id}
<div style="text-align:center;">{submit}</div>
</form>

