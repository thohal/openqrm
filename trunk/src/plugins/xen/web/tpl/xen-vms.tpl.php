<style>
.htmlobject_tab_box {
	width:800px;
}
</style>
<form action="{formaction}" method="GET">

<h1>Xen Admin</h1>

<div style="float:left;">
{xen_server_table}
</div>

<div style="float:left;">
Xen vms on resource {xen_server_id}/{xen_server_name}
<br>
<br>
{xen_vm_table}
</div>

</form>

