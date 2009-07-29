#
# This file is part of openQRM.
#
# openQRM is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2
# as published by the Free Software Foundation.
#
# openQRM is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
#
# Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
#
Name: openqrm
Summary: next generation data-center management platform.
Version: 4.3
Release: suse.11.1
License: GPL
Group: Networking/Admin
Source: http://puzzle.dl.sourceforge.net/sourceforge/openqrm/openqrm-4.3.tgz
#Prefix: /
#BuildRoot: /tmp/openqrm-packaging/OPENQRM_PACKAGE_NAME
Requires : apache2, php5, php5-mysql, libphp-adodb, mysql-server, syslinux
BuildRequires: make, gcc, pciutils-devel, portmap, rsync, zlib-devel, wget, tar, bzip2
%description
openQRM is the next generation data-center management platform.

%prep
%setup

%build
make

%install
mkdir -p $RPM_BUILD_ROOT/usr/lib
make install DESTINATION_DIR=$RPM_BUILD_ROOT

%post

%preun

%files
%defattr(-,root,root)
/usr/lib/openqrm/bin
/usr/lib/openqrm/etc
/usr/lib/openqrm/include
/usr/lib/openqrm/sbin
/usr/lib/openqrm/tftpboot
/usr/lib/openqrm/web

#%clean
#make clean
#rm -rf $RPM_BUILD_ROOT

%package plugin-aoe-storage
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-aoe-storage
openQRM is the next generation data-center management platform.
%files plugin-aoe-storage
%defattr(-,root,root)
/usr/lib/openqrm/plugins/aoe-storage

%post plugin-aoe-storage
ln -s /usr/lib/openqrm/plugins/aoe-storage/etc/init.d/openqrm-plugin-aoe-storage /etc/init.d/openqrm-plugin-aoe-storage
mkdir -p /var/www/openqrm/base/plugins/aoe-storage

%preun plugin-aoe-storage
/etc/init.d/openqrm-plugin-aoe-storage stop
rm /etc/init.d/openqrm-plugin-aoe-storage
rm -fr /var/www/openqrm/base/plugins/aoe-storage

%package plugin-citrix
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-citrix
openQRM is the next generation data-center management platform.
%files plugin-citrix
%defattr(-,root,root)
/usr/lib/openqrm/plugins/citrix

%post plugin-citrix
ln -s /usr/lib/openqrm/plugins/citrix/etc/init.d/openqrm-plugin-citrix /etc/init.d/openqrm-plugin-citrix
mkdir -p /var/www/openqrm/base/plugins/citrix

%preun plugin-citrix
/etc/init.d/openqrm-plugin-citrix stop
rm /etc/init.d/openqrm-plugin-citrix
rm -fr /var/www/openqrm/base/plugins/citrix

%package plugin-cloud
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm, screen
%description plugin-cloud
openQRM is the next generation data-center management platform.
%files plugin-cloud
%defattr(-,root,root)
/usr/lib/openqrm/plugins/cloud

%post plugin-cloud
ln -s /usr/lib/openqrm/plugins/cloud/etc/init.d/openqrm-plugin-cloud /etc/init.d/openqrm-plugin-cloud
mkdir -p /var/www/openqrm/base/plugins/cloud

%preun plugin-cloud
/etc/init.d/openqrm-plugin-cloud stop
rm /etc/init.d/openqrm-plugin-cloud
rm -fr /var/www/openqrm/base/plugins/cloud

%package plugin-dhcpd
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm, dhcp-server
%description plugin-dhcpd
openQRM is the next generation data-center management platform.
%files plugin-dhcpd
%defattr(-,root,root)
/usr/lib/openqrm/plugins/dhcpd

%post plugin-dhcpd
ln -s /usr/lib/openqrm/plugins/dhcpd/etc/init.d/openqrm-plugin-dhcpd /etc/init.d/openqrm-plugin-dhcpd
mkdir -p /var/www/openqrm/base/plugins/dhcpd

%preun plugin-dhcpd
/etc/init.d/openqrm-plugin-dhcpd stop
rm /etc/init.d/openqrm-plugin-dhcpd
rm -fr /var/www/openqrm/base/plugins/dhcpd

%package plugin-dns
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-dns
openQRM is the next generation data-center management platform.
%files plugin-dns
%defattr(-,root,root)
/usr/lib/openqrm/plugins/dns

%post plugin-dns
ln -s /usr/lib/openqrm/plugins/dns/etc/init.d/openqrm-plugin-dns /etc/init.d/openqrm-plugin-dns
mkdir -p /var/www/openqrm/base/plugins/dns

%preun plugin-dns
/etc/init.d/openqrm-plugin-dns stop
rm -fr /var/www/openqrm/base/plugins/dns

