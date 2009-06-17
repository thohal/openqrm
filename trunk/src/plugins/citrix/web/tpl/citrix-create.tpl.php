<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<form action="{formaction}" method="GET">

<h1>Citrix XenServer create VM</h1>

Add new VM to Citrix XenServer Host id {citrix_server_id}
<br>
<br>
<div style="float:left;">
{citrix_server_name}
{citrix_server_mac}
{citrix_server_ram}
{template_list_select}
Please select one of the HVM templates supporting PXE-boot

</div>
{hidden_citrix_server_id}
<div style="text-align:center;">
    {submit}
    <br>
    <br>
    <br>
    <br>
    <strong>{backlink}</strong>
</div>



</form>
