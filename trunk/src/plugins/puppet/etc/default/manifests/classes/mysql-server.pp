
class mysql-server {

	package { "mysql-server": ensure => installed }
	package { "mysql-common": ensure => installed }
	package { "mysql-client": ensure => installed }

}