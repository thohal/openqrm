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
<h1><img border=0 src="/openqrm/base/plugins/puppet/img/plugin.png"> Puppet Plugin</h1>

The Puppet plugin provides automated configuration-management for appliances in openQRM.
 It seamlessly integrates <a href="http://reductivelabs.com/projects/puppet/" target="_BLANK">Puppet</a> within the openQRM GUI and assists to put specific appliances into pre-made or custom Puppet-classes.
 By enabling the plugin the puppet-environment (server and client) is pre-configured and initialyzed automatically according to best-practice experiences e.g. by keeping the puppet-configuration within a svn-repsitory.
 This puppet-configuration repository is also available for external svn clients. To check out the puppet-repo please run :
<br>
<br>
<b><i>svn co svn+ssh://[user]@[openqrm-server-ip]/usr/lib/openqrm/plugins/puppet/etc/puppet/ .</i></b>
<br>
<br>
Commits the this repository will automatically the puppet configuration at /etc/puppet/*
<br>
The puppet-configuration is organized in 'classes', 'goups' and 'appliances'. Own custom classes should be added to the class-directory.
 Classes should be then combined in 'groups' which will be automatically displayed in the puppet-manager.
 The 'appliances' section is fully managed via the puppet-manager user-interface.
<br>
<br>
The default puppet-plugin configuration comes with a set of pre-made puppet-classes and groups.
 The available groups are :
<ul>
<li>
basic-server
</li><li>
webserver
</li><li>
database-server
</li><li>
lamp
</li>
</ul>
<br>
Those pre-defined groups can of course be adapted and enhanced via the puppet svn repository.
<br>
<br>
<b>Please notice that the puppet-plugin depends on the dns-plugin !</b>
<br>
<b>Make sure to have the dns-plugin enabled and started before the puppet-plugin.</b>
<br>
<br>
<b>How to use :</b>
<br>
<ul>
<li>
Go to the 'puppet-manager' in the puppet-plugin menu
</li><li>
Select an appliance to automatic configure via puppet
</li><li>
Select the puppet-groups the appliance should belong to
</li>
</ul>
<br>
Within short time the puppet-server will distribute the new configuration to the appliance automatically.

<br>
