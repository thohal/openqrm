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
Name: OPENQRM_PACKAGE_NAME
Summary: OPENQRM_PACKAGE_NAME
Version: OPENQRM_PACKAGE_VERSION
Release: OPENQRM_PACKAGE_DISTRIBUTION
License: GPL
Group: Networking/Admin
Source: OPENQRM_PACKAGE_NAME-OPENQRM_PACKAGE_VERSION.tgz
Prefix: /
BuildRoot: /tmp/openqrm-packaging/OPENQRM_PACKAGE_NAME
Requires : openqrm, openqrm-plugins
%description
openQRM is the next generation data-center management platform.

%files
%defattr(-,root,root)
/usr/share/openqrm/README.openqrm-entire

%prep
%setup
%build

%install
mkdir -p $RPM_BUILD_ROOT/usr/share/openqrm
echo "This is a META rpm-package containing openqrm + all available pluings" > $RPM_BUILD_ROOT/usr/share/openqrm/README.openqrm-entire

%post
%preun

%clean
rm -rf $RPM_BUILD_ROOT
make clean

