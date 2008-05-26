connect to OPENQRM_DB

drop table resource_info
create table resource_info(				\
	resource_id bigint not null,			\
	resource_localboot bigint,			\
	resource_kernel varchar(50),			\
	resource_kernelid bigint,			\
	resource_image varchar(50),			\
	resource_imageid bigint,			\
	resource_openqrmserver varchar(20),		\
	resource_basedir varchar(100),			\
	resource_applianceid bigint,			\
	resource_ip varchar(20),			\
	resource_subnet varchar(20),			\
	resource_broadcast varchar(20),			\
	resource_network varchar(20),			\
	resource_mac varchar(20),			\
	resource_uptime bigint,				\
	resource_cpunumber bigint,			\
	resource_cpuspeed bigint,			\
	resource_cpumodel varchar(40),			\
	resource_memtotal bigint,			\
	resource_memused bigint,			\
	resource_swaptotal bigint,			\
	resource_swapused bigint,			\
	resource_hostname varchar(40),			\
	resource_load decimal(4,2),			\
	resource_execdport bigint,			\
	resource_senddelay bigint,			\
	resource_capabilities varchar(255),		\
	resource_lastgood varchar(10),			\
	resource_state varchar(20),			\
	resource_event varchar(20),			\
	primary key(resource_id)			\
)


drop table kernel_info
create table kernel_info(				\
	kernel_id bigint not null,			\
	kernel_name varchar(50),			\
	kernel_version varchar(50),			\
	kernel_capabilities varchar(255),		\
	primary key(kernel_id)				\
)



drop table image_info
create table image_info(			\
	image_id bigint not null,		\
	image_name varchar(50),			\
	image_version varchar(30),		\
	image_type varchar(20),			\
	image_rootdevice varchar(20),		\
	image_rootfstype varchar(10),		\
	image_storageid bigint,			\
	image_deployment_parameter varchar(10),	\
	image_isshared bigint,			\
	image_comment varchar(255),		\
	image_capabilities varchar(255),	\
	primary key(image_id)			\
)


drop table appliance_info
create table appliance_info(				\
	appliance_id bigint not null,			\
	appliance_name varchar(50),			\
	appliance_kernelid bigint,			\
	appliance_imageid bigint,			\
	appliance_starttime bigint,			\
	appliance_stoptime bigint,			\
	appliance_cpunumber bigint,			\
	appliance_cpuspeed bigint,			\
	appliance_cpumodel varchar(40),			\
	appliance_memtotal bigint,			\
	appliance_swaptotal bigint,			\
	appliance_capabilities varchar(255),		\
	appliance_cluster bigint,			\
	appliance_ssi bigint,				\
	appliance_resources bigint,			\
	appliance_highavailable bigint,			\
	appliance_virtual bigint,			\
	appliance_virtualization varchar(20),		\
	appliance_virtualization_host bigint,		\
	appliance_state varchar(20),			\
	appliance_comment varchar(100),			\
	appliance_event varchar(20),			\
	primary key(appliance_id)			\
)


drop table event_info
create table event_info(			\
	event_id bigint not null,		\
	event_name varchar(50),			\
	event_time varchar(50),			\
	event_priority bigint,			\
	event_source varchar(50),		\
	event_description varchar(100),		\
	event_comment varchar(100),		\
	event_capabilities varchar(255),	\
	event_status bigint,			\
	event_image_id bigint,			\
	event_resource_id bigint,		\
	primary key(event_id)			\
)



drop table user_info
create table user_info(					\
	user_id bigint not null,			\
	user_name varchar(20),				\
	user_password varchar(20),			\
	user_gender varchar(1),				\
	user_first_name varchar(50),			\
	user_last_name varchar(50),			\
	user_department varchar(50),			\
	user_office varchar(50),			\
	user_role bigint,				\
	user_last_update_time varchar(50),		\
	user_description varchar(255),			\
	user_capabilities varchar(255),			\
	user_state varchar(20),				\
	primary key(user_id)				\
)

drop table role_info
create table role_info(					\
	role_id bigint not null,			\
	role_name varchar(20)				\
)

drop table storage_info
create table storage_info(				\
	storage_id bigint not null,			\
	storage_name varchar(20),			\
	storage_resource_id bigint,			\
	storage_deployment_type bigint,			\
	storage_comment varchar(100),			\
	storage_capabilities varchar(255),		\
	storage_state varchar(20),			\
	primary key(storage_id)				\
)


drop table resource_service
create table resource_service (				\
	resource_id bigint not null,			\
	service varchar(50),				\
	primary key(resource_id)			\
)

drop table image_service
create table image_service (				\
	image_id bigint not null,			\
	service varchar(50),				\
	primary key(image_id)				\
)

drop table deployment_info
create table deployment_info(				\
	deployment_id bigint not null,			\
	deployment_name varchar(50),			\
	deployment_type varchar(20)			\
)

drop table virtualization_info
create table virtualization_info(			\
	virtualization_id bigint not null,		\
	virtualization_name varchar(50),		\
	virtualization_type varchar(20),		\
	virtualization_mapping varchar(255)		\
)

drop table storagetype_info
create table storagetype_info(				\
	storagetype_id bigint not null,			\
	storagetype_description varchar(50),		\
	storagetype_name varchar(20),			\
	storagetype_mapping varchar(255)		\
)


insert into kernel_info (kernel_id, kernel_name, kernel_version) values (0, 'openqrm', 'openqrm');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_isshared) values (0, 'openqrm', 'openqrm', 'ram', 'ram', 0);

insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared) values (1, 'idle', 'openqrm', 'ram', 'ram', 'ext2', 1)
insert into resource_info (resource_id, resource_localboot, resource_kernel, resource_image, resource_openqrmserver, resource_ip) values (0, 1, 'local', 'local', 'OPENQRM_SERVER_IP_ADDRESS', 'OPENQRM_SERVER_IP_ADDRESS')
insert into deployment_info (deployment_id, deployment_name, deployment_type) values (1, 'Ramdisk Deployment', 'ram')
insert into virtualization_info (virtualization_id, virtualization_name, virtualization_type) values (1, 'Physical Systems', 'physical')
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state) values (0, 'openqrm', 'openqrm', '-', '-', '-', '-', '-', 0, '-', 'default admin user', '', 'activated')
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state) values (1, 'anonymous', 'openqrm', '-', '-', '-', '-', '-', 1, '-', 'default readonly user', '', 'activated')
insert into role_info (role_id, role_name) values (0, 'administrator')
insert into role_info (role_id, role_name) values (1, 'readonly')

insert into appliance_info (appliance_id, appliance_name, appliance_kernelid, appliance_imageid, appliance_starttime, appliance_stoptime, appliance_cluster, appliance_ssi, appliance_resources, appliance_highavailable, appliance_virtual, appliance_state, appliance_comment) values (1, 'openqrm', 0, 0, 10000, 0, 0, 0, 0, 0, 0, 'active', 'openQRM-Server');