%package plugin-highavailability
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-highavailability
openQRM is the next generation data-center management platform.
%files plugin-highavailability
%defattr(-,root,root)
/usr/lib/openqrm/plugins/highavailability

%post plugin-highavailability
ln -s /usr/lib/openqrm/plugins/highavailability/etc/init.d/openqrm-plugin-highavailability /etc/init.d/openqrm-plugin-highavailability
mkdir -p /var/www/openqrm/base/plugins/highavailability

%preun plugin-highavailability
/etc/init.d/openqrm-plugin-highavailability stop
rm /etc/init.d/openqrm-plugin-highavailability
rm -fr /var/www/openqrm/base/plugins/highavailability

%package plugin-image-shelf
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-image-shelf
openQRM is the next generation data-center management platform.
%files plugin-image-shelf
%defattr(-,root,root)
/usr/lib/openqrm/plugins/image-shelf

%post plugin-image-shelf
ln -s /usr/lib/openqrm/plugins/image-shelf/etc/init.d/openqrm-plugin-image-shelf /etc/init.d/openqrm-plugin-image-shelf
mkdir -p /var/www/openqrm/base/plugins/image-shelf

%preun plugin-image-shelf
/etc/init.d/openqrm-plugin-image-shelf stop
rm /etc/init.d/openqrm-plugin-image-shelf
rm -fr /var/www/openqrm/plugins/image/shelf

%package plugin-iscsi-storage
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-iscsi-storage
openQRM is the next generation data-center management platform.
%files plugin-iscsi-storage
%defattr(-,root,root)
/usr/lib/openqrm/plugins/iscsi-storage

%post plugin-iscsi-storage
ln -s /usr/lib/openqrm/plugins/iscsi-storage/etc/init.d/openqrm-plugin-iscsi-storage /etc/init.d/openqrm-plugin-iscsi-storage
mkdir -p /var/www/openqrm/base/plugins/iscsi/storage

%preun plugin-iscsi-storage
/etc/init.d/openqrm-plugin-iscsi-storage stop
rm /etc/init.d/openqrm-plugin-iscsi-storage
rm -fr /var/www/openqrm/base/plugins/iscsi-storage

%package plugin-kvm
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-kvm
openQRM is the next generation data-center management platform.
%files plugin-kvm
%defattr(-,root,root)
/usr/lib/openqrm/plugins/kvm

%post plugin-kvm
ln -s /usr/lib/openqrm/plugins/kvm/etc/init.d/openqrm-plugin-kvm /etc/init.d/openqrm-plugin-kvm
mkdir -p /var/www/openqrm/base/plugins/kvm

%preun plugin-kvm
/etc/init.d/openqrm-plugin-kvm stop
rm /etc/init.d/openqrm-plugin-kvm
rm -fr /var/www/openqrm/base/plugins/kvm

%package plugin-linux-vserver
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-linux-vserver
openQRM is the next generation data-center management platform.
%files plugin-linux-vserver
%defattr(-,root,root)
/usr/lib/openqrm/plugins/linux-vserver

%post plugin-linux-vserver
ln -s /usr/lib/openqrm/plugins/linux-vserver/etc/init.d/openqrm-plugin-linux-vserver /etc/init.d/openqrm-plugin-linux-vserver
mkdir -p /var/www/openqrm/base/plugins/linux-vserver

%preun plugin-linux-vserver
/etc/init.d/openqrm-plugin-linux-vserver
rm /etc/init.d/openqrm-plugin-linux-vserver
rm -fr /var/www/openqrm/base/plugins/linux-vserver

%package plugin-local-server
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-local-server
openQRM is the next generation data-center management platform.
%files plugin-local-server
%defattr(-,root,root)
/usr/lib/openqrm/plugins/local-server

%post plugin-local-server
ln -s /usr/lib/openqrm/plugins/local-server/etc/init.d/openqrm-plugin-local-server /etc/init.d/openqrm-plugin-local-server
mkdir -p /var/www/openqrm/base/plugins/local-server

%preun plugin-local-server
/etc/init.d/openqrm-plugin-local-server stop
rm /etc/init.d/openqrm-plugin-local-server
rm -fr /var/www/openqrm/base/plugins/local-server

%package plugin-local-storage
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-local-storage
openQRM is the next generation data-center management platform.
%files plugin-local-storage
%defattr(-,root,root)
/usr/lib/openqrm/plugins/local-storage

%post plugin-local-storage
ln -s /usr/lib/openqrm/plugins/local-storage/etc/init.d/openqrm-plugin-local-storage /etc/init.d/openqrm-plugin-local-storage
mkdir -p /var/www/openqrm/base/plugins/local-storage

