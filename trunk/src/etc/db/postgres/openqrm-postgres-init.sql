
drop table resource_info;
create table resource_info(
	resource_id int8 NOT NULL PRIMARY KEY,
	resource_localboot int8,
	resource_kernel char(50),
	resource_kernelid int8,
	resource_image char(50),
	resource_imageid int8,
	resource_openqrmserver char(20),
	resource_basedir char(100),
	resource_applianceid int8,
	resource_ip char(20),
	resource_subnet char(20),
	resource_broadcast char(20),
	resource_network char(20),
	resource_mac char(20),
	resource_uptime int8,
	resource_cpunumber int8,
	resource_cpuspeed int8,
	resource_cpumodel char(40),
	resource_memtotal int8,
	resource_memused int8,
	resource_swaptotal int8,
	resource_swapused int8,
	resource_hostname char(40),
	resource_load decimal(4,2),
	resource_execdport int8,
	resource_senddelay int8,
	resource_capabilities char(255),
	resource_lastgood char(10),
	resource_state char(20),
	resource_event char(20)
);


drop table kernel_info;
create table kernel_info(
	kernel_id int8  NOT NULL PRIMARY KEY,
	kernel_name char(50),
	kernel_version char(50),
	kernel_capabilities char(255)
);


drop table image_info;
create table image_info(
	image_id int8  NOT NULL PRIMARY KEY,
	image_name char(50),
	image_version char(30),
	image_type char(20),
	image_rootdevice char(20),
	image_rootfstype char(10),
	image_storageid int8,
	image_deployment_parameter char(255),
	image_isshared int8,
	image_comment char(255),
	image_capabilities char(255)
);


drop table appliance_info;
create table appliance_info(
	appliance_id int8 NOT NULL PRIMARY KEY,
	appliance_name char(50),
	appliance_kernelid int8,
	appliance_imageid int8,
	appliance_starttime int8,
	appliance_stoptime int8,
	appliance_cpunumber int8,
	appliance_cpuspeed int8,
	appliance_cpumodel char(40),
	appliance_memtotal int8,
	appliance_swaptotal int8,
	appliance_capabilities char(255),
	appliance_cluster int8,
	appliance_ssi int8,
	appliance_resources int8,
	appliance_highavailable int8,
	appliance_virtual int8,
	appliance_virtualization char(20),
	appliance_virtualization_host int8,
	appliance_state char(20),
	appliance_comment char(100),
	appliance_event char(20)
);

drop table event_info;
create table event_info(
	event_id int8 NOT NULL PRIMARY KEY,
	event_name char(50),
	event_time char(50),
	event_priority int8,
	event_source char(50),
	event_description char(100),
	event_comment char(100),
	event_capabilities char(255),
	event_status int8,
	event_image_id int8,
	event_resource_id int8
);



drop table user_info;
create table user_info(
	user_id int8 NOT NULL PRIMARY KEY,
	user_name char(20),
	user_password char(20),
	user_gender char(1),
	user_first_name char(50),
	user_last_name char(50),
	user_department char(50),
	user_office char(50),
	user_role int8,
	user_last_update_time char(50),
	user_description char(255),
	user_capabilities char(255),
	user_state char(20)
);


drop table role_info;
create table role_info(
	role_id int8 NOT NULL PRIMARY KEY,
	role_name char(20)
);


drop table storage_info;
create table storage_info(
	storage_id int8 NOT NULL PRIMARY KEY,
	storage_name char(20),
	storage_resource_id int8,
	storage_deployment_type int8,
	storage_comment char(255),
	storage_capabilities char(255),
	storage_state char(20)
);

drop table resource_service;
create table resource_service(
	resource_id int8 NOT NULL PRIMARY KEY,
	service char(50)
);

drop table image_service;
create table image_service(
	image_id int8 NOT NULL PRIMARY KEY,
	service char(50)
);

drop table deployment_info;
create table deployment_info(
	deployment_id int8 NOT NULL PRIMARY KEY,
	deployment_name char(50),
	deployment_type char(20)
);

drop table virtualization_info;
create table virtualization_info(
	virtualization_id int8 NOT NULL PRIMARY KEY,
	virtualization_name char(50),
	virtualization_type char(20),
	virtualization_mapping char(255)
);

drop table storagetype_info;
create table storagetype_info(
	storagetype_id int8 NOT NULL PRIMARY KEY,
	storagetype_description char(50),
	storagetype_name char(20),
	storagetype_mapping char(255)
);


insert into kernel_info (kernel_id, kernel_name, kernel_version) values (0, 'openqrm', 'openqrm');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_isshared) values (0, 'openqrm', 'openqrm', 'ram', 'ram', 0);

insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared) values ('1', 'idle', 'openQRM', 'ram', 'ram', 'ext2', '1');
insert into resource_info (resource_id, resource_localboot, resource_kernel, resource_image, resource_openqrmserver, resource_ip) values ('0', '1', 'local', 'local', 'OPENQRM_SERVER_IP_ADDRESS', 'OPENQRM_SERVER_IP_ADDRESS');
insert into deployment_info (deployment_id, deployment_name, deployment_type) values ('1', 'Ramdisk Deployment', 'ram');
insert into virtualization_info (virtualization_id, virtualization_name, virtualization_type) values ('1', 'Physical Systems', 'physical');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state) values (0, 'openqrm', 'openqrm', '-', '-', '-', '-', '-', 0, '-', 'default admin user', '', 'activated');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state) values (1, 'anonymous', 'openqrm', '-', '-', '-', '-', '-', 1, '-', 'default readonly user', '', 'activated');
insert into role_info (role_id, role_name) values (0, 'administrator');
insert into role_info (role_id, role_name) values (1, 'readonly');

insert into appliance_info (appliance_id, appliance_name, appliance_kernelid, appliance_imageid, appliance_starttime, appliance_stoptime, appliance_cluster, appliance_ssi, appliance_resources, appliance_highavailable, appliance_virtual, appliance_state, appliance_comment) values (1, 'openqrm', 0, 0, 10000, 0, 0, 0, 0, 0, 0, 'active', 'openQRM-Server');

grant all on resource_info to :openqrmdbuser;
grant all on kernel_info to :openqrmdbuser;
grant all on image_info to :openqrmdbuser;
grant all on event_info to :openqrmdbuser;
grant all on user_info to :openqrmdbuser;
grant all on appliance_info to :openqrmdbuser;
grant all on role_info to :openqrmdbuser;
grant all on storage_info to :openqrmdbuser;
grant all on resource_service to :openqrmdbuser;
grant all on image_service to :openqrmdbuser;
grant all on deployment_info to :openqrmdbuser;
grant all on virtualization_info to :openqrmdbuser;
grant all on storagetype_info to :openqrmdbuser;
