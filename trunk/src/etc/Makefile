# this is the openQRM etc Makefile
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

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_initrd_templates

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc
	. $(OPENQRM_SERVER_CONF) && cp openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp init.d/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/init.d/*   
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/httpd
	. $(OPENQRM_SERVER_CONF) && cp httpd/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/httpd/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp templates/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/templates/*   
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres
	. $(OPENQRM_SERVER_CONF) && cp db/db2/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2/
	. $(OPENQRM_SERVER_CONF) && cp db/db2/README $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/db2/
	. $(OPENQRM_SERVER_CONF) && cp db/mysql/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql/
	. $(OPENQRM_SERVER_CONF) && cp db/mysql/README $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/mysql/
	. $(OPENQRM_SERVER_CONF) && cp db/oracle/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle/
	. $(OPENQRM_SERVER_CONF) && cp db/oracle/README $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/oracle/
	. $(OPENQRM_SERVER_CONF) && cp db/postgres/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres/
	. $(OPENQRM_SERVER_CONF) && cp db/postgres/README $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/db/postgres/
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_initrd_templates

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/etc

clean:

realclean:	clean

all:	configure compile

.PHONY: all configure compile install uninstall clean realclean