%preun plugin-local-storage
/etc/init.d/openqrm-plugin-local-storage stop
rm /etc/init.d/openqrm-plugin-local-storage
rm -fr /var/www/openqrm/base/plugins/local-storage

%package plugin-lvm-storage
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-lvm-storage
openQRM is the next generation data-center management platform.
%files plugin-lvm-storage
%defattr(-,root,root)
/usr/lib/openqrm/plugins/lvm-storage

%post plugin-lvm-storage
ln -s /usr/lib/openqrm/plugins/lvm-storage/etc/init.d/openqrm-plugin-lvm-storage /etc/init.d/openqrm-plugin-lvm-storage
mkdir -p /var/www/openqrm/base/plugins/lvm-storage

%preun plugin-lvm-storage
/etc/init.d/openqrm-plugin-lvm-storage stop
rm /etc/init.d/openqrm-plugin-lvm-storage
rm -fr /var/www/openqrm/base/plugins/lvm-storage

%package plugin-nagios2
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-nagios2
openQRM is the next generation data-center management platform.
%files plugin-nagios2
%defattr(-,root,root)
/usr/lib/openqrm/plugins/nagios2

%post plugin-nagios2
ln -s /usr/lib/openqrm/plugins/nagios2/etc/init.d/openqrm-plugin-nagios2 /etc/init.d/openqrm-plugin/nagios2
mkdir -p /var/www/openqrm/base/plugins/nagios2

%preun plugin-nagios2
/etc/init.d/openqrm-plugin-nagios2 stop
rm /etc/init.d/openqrm-plugin-nagios2
rm -fr /var/www/openqrm/base/plugins/nagios2

%package plugin-nagios3
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-nagios3
openQRM is the next generation data-center management platform.
%files plugin-nagios3
%defattr(-,root,root)
/usr/lib/openqrm/plugins/nagios3

%post plugin-nagios3
ln -s /usr/lib/openqrm/plugins/nagios3/etc/init.d/openqrm-plugin-nagios3 /etc/init.d/openqrm-plugin/nagios3
mkdir -p /var/www/openqrm/base/plugins/nagios3

%preun plugin-nagios3
/etc/init.d/openqrm-plugin-nagios3 stop
rm /etc/init.d/openqrm-plugin-nagios3
rm -fr /var/www/openqrm/base/plugins/nagios3

%package plugin-netapp-storage
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-netapp-storage
openQRM is the next generation data-center management platform.
%files plugin-netapp-storage
%defattr(-,root,root)
/usr/lib/openqrm/plugins/netapp-storage

%post plugin-netapp-storage
ln -s /usr/lib/openqrm/plugins/netapp-storage/etc/init.d/openqrm-plugin-netapp-storage /etc/init.d/openqrm-plugin-netapp-storage
mkdir -p /var/www/openqrm/base/plugins/netapp-storage

%preun plugin-netapp-storage
/etc/init.d/openqrm-plugin-netapp-storage stop
rm /etc/init.d/openqrm-plugin-netapp-storage
rm -fr /var/www/openqrm/base/plugins/netapp-storage

%package plugin-nfs-storage
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-nfs-storage
openQRM is the next generation data-center management platform.
%files plugin-nfs-storage
%defattr(-,root,root)
/usr/lib/openqrm/plugins/nfs-storage

%post plugin-nfs-storage
ln -s /usr/lib/openqrm/plugins/nfs-storage/etc/init.d/openqrm-plugin-nfs-storage /etc/init.d/openqrm-plugin-nfs-storage
mkdir -p /var/www/openqrm/base/plugins/nfs-storage

%preun plugin-nfs-storage
/etc/init.d/openqrm-plugin-nfs-storage stop
rm /etc/init.d/openqrm-plugin-nfs-storage
rm -fr /var/www/openqrm/base/plugins/nfs-storage

%package plugin-puppet
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-puppet
openQRM is the next generation data-center management platform.
%files plugin-puppet
%defattr(-,root,root)
/usr/lib/openqrm/plugins/puppet

%post plugin-puppet
ln -s /usr/lib/openqrm/plugins/puppet/etc/init.d/openqrm-plugin-puppet /etc/init.d/openqrm-plugin-puppet
mkdir -p /var/www/openqrm/base/plugins/puppet

%preun plugin-puppet
/etc/init.d/openqrm-plugin-puppet stop
rm /etc/init.d/openqrm-plugin-puppet
rm -fr /var/www/openqrm/base/plugins/puppet

%package plugin-sshterm
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-sshterm
openQRM is the next generation data-center management platform.
%files plugin-sshterm
%defattr(-,root,root)
/usr/lib/openqrm/plugins/sshterm

