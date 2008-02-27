# this is the openQRM web Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:
	../include/openqrm-build-functions get_phplayersmenu

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web
	. $(OPENQRM_SERVER_CONF) && cp *.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/action
	. $(OPENQRM_SERVER_CONF) && cp action/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base
	. $(OPENQRM_SERVER_CONF) && cp base/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include
	. $(OPENQRM_SERVER_CONF) && cp base/include/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/class
	. $(OPENQRM_SERVER_CONF) && cp base/class/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/class/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/css
	. $(OPENQRM_SERVER_CONF) && cp base/css/*.css $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/css/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu
	. $(OPENQRM_SERVER_CONF) && cp base/img/menu/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server
	#. $(OPENQRM_SERVER_CONF) && cp base/server/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user
	. $(OPENQRM_SERVER_CONF) && cp base/server/user/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/
	#. $(OPENQRM_SERVER_CONF) && cp base/server/user/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/
	# install phplayersmenu
	. $(OPENQRM_SERVER_CONF) && . ../etc/build.conf/phplayersmenu.conf && tar -C $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include/ -xzf $$OPENQRM_BUILD_TMP_DIR/openqrm-build/phplayersmenu/source/phplayersmenu-$$OPENQRM_SOURCE_VERSION-$$OPENQRM_SOURCE_SUB_VERSION.tar.gz
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include/phplayersmenu
	. $(OPENQRM_SERVER_CONF) && . ../etc/build.conf/phplayersmenu.conf && mv $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include/phplayersmenu-$$OPENQRM_SOURCE_VERSION $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include/phplayersmenu
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins
	
	echo "Creating the openqrm-client boot-service package"
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin
	. $(OPENQRM_SERVER_CONF) && cp -a ../sbin/openqrm-execd $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a ../sbin/openqrm-exec-port-monitor $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a ../sbin/openqrm-monitord $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin/
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
