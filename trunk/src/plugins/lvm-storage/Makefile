# this is the openQRM lvm-storage-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.html $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp web/root-mount* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web/img/
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web/img/
	. $(OPENQRM_SERVER_CONF) && chmod 777 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin
	. $(OPENQRM_SERVER_CONF) && cp bin/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/bin/openqrm*
	. $(OPENQRM_SERVER_CONF) && tar -C $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage -czvf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/web/boot-service-lvm-storage.tgz include/ bin/

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lvm-storage

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
