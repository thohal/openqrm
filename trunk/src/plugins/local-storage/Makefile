# this is the openQRM local-storage-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf
export OPENQRM_PLUGIN_LOCAL_DEPLOYMENT_DIR=$(shell pwd)

configure:

compile:
	
	. $(OPENQRM_SERVER_CONF) && ../../make-assistant openqrm_cache_or_download partimage $(OPENQRM_PLUGIN_LOCAL_DEPLOYMENT_DIR)/etc/build.conf/partimage.conf
	. $(OPENQRM_SERVER_CONF) && . etc/build.conf/partimage.conf && cd $$OPENQRM_BUILD_TMP_DIR/openqrm-build/partimage/ && tar -xjf partimage-$$OPENQRM_SOURCE_VERSION.tar.bz2

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.local-deployment $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/partimage/partimage $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/web/img
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/web/img/


uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
