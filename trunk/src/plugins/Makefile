# this is the main openQRM plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

all:	configure compile
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make all && cd ..; done

configure:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make configure && cd ..; done

compile:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make && cd ..; done

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make install && cd ..; done

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make uninstall && cd ..; done
	. $(OPENQRM_SERVER_CONF) && rm -rf $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/*
	. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm/plugins

clean:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make clean && cd ..; done

realclean:	clean
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make realclean && cd ..; done

.PHONY: all configure compile install uninstall clean realclean

