<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<form action="{formaction}" method="GET">

<h1>KVM-server Create VM</h1>

Add new VM to KVM Host id {kvm_server_id}
<br>
<br>
<div style="float:left;">
{kvm_server_name}
{kvm_server_mac}
{kvm_server_ram}
{kvm_server_disk}
</div>
{hidden_kvm_server_id}
<div style="text-align:center;">{submit}</div>



</form>