%post plugin-sshterm
ln -s /usr/lib/openqrm/plugins/sshterm/etc/init.d/openqrm-plugin-sshterm /etc/init.d/openqrm-plugin-sshterm
mkdir -p /var/www/openqrm/base/plugins/sshterm

%preun plugin-sshterm
/etc/init.d/openqrm-plugin-sshterm stop
rm /etc/init.d/openqrm-plugin-sshterm
rm -fr /var/www/openqrm/base/plugins/sshterm

%package plugin-tftpd
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-tftpd
openQRM is the next generation data-center management platform.
%files plugin-tftpd
%defattr(-,root,root)
/usr/lib/openqrm/plugins/tftpd

%post plugin-tftpd
ln -s /usr/lib/openqrm/plugins/tftpd/etc/init.d/openqrm-plugin-tftpd
mkdir -p /var/www/openqrm/base/plugins/tftpd

%preun plugin-tftpd
/etc/init.d/openqrm-plugin-tftpd stop
rm /etc/init.d/openqrm-plugin-tftpd
rm -fr /var/www/openqrm/base/plugins/tftpd

%package plugin-vmware-esx
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-vmware-esx
openQRM is the next generation data-center management platform.
%files plugin-vmware-esx
%defattr(-,root,root)
/usr/lib/openqrm/plugins/vmware-esx

%post plugin-vmware-esx
ln -s /usr/lib/openqrm/plugins/vmware-esx/etc/init.d/openqrm-plugin-vmware-esx /etc/init.d/openqrm-plugin-vmware-esx
mkdir -p /var/www/openqrm/base/plugins/vmware-esx

%preun plugin-vmware-esx
/etc/init.d/openqrm-plugin-vmware-esx stop
rm /etc/init.d/openqrm-plugin-vmware-esx
rm -fr /var/www/openqrm/base/plugins/vmware-esx

%package plugin-vmware-server
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-vmware-server
openQRM is the next generation data-center management platform.
%files plugin-vmware-server
%defattr(-,root,root)
/usr/lib/openqrm/plugins/vmware-server

%post plugin-vmware-server
ln -s /usr/lib/openqrm/plugins/vmware-server/etc/init.d/openqrm-plugin-vmware-server /etc/init.d/openqrm-vmware-server
mkdir -p /etc/init.d/openqrm/base/plugins/vmware-server

%preun plugin-vmware-server
/etc/init.d/openqrm-plugin-vmware-server stop
rm /etc/init.d/openqrm-plugin-vmware-server
rm -fr /var/www/openqrm/base/plugins/vmware-server

%package plugin-vmware-server2
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-vmware-server2
openQRM is the next generation data-center management platform.
%files plugin-vmware-server2
%defattr(-,root,root)
/usr/lib/openqrm/plugins/vmware-server2

%post plugin-vmware-server2
ln -s /usr/lib/openqrm/plugins/vmware-server2/etc/init.d/openqrm-plugin-vmware-server2 /etc/init.d/openqrm-vmware-server2
mkdir -p /etc/init.d/openqrm/base/plugins/vmware-server2

%preun plugin-vmware-server2
/etc/init.d/openqrm-plugin-vmware-server2 stop
rm /etc/init.d/openqrm-plugin-vmware-server2
rm -fr /var/www/openqrm/base/plugins/vmware-server2

%package plugin-windows
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-windows
openQRM is the next generation data-center management platform.
%files plugin-windows
%defattr(-,root,root)
/usr/lib/openqrm/plugins/windows

%post plugin-windows
ln -s /usr/lib/openqrm/plugins/windows/etc/init.d/openqrm-plugin-windows /etc/init.d/openqrm-windows
mkdir -p /etc/init.d/openqrm/base/plugins/windows

%preun plugin-windows
/etc/init.d/openqrm-plugin-windows stop
rm /etc/init.d/openqrm-plugin-windows
rm -fr /var/www/openqrm/base/plugins/windows

%package plugin-xen
Summary: next generation data-center management platform.
Group: Networking/Admin
Requires: openqrm
%description plugin-xen
openQRM is the next generation data-center management platform.
%files  plugin-xen
%defattr(-,root,root)
/usr/lib/openqrm/plugins/xen

%post plugin-xen
ln -s /usr/lib/openqrm/plugins/xen/etc/init.d/openqrm-plugin-xen /etc/init.d/openqrm-plugin-xen
mkdir -p /etc/init.d/openqrm/base/plugins/xen

%preun plugin-xen
/etc/init.d/openqrm-plugin-xen stop
rm /etc/init.d/openqrm-plugin-xen
rm -fr /var/www/openqrm/base/plugins/xen

