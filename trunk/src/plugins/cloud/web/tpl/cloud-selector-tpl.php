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
    left: 250px;
    top: 260px;
    width: 150px;
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
    top: 215px;
    width: 100px;
    height: 70px;
}

#ha {
    position: absolute;
    left: 280px;
    top: 200px;
    width: 100px;
    height: 70px;
}

#kernel {
    position: absolute;
    left: 360px;
    top: 190px;
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
    top: 330px;
    width: 100px;
    height: 70px;
}

#quantity {
    position: absolute;
    left: 320px;
    top: 360px;
    width: 100px;
    height: 70px;
}

#resource {
    position: absolute;
    left: 190px;
    top: 330px;
    width: 100px;
    height: 70px;
}

</style>

<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> Cloud Selector for portal <small><a href="{external_portal_name}" target="_BLANK">{external_portal_name}</a></small></h1>

&nbsp;&nbsp;&nbsp;&nbsp;<img src="img/cloudofferings.png" width="518" height="233" alt="cloudofferings"/>


<div id="cloudoffer" class="link">
<h4>Define Cloud Offerings</h4>
</div>


<div id="cpu" class="link">
<a href="cloud-selector.php?product_type=cpu"><strong>CPU</strong></a>
</div>

<div id="disk" class="link">
<a href="cloud-selector.php?product_type=disk"><strong>Disk</strong></a>
</div>

<div id="ha" class="link">
<a href="cloud-selector.php?product_type=ha"><strong>HA</strong></a>
</div>

<div id="kernel" class="link">
<a href="cloud-selector.php?product_type=kernel"><strong>Kernel</strong></a>
</div>

<div id="memory" class="link">
<a href="cloud-selector.php?product_type=memory"><strong>Memory</strong></a>
</div>

<div id="network" class="link">
<a href="cloud-selector.php?product_type=network"><strong>Network</strong></a>
</div>

<div id="puppet" class="link">
<a href="cloud-selector.php?product_type=puppet"><strong>Puppet</strong></a>
</div>

<div id="quantity" class="link">
<a href="cloud-selector.php?product_type=quantity"><strong>Quantity</strong></a>
</div>

<div id="resource" class="link">
<a href="cloud-selector.php?product_type=resource"><strong>Resource</strong></a>
</div>



