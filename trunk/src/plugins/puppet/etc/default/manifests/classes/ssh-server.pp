
class ssh-server {
	package { ssh: ensure => installed }

	service { ssh:
		ensure    => running,
		subscribe => [Package[ssh]],
	}
}

