<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<div style="float:left;">

<h1><img border=0 src="/openqrm/base/plugins/kvm/img/plugin.png"> KVM plugin</h1>
<br>
The KVM plugin adds support for KVM-Virtualization to openQRM.
 Appliances with the resource-type 'KVM Host' are listed in the KVM-Manager and
 can be managed via the openQRM GUI. Additional to the regular partition commands
 like create/start/stop/remove the KVM-plugin provides a configuration form per vm
 to re-configure the partition as needed (e.g. adding a virtual network card or harddisks).
<br>
<br>
Hint:
<br>
The openQRM-server itself can be used as a resource for an KVM-Host appliance.
 In this case network-bridging should be setup on openQRM-server system before
 installing openQRM. After having a network-bridge configured openQRM should be installed

 on the bridge-interface (br0).
<br>
On managed resources a network-bridge (br0) for the KVM vms is created automatically
 during start of the KVM-plugin (if not already existing). This bridge (named br0)
 is then used for the virtual network-interfaces of the partitions.
<br>
<br>
<br>
<b>How to use :</b>
<br>

<ul>
<li>
Create an appliance and set its resource-type to 'KVM Host'
</li><li>
Use the 'VM Manager' in the Kvm-plugin menu to create a new Kvm-server virtual-machines on the Host
</li><li>
 The created Kvm-server vm is then booting into openQRM as regular resources
</li>
</ul>
<br>

</div>

</form>

