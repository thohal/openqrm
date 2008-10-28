# a basic server class for some defaults
class server {
    file { "/tmp/testfile":
    	ensure => "present",
        owner => "root",
        group => "root",
        mode  => 440,
    }
}
