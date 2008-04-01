# this is the openQRM nagios2-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/etc/init.d/
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/nagios* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/etc/templates/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/web/
	. $(OPENQRM_SERVER_CONF) && tar -C $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2 -czvf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/web/boot-service-nagios2.tgz etc/init.d/nagios2

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios2

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
