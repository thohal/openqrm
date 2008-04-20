# this is the openQRM linux-vserver-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:
	../../make-assistant openqrm_cache_or_download veth etc/build.conf/veth.conf
	../../make-assistant openqrm_compile_from_source veth etc/build.conf/veth.conf

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/linux-vserver* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/etc/templates/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/web/img
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/web/img/
	. $(OPENQRM_SERVER_CONF) && chmod 777 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/web
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin
	. $(OPENQRM_SERVER_CONF) && cp bin/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/
	. $(OPENQRM_SERVER_CONF) && cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/veth/veth-1.0/veth/vethd $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/bin/openqrm*
	. $(OPENQRM_SERVER_CONF) && tar -C $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver -czvf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/web/boot-service-linux-vserver.tgz include/ bin/ etc/init.d/linux-vserver etc/templates/openqrm-linux-vserver-vm

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/linux-vserver

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
