Name: openqrm
Summary: next generation data-center management platform.
Version: 4.3-1
Release: suse-1
License: GPL
Group: Networking/Admin
Source: http://puzzle.dl.sourceforge.net/sourceforge/openqrm/openqrm-4.3.tgz
#Prefix: /
#BuildRoot: /tmp/openqrm-packaging/OPENQRM_PACKAGE_NAME
Requires : apache2, php5, php5-mysql, libphp-adodb, mysql-server, syslinux
BuildRequires: make, gcc, pciutils-dev, portmap, rsync, zlib1g-dev, wget, tar, bzip2
%description
openQRM is the next generation data-center management platform.

%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/bin
$RPM_BUILD_ROOT/usr/lib/openqrm/etc
$RPM_BUILD_ROOT/usr/lib/openqrm/include
$RPM_BUILD_ROOT/usr/lib/openqrm/sbin
$RPM_BUILD_ROOT/usr/lib/openqrm/tftpboot
$RPM_BUILD_ROOT/usr/lib/openqrm/web

%prep
%setup

%build
make

%install
mkdir -p $RPM_BUILD_ROOT/usr/lib
make install DESTINATION_DIR=$RPM_BUILD_ROOT

%post

%preun

%clean
make clean
rm -rf $RPM_BUILD_ROOT

%package: openqrm-ng-plugin-dhcpd
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/plugins/dhcpd/bin/* /usr/bin/

%package: openqrm-plugin-aoe-storage
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/aoe-storage

%package: openqrm-plugin-citrix
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/citrix

%package: openqrm-plugin-cloud
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/cloud

%package: openqrm-plugin-dhcpd
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/dhcpd

%package: openqrm-plugin-dns
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/dns

%package: openqrm-plugin-highavailability
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/highavailability

%package: openqrm-plugin-image-shelf
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/image-shelf

%package: openqrm-plugin-iscsi-storage
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/iscsi-storage

%package: openqrm-plugin-kvm
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/kvm

%package: openqrm-plugin-linux-vserver
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/linux-vserver

%package: openqrm-plugin-local-server
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/local-server

%package: openqrm-plugin-local-storage
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/local-storage

%package: openqrm-plugin-lvm-storage
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/lvm-storage

%package: openqrm-plugin-nagios2
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/nagios2

%package: openqrm-plugin-nagios3
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/nagios3

%package: openqrm-plugin-netapp-storage
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/netapp-storage

%package: openqrm-plugin-nfs-storage
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/nfs-storage

%package: openqrm-plugin-puppet
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/puppet

%package: openqrm-plugin-sshterm
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/sshterm

%package: openqrm-plugin-tftpd
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/tftpd

%package: openqrm-plugin-vmware-esx
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/vmware-esx

%package: openqrm-plugin-vmware-server
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/vmware-server

%package: openqrm-plugin-vmware-server2
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/vmware-server2

%package: openqrm-plugin-windows
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/windows

%package: openqrm-plugin-xen
%files
%defattr(-,root,root)
$RPM_BUILD_ROOT/usr/lib/openqrm/plugins/xen

