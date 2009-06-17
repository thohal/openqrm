<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<form action="{formaction}" method="GET">

<h1>Authenticate Citrix XenServer {citrix_server_id}</h1>

<div style="float:left;">
{citrix_server_user}
{citrix_server_password}
</div>
{hidden_citrix_server_id}
{hidden_citrix_server_ip}
{hidden_action}
<div style="text-align:center;">
    {submit}
    <br>
    <br>
    <br>
    <br>
    <strong>{backlink}</strong>
</div>



</form>
