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
	width:750px;
}
</style>

<h1>Cloud NAT (natural address translation)</h1>
<br>
<br>
This feature hels for openQRM Setups within a secured network which
just allows one mac-address to be sent out. In this case please confiure
post- and pre-routing to translate your internal Cloud network to the external
ip-addresses via iptables. Then just put your internal and external network
address in here, update the NAT table and enable the CloudNat features
in the main CloudConfig.
<br>
Then all displays and mails containing the Cloud systems ip addresses
will be mapped according the CloudNat setup below.
<br>
<br>
{cloud_nat_table}