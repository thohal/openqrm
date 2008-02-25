# this is the openQRM etc Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/etc
	. $(OPENQRM_SERVER_CONF) && cp openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp init.d/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/*   
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/httpd
	. $(OPENQRM_SERVER_CONF) && cp httpd/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/httpd/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp templates/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates/ && chmod 700 $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates/*   
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2 $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres
	. $(OPENQRM_SERVER_CONF) && cp db/db2/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2/
	. $(OPENQRM_SERVER_CONF) && cp db/db2/README $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2/
	. $(OPENQRM_SERVER_CONF) && cp db/mysql/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql/
	. $(OPENQRM_SERVER_CONF) && cp db/mysql/README $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql/
	. $(OPENQRM_SERVER_CONF) && cp db/oracle/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle/
	. $(OPENQRM_SERVER_CONF) && cp db/oracle/README $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle/
	. $(OPENQRM_SERVER_CONF) && cp db/postgres/openqrm* $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres/
	. $(OPENQRM_SERVER_CONF) && cp db/postgres/README $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres/

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/etc

clean:

realclean:	clean

all:	configure compile

.PHONY: all configure compile install uninstall clean realclean


