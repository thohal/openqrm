# this is the main openQRM Makefile
#.SILENT:

export OPENQRM_SERVER_CONF=$(shell pwd)/etc/openqrm-server.conf
export OPENQRM_SOURCE_DIR=$(shell pwd)

all: buildrequirements configure compile initrd
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && make all && cd ..; done

configure:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && make configure && cd ..; done

compile:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && make && cd ..; done

initrd:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_create_default_initrd_template

install:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && make install && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_pack_default_initrd_template

start: check
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make start' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm-server
	@. $(OPENQRM_SERVER_CONF) && ln -sf $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm-server /etc/init.d/openqrm-server
	@/etc/init.d/openqrm-server start

stop:
	@if [ -x /etc/init.d/openqrm-server ]; then /etc/init.d/openqrm-server stop; fi

uninstall: stop
	@. $(OPENQRM_SERVER_CONF) && if [ ! -d $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm ]; then echo "openQRM not installed"; exit 1; fi
	@if [ -x /etc/init.d/openqrm-server ]; then /etc/init.d/openqrm-server uninstall; fi
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && make uninstall && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm

clean:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && make clean && cd ..; done

realclean:	clean
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn"`; do cd $$SRC_DIR && make realclean && cd ..; done

package: all
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && DESTINATION_DIR=$$OPENQRM_PACKAGE_TMP_DIR/packageroot make install
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_package

reinstall: uninstall clean all install start

check:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_check_requirements

buildrequirements:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_check_build_requirements
	


.PHONY: all configure compile install start uninstall clean realclean reinstall package check initrd buildrequirements

