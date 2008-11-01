
class apache-server {

	package { "apache2": ensure => installed }

	exec { "reload-apache2":
		command => "/etc/init.d/apache2 reload",
		refreshonly => true,
	}

	exec { "force-reload-apache2":
		command => "/etc/init.d/apache2 force-reload",
		refreshonly => true,
	}

	# We want to make sure that Apache2 is running.
	service { "apache2":
		ensure => running,
		hasstatus => true,
		hasrestart => true,
		require => Package["apache2"],
	}
}

