# this is the openQRM local-deployment-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf
export OPENQRM_PLUGIN_LOCAL_DEPLOYMENT_DIR=$(shell pwd)

configure:

compile:
	
	. $(OPENQRM_SERVER_CONF) && ../../make-assistant openqrm_cache_or_download partimage $(OPENQRM_PLUGIN_LOCAL_DEPLOYMENT_DIR)/etc/build.conf/partimage.conf
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/partimage.conf && cd $$OPENQRM_BUILD_TMP_DIR/openqrm-build/partimage/ && tar -xjf partimage-$$OPENQRM_SOURCE_VERSION.tar.bz2

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.local-deployment $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/web/
	. $(OPENQRM_SERVER_CONF) && cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/partimage/partimage $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/web/img
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/web/img/


uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-deployment

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
