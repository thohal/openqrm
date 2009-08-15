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
<h1><img border=0 src="/openqrm/base/plugins/collectd/img/plugin.png"> Collectd-plugin</h1>
<br>
The Collectd plugin provides automated monitoring and system graphs for appliances in openQRM.
 It seamlessly integrates <a href="http://collectd.org/" target="_BLANK">Collectd</a> within openQRM
 and provides hourly, daily, weekly and monthly system graphs created from the collected data via <a href="http://oss.oetiker.ch/rrdtool/" target="_BLANK">rrdtool</a>.
 By enabling the plugin collectd is pre-configured and initialyzed automatically.
 The system graphs are updated sequentially via a cron-job.
<br>
<br>
It may take some minutes after the start of an appliance to collect enough data to create the graphs.
<br>
<br>
<br>
