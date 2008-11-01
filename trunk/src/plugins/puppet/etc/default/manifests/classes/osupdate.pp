
class osupdate {
	case "$lsbdistid" {
		Debian: {
			exec { "/usr/bin/apt-get -y update":
				refreshonly => true,
			}
		}
		Ubuntu: {
			exec { "/usr/bin/apt-get -y update":
				refreshonly => true,
			}
		}





	}
}


