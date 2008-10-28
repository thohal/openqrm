# /etc/puppet/manifests/site.pp

import "classes/*.pp"

node default {
    include server
}
