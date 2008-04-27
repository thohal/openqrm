# this is the openQRM local-server-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/bin
	. $(OPENQRM_SERVER_CONF) && cp bin/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/bin/openqrm*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/web/img
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/web/img/

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-server

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
