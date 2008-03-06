# this is the main openQRM Makefile
#.SILENT:

export OPENQRM_SERVER_CONF=$(shell pwd)/etc/openqrm-server.conf
export OPENQRM_SOURCE_DIR=$(shell pwd)

all: buildrequirements configure compile initrd
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make all && cd ..; done

configure:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make configure && cd ..; done

compile:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make && cd ..; done

initrd:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_create_default_initrd_template
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_get_kernel_default_kernel

bootimage: initrd
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_create_default_kernel

install:
	@chmod +x make-assistant
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make install' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_SERVER_BASE_DIR/openqrm
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make install && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_pack_default_initrd_template
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_create_default_kernel

start: check
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make start' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm-server
	@. $(OPENQRM_SERVER_CONF) && ln -sf $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm-server /etc/init.d/openqrm-server
	@/etc/init.d/openqrm-server start

stop:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make start' as root" && exit 1)
	@if [ -x /etc/init.d/openqrm-server ]; then /etc/init.d/openqrm-server stop; fi

uninstall: stop
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && if [ ! -d $$OPENQRM_SERVER_BASE_DIR/openqrm ]; then echo "openQRM not installed"; exit 1; fi
	@if [ -x /etc/init.d/openqrm-server ]; then /etc/init.d/openqrm-server uninstall; fi
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make uninstall && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && rmdir $$OPENQRM_SERVER_BASE_DIR/openqrm

clean:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make clean && cd ..; done

realclean:	clean
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name "CVS"`; do cd $$SRC_DIR && make realclean && cd ..; done

package: uninstall clean all install
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_package

reinstall: uninstall clean all install start

check:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_check_requirements

buildrequirements:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_check_build_requirements
	


.PHONY: all configure compile install start uninstall clean realclean reinstall package check initrd bootimage buildrequirements

