# this is the openQRM vmware-esx-plugin Makefile

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/vmware-esx* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/templates/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/img
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/img/
	. $(OPENQRM_SERVER_CONF) && chmod 777 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin
	. $(OPENQRM_SERVER_CONF) && cp bin/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm*
	. $(OPENQRM_SERVER_CONF) && tar -C $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx -czvf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/boot-service-vmware-esx.tgz include/ bin/ etc/init.d/vmware-esx etc/templates/openqrm-vmware-esx-vm

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
