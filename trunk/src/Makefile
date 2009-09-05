# this is the main openQRM Makefile
#.SILENT:
#
# This file is part of openQRM.
#
# openQRM is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2
# as published by the Free Software Foundation.
#
# openQRM is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
#
# Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
#

export OPENQRM_SERVER_CONF=$(shell pwd)/etc/openqrm-server.conf
export OPENQRM_SOURCE_DIR=$(shell pwd)

all: buildrequirements configure compile initrd
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make all -s && cd ..; done

configure:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make configure -s && cd ..; done

compile:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make -s && cd ..; done

initrd:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_create_default_initrd_template

install:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make install -s && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_pack_default_initrd_template
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.php | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.css | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.conf | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.png | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.gif | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.jpg | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *.spec | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *configuration.template | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *deb.control | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name openqrm-pxelinux | xargs -r chmod -x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-functions | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-hook | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-deployment | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name root-mount* | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-maintenance | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *-ifup | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && find $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm -name *commit.tmpl | xargs -r chmod +x
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *.conf | xargs -r chmod -x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *deb.control | xargs -r chmod -x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name openqrm-pxelinux | xargs -r chmod -x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *.spec | xargs -r chmod -x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *-functions | xargs -r chmod +x; fi
	@. $(OPENQRM_SERVER_CONF) && if [ -d $(DESTINATION_DIR)/etc/openqrm ]; then find $(DESTINATION_DIR)/etc/openqrm -name *-maintenance | xargs -r chmod +x; fi


start: check
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make start' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm
	@. $(OPENQRM_SERVER_CONF) && ln -sf $$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/openqrm /etc/init.d/openqrm
	@/etc/init.d/openqrm start

stop:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make stop' as root" && exit 1)
	@if [ -x /etc/init.d/openqrm ]; then /etc/init.d/openqrm stop; fi

uninstall: stop
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make uninstall' as root" && exit 1)
	@. $(OPENQRM_SERVER_CONF) && if [ ! -d $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm ]; then echo "openQRM not installed"; exit 1; fi
	@if [ -x /etc/init.d/openqrm ]; then /etc/init.d/openqrm uninstall; fi
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm"`; do cd $$SRC_DIR && make uninstall -s && cd ..; done
	@. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm

clean:
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make clean -s && cd ..; done

realclean:	clean
	@for SRC_DIR in `find -mindepth 1 -maxdepth 1 -type d -not -name ".svn" -not -name "debian" -not -name "rpm" -not -name "thirdparty"`; do cd $$SRC_DIR && make realclean -s && cd ..; done

rpm: all
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make rpm' as root" && exit 1)
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && DESTINATION_DIR=$$OPENQRM_PACKAGE_TMP_DIR/packageroot make install
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_package

reinstall: 
	sudo make uninstall -s
	make clean -s
	make all -s
	sudo make install -s
	sudo make start -s

debsource:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_create_deb_source

deb:
	@whoami | grep root 1>/dev/null 2>&1 || (echo "Please run 'make deb' as root" && exit 1)
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_pbuilder

check:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_check_requirements

buildrequirements:
	@chmod +x make-assistant
	@. $(OPENQRM_SERVER_CONF) && ./make-assistant openqrm_check_build_requirements
	if [ -d ./thirdparty ]; then mkdir -p ../buildtmp; cp -aR ./thirdparty/* ../buildtmp/; fi


.PHONY: all configure compile install start uninstall clean realclean reinstall rpm deb debsource check initrd buildrequirements

