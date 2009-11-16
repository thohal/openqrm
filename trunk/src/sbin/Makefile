# this is the openQRM sbin Makefile
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

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-exec $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-cmd-queue $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-monitord $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-postencode $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && cp -a openqrm-crypt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm*

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/sbin

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
	