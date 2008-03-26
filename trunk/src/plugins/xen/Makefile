# this is the openQRM xen-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:
	../../make-assistant openqrm_cache_or_download veth etc/build.conf/veth.conf
	../../make-assistant openqrm_compile_from_source veth etc/build.conf/veth.conf

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/xen* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/etc/templates/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web/
	. $(OPENQRM_SERVER_CONF) && chmod 777 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin
	. $(OPENQRM_SERVER_CONF) && cp bin/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/
	. $(OPENQRM_SERVER_CONF) && cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/veth/veth-1.0/veth/vethd $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/bin/openqrm*
	. $(OPENQRM_SERVER_CONF) && tar -C $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen -czvf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/web/boot-service-xen.tgz include/ bin/ etc/init.d/xen etc/templates/openqrm-xen-vm

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
