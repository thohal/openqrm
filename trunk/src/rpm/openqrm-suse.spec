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
/usr/lib/openqrm/*

%prep
%setup

%build
make

%install
make install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT/usr/lib
cp -aR /usr/lib/openqrm $RPM_BUILD_ROOT/usr/lib/
OPENQRM_BUILD_POSTINSTALL

%post
OPENQRM_PACKAGE_POSTINSTALL

%preun
OPENQRM_PACKAGE_PREREMOVE

%clean
rm -rf $RPM_BUILD_ROOT
make clean
