Name: OPENQRM_PACKAGE_NAME
Summary: OPENQRM_PACKAGE_NAME
Version: OPENQRM_PACKAGE_VERSION
Release: OPENQRM_PACKAGE_DISTRIBUTION
License: GPL
Group: Networking/Admin
Source: OPENQRM_PACKAGE_NAME-OPENQRM_PACKAGE_VERSION.tgz
Prefix: /
BuildRoot: /tmp/openqrm-packaging/OPENQRM_PACKAGE_NAME
Requires : OPENQRM_PACKAGE_DEPENDENCIES
%description
openQRM is the next generation data-center management platform.

%files
%defattr(-,root,root)
/opt/openqrm/*

%prep
%setup

%build
make

%install
make install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT/opt
cp -aR /opt/openqrm $RPM_BUILD_ROOT/opt/
rm -rf $RPM_BUILD_ROOT/opt/openqrm/plugins/*

%post
chmod -x /opt/openqrm/etc/init.d/openqrm-server.postinstall
/opt/openqrm/etc/init.d/openqrm-server.postinstall

%preun
chmod -x /opt/openqrm/etc/init.d/openqrm-server.preremove
/opt/openqrm/etc/init.d/openqrm-server.preremove

%clean
rm -rf $RPM_BUILD_ROOT
make clean
