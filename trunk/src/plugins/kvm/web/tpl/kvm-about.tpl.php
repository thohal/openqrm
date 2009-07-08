<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<div style="float:left;">

<h1><img border=0 src="/openqrm/base/plugins/kvm/img/plugin.png"> KVM plugin</h1>
<strong>This plugin is tested with KVM kvm-62</strong>
<br>
<strong>To benefit from KVM's 'virtio' feature at least kvm-84 is needed</strong>
<br>
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
 installing openQRM. At least an "internal" bridge for the openQRM management network
 is needed. The name for this bridge can be configured in the KVM plugin-configuration file
 via the parameter OPENQRM_PLUGIN_KVM_INTERNAL_BRIDGE.
<br>
<br>
Additional an external bridge (e.g. pointing to the internet) can be setup and configured
 via the OPENQRM_PLUGIN_KVM_EXTERNAL_BRIDGE parameter in the KVM plugin-configuration file.
<br>
openQRM then will create every first (virtual) network-card for the KVM vms on the internal
 bridge and every other on the external one. With this 2-bridge setup every vm will then
 have its first nic pointing to the openQRM management network (doing the pxe-boot)
 and every other nic will point e.g. to the internet.
<br>
<br>
After having a network-bridge configured openQRM should be installed
 on the internal bridge-interface (by default br0). This can be done by setting the openQRM management
 network-interface in /usr/lib/openqrm/etc/openqrm-server.conf to br0 before initalyzing openQRM.
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
