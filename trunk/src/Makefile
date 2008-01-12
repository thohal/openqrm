# this is the main openQRM Makefile
#.SILENT:

export OPENQRM_SERVER_CONF=$(shell pwd)/etc/openqrm-server.conf

all:	configure compile
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d`; do cd $$SRC_DIR && make all && cd ..; done

configure:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d`; do cd $$SRC_DIR && make configure && cd ..; done

compile:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d`; do cd $$SRC_DIR && make && cd ..; done

install:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d`; do cd $$SRC_DIR && make install && cd ..; done

start: check
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make start' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm-server
	@. $(OPENQRM_SERVER_CONF) && ln -sf $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm-server /etc/init.d/openqrm-server
	@/etc/init.d/openqrm-server start

uninstall:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && if [ ! -d $$OPENQRM_SERVER_BASE_DIR/openqrm ]; then echo "openQRM not installed"; exit 1; fi
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d`; do cd $$SRC_DIR && make uninstall && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm

clean:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d`; do cd $$SRC_DIR && make clean && cd ..; done

realclean:	clean
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d`; do cd $$SRC_DIR && make realclean && cd ..; done

package: uninstall clean all install
	. $(OPENQRM_SERVER_CONF) && chmod +x include/openqrm-package-functions
	. $(OPENQRM_SERVER_CONF) && export OPENQRM_SERVER_BASE_DIR && include/openqrm-package-functions package

reinstall: uninstall clean all install start

check:
	. $(OPENQRM_SERVER_CONF) && chmod +x include/openqrm-package-functions
	. $(OPENQRM_SERVER_CONF) && export OPENQRM_SERVER_BASE_DIR && include/openqrm-package-functions check



.PHONY: all configure compile install start uninstall clean realclean reinstall package check

