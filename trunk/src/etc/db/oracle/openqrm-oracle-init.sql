
drop table resource_info;
create table resource_info(
	resource_id INTEGER NOT NULL PRIMARY KEY,
	resource_localboot INTEGER,
	resource_kernel VARCHAR2(50),
	resource_kernelid INTEGER,
	resource_image VARCHAR2(50),
	resource_imageid INTEGER,
	resource_openqrmserver VARCHAR2(20),
	resource_basedir VARCHAR2(100),
	resource_serverid INTEGER,
	resource_ip VARCHAR2(20),
	resource_subnet VARCHAR2(20),
	resource_broadcast VARCHAR2(20),
	resource_network VARCHAR2(20),
	resource_mac VARCHAR2(20),
	resource_uptime INTEGER,
	resource_cpunumber INTEGER,
	resource_cpuspeed INTEGER,
	resource_cpumodel VARCHAR2(40),
	resource_memtotal INTEGER,
	resource_memused INTEGER,
	resource_swaptotal INTEGER,
	resource_swapused INTEGER,
	resource_hostname VARCHAR2(40),
	resource_load FLOAT,
	resource_execdport INTEGER,
	resource_senddelay INTEGER,
	resource_capabilities VARCHAR2(255),
	resource_state VARCHAR2(20),
	resource_event VARCHAR2(20)
);



drop table kernel_info;
create table kernel_info(
	kernel_id INTEGER NOT NULL PRIMARY KEY,
	kernel_name VARCHAR2(50),
	kernel_version VARCHAR2(50),
	kernel_capabilities VARCHAR2(255)
);



drop table image_info;
create table image_info(
	image_id INTEGER NOT NULL PRIMARY KEY,
	image_name VARCHAR2(50),
	image_version VARCHAR2(30),
	image_type VARCHAR2(20),
	image_rootdevice VARCHAR2(20),
	image_rootfstype VARCHAR2(10),
	image_isshared INTEGER,
	image_comment VARCHAR2(255),
	image_capabilities VARCHAR2(255)
);

drop table appliance_info;
create table appliance_info(
	appliance_id INTEGER NOT NULL PRIMARY KEY,
	appliance_name VARCHAR2(50),
	appliance_kernelid INTEGER,
	appliance_imageid INTEGER,
	appliance_starttime INTEGER,
	appliance_stoptime INTEGER,
	appliance_cpunumber INTEGER,
	appliance_cpuspeed INTEGER,
	appliance_cpumodel VARCHAR2(40),
	appliance_memtotal INTEGER,
	appliance_swaptotal INTEGER,
	appliance_capabilities VARCHAR2(255),
	appliance_cluster INTEGER,
	appliance_ssi INTEGER,
	appliance_resources INTEGER,
	appliance_highavailable INTEGER,
	appliance_virtual INTEGER,
	appliance_virtualization_method VARCHAR2(20),
	appliance_virtualization_host INTEGER,
	appliance_state VARCHAR2(20),
	appliance_comment VARCHAR2(100),
	appliance_event VARCHAR2(20)
);

drop table event_info;
create table event_info(
	event_id INTEGER NOT NULL PRIMARY KEY,
	event_name VARCHAR2(50),
	event_time VARCHAR2(50),
	event_priority INTEGER,
	event_source VARCHAR2(50),
	event_description VARCHAR2(100),
	event_comment VARCHAR2(100),
	event_capabilities VARCHAR2(255),
	event_status INTEGER,
	event_image_id INTEGER,
	event_resource_id INTEGER
);



drop table user_info;
create table user_info(
	user_id INTEGER NOT NULL PRIMARY KEY,
	user_name VARCHAR2(20),
	user_password VARCHAR2(20),
	user_gender VARCHAR2(1),
	user_first_name VARCHAR2(50),
	user_last_name VARCHAR2(50),
	user_department VARCHAR2(50),
	user_office VARCHAR2(50),
	user_role INTEGER,
	user_last_update_time VARCHAR2(50),
	user_description VARCHAR2(255),
	user_capabilities VARCHAR2(255),
	user_state VARCHAR2(20)
);


drop table role_info;
create table role_info(
	role_id INTEGER NOT NULL PRIMARY KEY,
	role_name VARCHAR2(20)
);


drop table resource_service;
create table resource_service(
	resource_id INTEGER NOT NULL PRIMARY KEY,
	service VARCHAR(50)
);

drop table image_service;
create table image_service(
	image_id INTEGER NOT NULL PRIMARY KEY,
	service VARCHAR(50)
);

insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared) values ('1', 'idle', 'openqrm', 'ram', 'ram', 'ext2', '1');
insert into resource_info (resource_id, resource_localboot, resource_openqrmserver) values ('0', '1', 'OPENQRM_SERVER_IP_ADDRESS');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared ) values ( '2', 'hda1', 'Linux', 'local', '/dev/hda1', 'ext3', '0');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared ) values ( '3', 'hda2', 'Linux', 'local', '/dev/hda2', 'ext3', '0');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared ) values ( '4', 'sda1', 'Linux', 'local', '/dev/sda1', 'ext3', '0');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared ) values ( '5', 'sda2', 'Linux', 'local', '/dev/sda2', 'ext3', '0');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state) values (0, 'openqrm', 'openqrm', '-', '-', '-', '-', '-', 0, '-', 'openQRM-Server default user', '-', 'activated');
insert into role_info (role_id, role_name) values (0, 'administrator');


quit
