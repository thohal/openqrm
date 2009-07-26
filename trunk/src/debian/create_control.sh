#!/bin/sh

for SRC_DIR in `find ../plugins -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do
	if [ ! -f $SRC_DIR/deprecated ]; then
		PLUGINNAME=`echo $SRC_DIR | cut -d / -f 3`
		. $SRC_DIR/etc/openqrm-plugin-${PLUGINNAME}.conf
        for SINGLE_PLUGIN_DEPENDENCY in `echo ${OPENQRM_PLUGIN_DEPENDENCIES}`; do
            SINGLE_PLUGIN_DEPENDENCY=`echo ${SINGLE_PLUGIN_DEPENDENCY} | sed -e "s/,//g" | awk {' print $1 '}`
            if [ "${SINGLE_PLUGIN_DEPENDENCY}" != "openqrm-server" ]; then
                if ! echo ${ALL_PLUGINS_DEPENDENCIES} | grep ${SINGLE_PLUGIN_DEPENDENCY} 1>/dev/null; then
                    ALL_PLUGINS_DEPENDENCIES="${ALL_PLUGINS_DEPENDENCIES}, ${SINGLE_PLUGIN_DEPENDENCY}"
                fi
            fi
        done

	fi
done
# add plugin dependencies to control file
cat control.in | sed -e "s/@@PLUGIN_DEPENDENCIES@@/${ALL_PLUGINS_DEPENDENCIES}/g" > control


exit 0


cat control.in > control.new
for SRC_DIR in `find ../plugins -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do
	if [ ! -f $SRC_DIR/deprecated ]; then 
		PLUGINNAME=`echo $SRC_DIR | cut -d / -f 3`
		. $SRC_DIR/etc/openqrm-plugin-${PLUGINNAME}.conf
		echo "" >> control.new
		echo "Package: openqrm-plugin-${PLUGINNAME}" >> control.new
		echo "Architecture: any" >> control.new
		echo "Depends: \${shlibs:Depends}, \${misc:Depends}, openqrm, ${OPENQRM_PLUGIN_DEPENDENCIES}" >> control.new
		echo "Description: This openQRM plugin integrates ${PLUGINNAME}" >> control.new
		echo " ${OPENQRM_PLUGIN_DESCRIPTION}" >> control.new

		echo "usr/lib/openqrm/plugins/${PLUGINNAME}" > openqrm-plugin-${PLUGINNAME}.install

		echo "#!/bin/bash" > openqrm-plugin-${PLUGINNAME}.postinst
		echo "# this is the openqrm-plugin-${PLUGINNAME} postinstall script" >> openqrm-plugin-${PLUGINNAME}.postinst
		echo "ln -s /usr/lib/openqrm/plugins/${PLUGINNAME}/etc/init.d/openqrm-plugin-${PLUGINNAME} /etc/init.d/openqrm-plugin-${PLUGINNAME}" >> openqrm-plugin-${PLUGINNAME}.postinst
		echo "mkdir -p /var/www/openqrm/base/plugins/${PLUGINNAME}" >> openqrm-plugin-${PLUGINNAME}.postinst

		echo "#!/bin/bash" > openqrm-plugin-${PLUGINNAME}.prerm
		echo "# this is the openqrm-plugin-${PLUGINNAME} preremove script" >> openqrm-plugin-${PLUGINNAME}.prerm
		echo "/etc/init.d/openqrm-plugin-${PLUGINNAME} stop" >> openqrm-plugin-${PLUGINNAME}.prerm
		echo "rm /etc/init.d/openqrm-plugin-${PLUGINNAME}" >> openqrm-plugin-${PLUGINNAME}.prerm
		echo "rm -fr /var/www/openqrm/base/plugins/${PLUGINNAME}" >> openqrm-plugin-${PLUGINNAME}.prerm

	fi
done

mv control.new control

