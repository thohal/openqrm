# this is the openQRM sanboot-storage-plugin Makefile
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

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:
	../../make-assistant openqrm_cache_or_download gpxe etc/build.conf/gpxe.conf
	export OPENQRM_PRE_MAKE="cd src" && export OPENQRM_POST_MAKE="cd .." && ../../make-assistant openqrm_compile_from_source gpxe etc/build.conf/gpxe.conf

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc/templates/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/sanboot-storage $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/
	. $(OPENQRM_SERVER_CONF) && cp web/image* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/img/
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/img/
	. $(OPENQRM_SERVER_CONF) && chmod 777 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/tpl/
	. $(OPENQRM_SERVER_CONF) && cp web/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/tpl/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin
	. $(OPENQRM_SERVER_CONF) && cp bin/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/openqrm*
	. $(OPENQRM_SERVER_CONF) && tar -C $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage -czvf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/web/boot-service-sanboot-storage.tgz include/ bin/ etc/init.d/sanboot-storage
	# menu icons
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/sanboot-storage
	. $(OPENQRM_SERVER_CONF) && cp web/img/plugin.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/sanboot-storage/
	. $(OPENQRM_SERVER_CONF) && cp web/img/volumes.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/sanboot-storage/
    # gpxe bootloader
	. $(OPENQRM_SERVER_CONF) && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/gpxe/gpxe-0.9.9/src/bin/undionly.kpxe $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/ 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/gpxe/gpxe-0.9.9/src/bin/undionly.kpxe $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/; fi


uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
