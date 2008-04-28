# this is the openQRM web Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web
	. $(OPENQRM_SERVER_CONF) && cp *.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/
	# action
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/action
	. $(OPENQRM_SERVER_CONF) && cp action/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/
	# base
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base
	. $(OPENQRM_SERVER_CONF) && cp base/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/
	# base/include
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include
	. $(OPENQRM_SERVER_CONF) && cp base/include/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include/
	# base/js
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/js
	. $(OPENQRM_SERVER_CONF) && cp base/js/*.js $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/js/
	# base/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/class
	. $(OPENQRM_SERVER_CONF) && cp base/class/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/class/
	# base/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/css
	. $(OPENQRM_SERVER_CONF) && cp base/css/*.css $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/css/
	# base/img/menu
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu
	. $(OPENQRM_SERVER_CONF) && cp base/img/menu/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/*.gif $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/
	. $(OPENQRM_SERVER_CONF) && cp base/img/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/
	# base/server
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server
	# base/server/aa_server
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server
	. $(OPENQRM_SERVER_CONF) && cp base/server/aa_server/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/
	# base/server/resource
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/*.css $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/
	# base/server/user
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user
	. $(OPENQRM_SERVER_CONF) && cp base/server/user/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/
	# base/server/image
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image
	. $(OPENQRM_SERVER_CONF) && cp base/server/image/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/
	. $(OPENQRM_SERVER_CONF) && cp base/server/image/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/
	. $(OPENQRM_SERVER_CONF) && cp base/server/image/*.css $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/
	# base/server/appliance
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance
	. $(OPENQRM_SERVER_CONF) && cp base/server/appliance/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/
	. $(OPENQRM_SERVER_CONF) && cp base/server/appliance/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/
	. $(OPENQRM_SERVER_CONF) && cp base/server/appliance/*.css $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/
	# base/server/storage
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage
	. $(OPENQRM_SERVER_CONF) && cp base/server/storage/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/
	. $(OPENQRM_SERVER_CONF) && cp base/server/storage/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/
	. $(OPENQRM_SERVER_CONF) && cp base/server/storage/*.css $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/
	# base/server/kernel
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel
	. $(OPENQRM_SERVER_CONF) && cp base/server/kernel/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/
	. $(OPENQRM_SERVER_CONF) && cp base/server/kernel/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/
	. $(OPENQRM_SERVER_CONF) && cp base/server/kernel/*.css $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/
	# base/server/event
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event
	. $(OPENQRM_SERVER_CONF) && cp base/server/event/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/
	. $(OPENQRM_SERVER_CONF) && cp base/server/event/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/
	. $(OPENQRM_SERVER_CONF) && cp base/server/event/*.css $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/
	# base/plugins/aa_plugins
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/img
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/img/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/img/

	echo "Creating the openqrm-client boot-service package"
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin
	. $(OPENQRM_SERVER_CONF) && cp -a ../sbin/openqrm* $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/include
	. $(OPENQRM_SERVER_CONF) && cp -a ../include/openqrm-functions $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/etc/init.d/
	. $(OPENQRM_SERVER_CONF) && cp -a ../etc/init.d/openqrm-client $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/etc/init.d/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && tar -C 	$$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/ -czf $$OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service/openqrm-client.tgz `echo $$OPENQRM_RESOURCE_BASE_DIR | cut -d'/' -f2-`
	rm -rf $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client

	
uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_WEBSERVER_DOCUMENT_ROOT/openqrm
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/web/*
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/web

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
