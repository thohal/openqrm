<!--
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/
-->
<style>
.htmlobject_tab_box {
	width:600px;
}

a {
    text-decoration:none
}

#cloudoffer {
    position: absolute;
    left: 220px;
    top: 265px;
    width: 250px;
    height: 20px;
}


#cpu {
    position: absolute;
    left: 100px;
    top: 280px;
    width: 120px;
    height: 70px;
}

#disk {
    position: absolute;
    left: 185px;
    top: 225px;
    width: 100px;
    height: 70px;
}

#ha {
    position: absolute;
    left: 280px;
    top: 210px;
    width: 100px;
    height: 70px;
}

#kernel {
    position: absolute;
    left: 360px;
    top: 200px;
    width: 100px;
    height: 70px;
}

#memory {
    position: absolute;
    left: 420px;
    top: 225px;
    width: 100px;
    height: 70px;
}

#network {
    position: absolute;
    left: 500px;
    top: 280px;
    width: 100px;
    height: 70px;
}

#puppet {
    position: absolute;
    left: 420px;
    top: 320px;
    width: 100px;
    height: 70px;
}

#quantity {
    position: absolute;
    left: 320px;
    top: 340px;
    width: 100px;
    height: 70px;
}

#resource {
    position: absolute;
    left: 190px;
    top: 310px;
    width: 100px;
    height: 70px;
}

</style>

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> Cloud Products for portal <small><a href="{external_portal_name}" target="_BLANK">{external_portal_name}</a></small></h1>

&nbsp;&nbsp;&nbsp;&nbsp;<img src="img/cloudofferings.png" width="518" height="233" alt="cloudofferings"/>


<div id="cloudoffer" class="link">
<h2>Define Cloud Offerings</h2>
</div>


<div id="cpu" class="link">
<a href="cloud-selector.php?product_type=cpu"><img src="img/cloudproduct_cpu.png" border="0" width="24" height="24" alt="cloudproduct_cpu"/>
<br><strong>CPU</strong></a>
</div>

<div id="disk" class="link">
<a href="cloud-selector.php?product_type=disk">&nbsp;<img src="img/cloudproduct_disk.png" border="0" width="24" height="24" alt="cloudproduct_disk"/>
<br><strong>Disk</strong></a>
</div>

<div id="ha" class="link">
<a href="cloud-selector.php?product_type=ha"><img src="img/cloudproduct_ha.png" border="0" width="24" height="24" alt="cloudproduct_ha"/>
<br><strong>&nbsp;HA</strong></a>
</div>

<div id="kernel" class="link">
<a href="cloud-selector.php?product_type=kernel">&nbsp;&nbsp;<img src="img/cloudproduct_kernel.png" border="0" width="24" height="24" alt="cloudproduct_kernel"/>
<br><strong>Kernel</strong></a>
</div>

<div id="memory" class="link">
<a href="cloud-selector.php?product_type=memory">&nbsp;&nbsp;&nbsp;<img src="img/cloudproduct_memory.png" border="0" width="24" height="24" alt="cloudproduct_memory"/>
<br><strong>Memory</strong></a>
</div>

<div id="network" class="link">
    <a href="cloud-selector.php?product_type=network">&nbsp;&nbsp;&nbsp;<img src="img/cloudproduct_network.png" border="0" width="24" height="24" alt="cloudproduct_network"/>
    <br><strong>Network</strong></a>
</div>

<div id="puppet" class="link">
<a href="cloud-selector.php?product_type=puppet">&nbsp;&nbsp;&nbsp;<img src="img/cloudproduct_puppet.png" border="0" width="24" height="24" alt="cloudproduct_puppet"/>
<br><strong>Puppet</strong></a>
</div>

<div id="quantity" class="link">
<a href="cloud-selector.php?product_type=quantity">&nbsp;&nbsp;&nbsp;<img src="img/cloudproduct_quantity.png" border="0" width="24" height="24" alt="cloudproduct_quantity"/>
<br><strong>Quantity</strong></a>
</div>

<div id="resource" class="link">
<a href="cloud-selector.php?product_type=resource">&nbsp;&nbsp;&nbsp;<img src="img/cloudproduct_resource.png" border="0" width="24" height="24" alt="cloudproduct_resource"/>
<br><strong>Resource</strong></a>
</div>



