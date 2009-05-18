<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<h1>AOE Volumes on storage {storage_name}</h1>

{lun_table}

<form action="{formaction}" method="GET">
Add new AOE Volume group
<div style="float:left;">
{aoe_lun_name}
{aoe_lun_size}
</div>
{hidden_aoe_storage_id}
<div style="text-align:center;">{submit}</div>



</form>

