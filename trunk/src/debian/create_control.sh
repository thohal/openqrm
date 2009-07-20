
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
	fi
done



