# /etc/puppet/manifests/classes/php.pp - various ways of installing php
# Copyright (C) 2007 David Schmitt <david@schmitt.edv-bus.at>
# See LICENSE for the full license granted to you.

class php {
	case $php_version {
		'4': { include "php::four" }
		'5': { include "php::five" }
	}
}

define php::pear($version = '') {
	case $version {
		'4': { include "php::four" }
		'5': { include "php::five" }
		# let the user choose which php to install
		'': {}
	}
	include "php::pear::common"

	package { "php${version}-${name}": ensure => installed }
}

class php::pear::common {
	package { ["php-pear", "php5-common" ]: ensure => installed }
}

class php::four {

	package { [ "php4", "php4-cli", "libapache2-mod-php4", "phpunit" ]: ensure => installed }

	php::pear { [
		'auth-pam', 'curl', 'domxml', 'gd', 'idn', 'imap', 'json',
		'lasso', 'ldap', 'librdf', 'mapscript', 'mcrypt', 'mhash', 'ming',
		'mysql', 'odbc', 'pgsql', 'pspell', 'recode', 'sqlite', 'sqlite3',
		'syck', 'uuid', 'xapian', 'xslt' ]:
			version => 4;
	}

	include "php::common"
}

class php::five {

	package { [ "php5", "php5-cli", "libapache2-mod-php5", "phpunit2" ]: ensure => installed }

	php::pear { [
		'auth-pam', 'curl', 'idn', 'imap', 'json', 'ldap', 'mcrypt', 'mhash',
		'ming', 'mysql', 'odbc', 'pgsql', 'ps', 'pspell', 'recode', 'snmp',
		'sqlite', 'sqlite3', 'sqlrelay', 'tidy', 'uuid', 'xapian', 'xmlrpc',
		'xsl' ]:
			version => 5
	}

	include "php::common"
}



class php::common {
	php::pear {
		[ auth, benchmark, cache, cache-lite, date, db, file, fpdf, gettext,
		html-template-it, http, http-request, log, mail, net-checkip,
		net-dime, net-ftp, net-imap, net-ldap, net-sieve, net-smartirc, net-smtp,
		net-socket, net-url, pager, radius, simpletest, services-weather, soap,
		xajax, xml-parser, xml-serializer, xml-util ]:
	}

	# some special cases
	package { 
		'php-mail-mime': ensure => installed;
		'php-sqlite3': ensure => installed;
	}
}


