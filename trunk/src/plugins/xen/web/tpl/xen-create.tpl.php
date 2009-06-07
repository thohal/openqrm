<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<form action="{formaction}" method="GET">

<h1>Xen create VM</h1>

Add new VM to Xen Host id {xen_server_id}
<br>
<br>
<div style="float:left;">
{xen_server_name}
{xen_server_mac}
{xen_server_ram}
{xen_server_disk}
{xen_server_swap}
</div>
{hidden_xen_server_id}
<div style="text-align:center;">
    {submit}
    <br>
    <br>
    <br>
    <br>
    <strong>{backlink}</strong>
</div>



</form>

