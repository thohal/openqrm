source ../etc/openqrm-server.conf

echo "Name: openqrm"; \
echo "Summary: next generation data-center management platform."; \
echo "Version: $OPENQRM_SERVER_VERSION"; \
if [ -e /etc/redhat-release ]; then
	echo "Release: rh.1"; \
fi
if [ -e /etc/SuSE-release ]; then 
	echo "Release: suse.11.1"; \
fi
echo "License: GPL"; \
echo "Group: Networking/Admin"; \
echo "Source: http://puzzle.dl.sourceforge.net/sourceforge/openqrm/openqrm-$OPENQRM_SERVER_VERSION.tgz"; \
echo "Requires : apache2, php5, php5-mysql, libphp-adodb, mysql-server, syslinux"; \
echo "BuildRequires: make, gcc, pciutils-devel, portmap, rsync, zlib-devel, wget, tar, bzip2"; \
echo "%description"; \
echo "openQRM is the next generation data-center management platform."; \
echo ""; \
echo "%prep"; \
echo "%setup"; \
echo ""; \
echo "%build"; \
echo "make"; \
echo ""; \
echo "%install"; \
echo "mkdir -p $RPM_BUILD_ROOT/usr/lib"; \
echo "make install DESTINATION_DIR=$RPM_BUILD_ROOT"; \
echo ""; \
echo "%post"; \
echo ""; \
echo "%preun"; \
echo ""; \
echo "%files"; \
echo "%defattr(-,root,root)"; \
echo "/usr/lib/openqrm/bin"; \
echo "/usr/lib/openqrm/etc"; \
echo "/usr/lib/openqrm/include"; \
echo "/usr/lib/openqrm/sbin"; \
echo "/usr/lib/openqrm/tftpboot"; \
echo "/usr/lib/openqrm/web"; \
echo ""; \
echo "#%clean"; \
echo "#make clean"; \
echo "#rm -rf $RPM_BUILD_ROOT"; \
echo ""; \
for name in `ls ../plugins | grep -v Makefile`;  do \
	source ../plugins/$name/etc/openqrm-plugin-$name.conf
	echo "%package "$name; \
	echo "Summary: next generation data-center management platform."; \
	echo "Group: Networking/Admin"; \
	echo "Requires: $OPENQRM_PLUGIN_DEPENDENCIES"; \
	echo "%description plugin-$name"; \
	echo "$OPENQRM_PLUGIN_DESCRIPTION"; \
	echo "%files "$name; \
	echo "%defattr(-,root,root)"; \
	echo "/usr/lib/openqrm/"$name; \
	echo ""; \
	echo "%post plugin-$name"; \
	echo "ln -s /usr/lib/openqrm/plugins/$name/etc/init.d/openqrm-plugin-$name /etc/init.d/openqrm-plugin-$name"; \
	echo "mkdir -p /etc/init.d/openqrm/base/plugins/$name"; \
	echo ""; \
	echo "%preun plugin-$name"; \
	echo "/etc/init.d/openqrm-plugin-$name stop"; \
	echo "rm /etc/init.d/openqrm-plugin-$name"; \
	echo "rm -fr /var/www/openqrm/base/plugins/$name"; \
	echo "";

done
