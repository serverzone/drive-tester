# == Class: profile::base
#
# Profile basic module
#

class profile::base {

    package { ['smartmontools', 'sdparm', 'parted', 'mdadm']:
            ensure => latest,
    }

}
