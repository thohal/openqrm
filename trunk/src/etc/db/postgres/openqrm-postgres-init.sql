
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
	resource_serverid int8,
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
	resource_state char(20),
	resource_event char(20)
);


drop table kernel_info;
create table kernel_info(
	kernel_id int8  NOT NULL PRIMARY KEY,,
	kernel_name char(50),
	kernel_version char(50),
	kernel_capabilities char(255)
);


drop table image_info;
create table image_info(
	image_id int8  NOT NULL PRIMARY KEY,,
	image_name char(50),
	image_version char(30),
	image_type char(20),
	image_rootdevice char(20),
	image_rootfstype char(10),
	image_isshared int8,
	image_comment char(255),
	image_capabilities char(255)
);


drop table event_info;
create table event_info(
	event_id int8 NOT NULL PRIMARY KEY,,
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
	user_id int8 NOT NULL PRIMARY KEY,,
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
	role_id int8 NOT NULL PRIMARY KEY,,
	role_name char(20)
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

insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared) values ('1', 'idle', 'openQRM', 'ram', 'ram', 'ext2', '1');
insert into resource_info (resource_id, resource_localboot, resource_openqrmserver) values ('0', '1', 'OPENQRM_SERVER_IP_ADDRESS');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared ) values ( '2', 'hda1', 'Linux', 'local', '/dev/hda1', 'ext3', '0');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared ) values ( '3', 'hda2', 'Linux', 'local', '/dev/hda2', 'ext3', '0');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared ) values ( '4', 'sda1', 'Linux', 'local', '/dev/sda1', 'ext3', '0');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared ) values ( '5', 'sda2', 'Linux', 'local', '/dev/sda2', 'ext3', '0');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state) values (0, 'openqrm', 'openqrm', '-', '-', '-', '-', '-', 0, '-', 'openQRM-Server default user', '', 'activated');


grant all on resource_info to :openqrmdbuser;
grant all on kernel_info to :openqrmdbuser;
grant all on image_info to :openqrmdbuser;
grant all on event_info to :openqrmdbuser;
grant all on user_info to :openqrmdbuser;


