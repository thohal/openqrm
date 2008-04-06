# this is the openQRM dhcpd-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/etc/templates/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/web/img
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/web/img/

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
