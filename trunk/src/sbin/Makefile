# this is the openQRM sbin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:
	LDFLAGS=-static make openqrm-exec-port-monitor
	. $(OPENQRM_SERVER_CONF) &&  mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/exec-port-monitor
	. $(OPENQRM_SERVER_CONF) &&  mv openqrm-exec-port-monitor $$OPENQRM_BUILD_TMP_DIR/openqrm-build/exec-port-monitor/
	

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-execd $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a $$OPENQRM_BUILD_TMP_DIR/openqrm-build/exec-port-monitor/openqrm-exec-port-monitor $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-monitord $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-postencode.py $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm*

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin

clean:

realclean:	clean
	rm -f $$OPENQRM_BUILD_TMP_DIR/openqrm-build/exec-port-monitor/openqrm-exec-port-monitor
	rmdir $$OPENQRM_BUILD_TMP_DIR/openqrm-build/exec-port-monitor; exit 0

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
	