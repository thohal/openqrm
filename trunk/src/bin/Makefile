# this is the openQRM bin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/bin
	. $(OPENQRM_SERVER_CONF) && cp openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/bin/ && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/bin/*

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/bin/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/bin

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
