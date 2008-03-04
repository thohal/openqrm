# initializes the openqrm db

create database OPENQRM_DB;
use OPENQRM_DB;


# resource table
create table resource_info(
	resource_id INT(5) NOT NULL PRIMARY KEY,
	resource_localboot INT(1),
	resource_kernel VARCHAR(50),
	resource_kernelid BIGINT(3),
	resource_image VARCHAR(50),
	resource_imageid INT(5),
	resource_openqrmserver VARCHAR(20),
	resource_basedir VARCHAR(100),
	resource_serverid INT(5),
	resource_ip VARCHAR(20),
	resource_subnet VARCHAR(20),
	resource_broadcast VARCHAR(20),
	resource_network VARCHAR(20),
	resource_mac VARCHAR(20),
	resource_uptime BIGINT(10),
	resource_cpunumber INT(2),
	resource_cpuspeed BIGINT(10),
	resource_cpumodel VARCHAR(40),
	resource_memtotal BIGINT(10),
	resource_memused BIGINT(10),
	resource_swaptotal BIGINT(10),
	resource_swapused BIGINT(10),
	resource_hostname VARCHAR(40),
	resource_load DOUBLE(3,2),
	resource_execdport INT(5),
	resource_senddelay INT(3),
	resource_capabilities VARCHAR(255),
	resource_state VARCHAR(20),
	resource_event VARCHAR(20)
);


# kernel table
create table kernel_info(
	kernel_id INT(5) NOT NULL PRIMARY KEY,
	kernel_name VARCHAR(50),
	kernel_version VARCHAR(50),
	kernel_capabilities VARCHAR(255)
);


# image table
create table image_info(
	image_id INT(5) NOT NULL PRIMARY KEY,
	image_name VARCHAR(50),
	image_version VARCHAR(30),
	# can be : ramdisk, nfs, local, iscsi
	image_type VARCHAR(20),
	# can be : ram, /dev/hdX, /dev/sdX, nfs, iscsi
	image_rootdevice VARCHAR(20),
	# can be : ext2/3, nfs
	image_rootfstype VARCHAR(10),
	# freetext parameter for the deployment plugin
	image_deployment_parameter VARCHAR(255),
	image_isshared INT(1),
	image_comment VARCHAR(255),
	image_capabilities VARCHAR(255)
);


# appliance table
create table appliance_info(
	appliance_id INT(5) NOT NULL PRIMARY KEY,
	appliance_name VARCHAR(50),
	appliance_kernelid BIGINT(3),
	appliance_imageid INT(5),
	appliance_starttime BIGINT(10),
	appliance_stoptime BIGINT(10),
	appliance_cpunumber INT(2),
	appliance_cpuspeed BIGINT(10),
	appliance_cpumodel VARCHAR(40),
	appliance_memtotal BIGINT(10),
	appliance_swaptotal BIGINT(10),
	appliance_capabilities VARCHAR(100),
	appliance_cluster INT(5),
	appliance_ssi INT(5),
	appliance_resources INT(5),
	appliance_highavailable INT(5),
	appliance_virtual INT(5),
	appliance_virtualization_method VARCHAR(20),
	appliance_virtualization_host INT(5),
	appliance_state VARCHAR(20),
	appliance_comment VARCHAR(100),
	appliance_event VARCHAR(20)
);




# event table
create table event_info(
	event_id INT(5) NOT NULL PRIMARY KEY,
	event_name VARCHAR(50),
	event_time VARCHAR(50),
	event_priority INT(4),
	event_source VARCHAR(50),
	event_description VARCHAR(100),
	event_comment VARCHAR(100),
	event_capabilities VARCHAR(255),
	event_status INT(4),
	event_image_id INT(5),
	event_resource_id INT(5)
);


create table user_info(
	user_id INT(5) NOT NULL PRIMARY KEY,
	user_name VARCHAR(20),
	user_password VARCHAR(20),
	user_gender VARCHAR(1),
	user_first_name VARCHAR(50),
	user_last_name VARCHAR(50),
	user_department VARCHAR(50),
	user_office VARCHAR(50),
	user_role INT(5),
	user_last_update_time VARCHAR(50),
	user_description VARCHAR(255),
	user_capabilities VARCHAR(255),
	user_state VARCHAR(20)
);

create table role_info(
	role_id INT(5) NOT NULL PRIMARY KEY,
	role_name VARCHAR(20)
);


create table storage_info(
	storage_id INT(5) NOT NULL PRIMARY KEY,
	storage_name VARCHAR(20),
	storage_resource_id INT(5),
	storage_deployment_type INT(5),
	storage_comment VARCHAR(100),
	storage_capabilities VARCHAR(255),
	storage_state VARCHAR(20)
);


create table resource_service (
	resource_id INT(5) NOT NULL PRIMARY KEY,
	service VARCHAR(50) NOT NULL,
	INDEX(service)
);

create table image_service (
	image_id INT(5) NOT NULL PRIMARY KEY,
	service VARCHAR(50) NOT NULL,
	INDEX(service)
);


# plugg-able deployment types
create table deployment_info(
	deployment_id INT(5) NOT NULL PRIMARY KEY,
	deployment_name VARCHAR(50),
	deployment_type VARCHAR(20)
);



# initial data
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared) values ('1', 'idle', 'openqrm', 'ram', 'ram', 'ext2', '1');
insert into resource_info (resource_id, resource_localboot, resource_openqrmserver, resource_ip) values ('0', '1', 'OPENQRM_SERVER_IP_ADDRESS', 'OPENQRM_SERVER_IP_ADDRESS');
# base deployment type ram
insert into deployment_info (deployment_id, deployment_name, deployment_type ) values (1, 'Ramdisk Deployment', 'ram');
# user openqrm
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state) values (0, 'openqrm', 'openqrm', '-', '-', '-', '-', '-', 0, '-', 'default admin user', '', 'activated');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state) values (1, 'anonymous', 'openqrm', '-', '-', '-', '-', '-', 1, '-', 'default readonly user', '', 'activated');
insert into role_info (role_id, role_name) values (0, 'administrator');
insert into role_info (role_id, role_name) values (1, 'readonly');



