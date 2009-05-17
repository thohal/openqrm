<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<form action="{formaction}" method="GET">

<h1>KVM-Server-Admin</h1>

<div style="float:left;">
{kvm_server_table}
</div>

<div style="float:left;">
KVM vms on resource {kvm_server_id}/{kvm_server_name}
<br>
<br>
{kvm_vm_table}
</div>

</form>

