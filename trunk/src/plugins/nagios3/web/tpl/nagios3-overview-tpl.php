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


<h1><img border=0 src="/openqrm/base/plugins/nagios3/img/plugin.png"> Automatic configuration</h1>
<form action="{thisfile}" method="POST">
<br>
Click on the button below to automatic map the
<br>
openQRM network into Nagios.
<br>
<br>
Please notice that generating the Nagios configuration
<br>
 will take some time.	You can check the status of this
<br>
action in the <a href="../../server/event/event-overview.php">event-list</a>
<br>
<br>
<input type='hidden' name='action' value='map'>
<input type='submit' value='Map openQRM Network'>
<br>
<br>
<br>
<br>
{automap}
<br>
<br>
<br>
<br>
</form>
