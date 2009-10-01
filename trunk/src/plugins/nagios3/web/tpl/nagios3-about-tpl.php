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

<h1><img border=0 src="/openqrm/base/plugins/nagios3/img/plugin.png"> Nagios3 plugin</h1>
<br>
The nagios3-plugin automatically monitors the systems and services managed by the openQRM-server.
<br>
<br>
<b>How to use :</b>
<br>
To generate and/or update the Nagios configuration for the openQRM-network and managed servers use the 'Config' link in the Nagios-plugin menu.
 The nagios-configuration is then created fully automatically by scanning the network via the 'nmap' utility. The output of the nmap run then is used by 'nmap2nagios-ng' to generate the Nagios-configuration.
<br>
<br>
<br>
