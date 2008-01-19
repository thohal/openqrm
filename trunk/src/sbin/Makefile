# this is the openQRM sbin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:
	make openqrm-exec-port-monitor

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/sbin
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-execd $$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-exec-port-monitor $$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm*

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/sbin

clean:
	rm -f openqrm-exec-port-monitor

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
	