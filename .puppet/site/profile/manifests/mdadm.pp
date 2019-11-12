# == Class: profile::mdadm
#
# Install and configure mdadm
#

class profile::mdadm inherits ::profile::base {

    package { 'mdadm':
        ensure => latest,
    }

    augeas { 'mdadm_disable_auto_detect':
        context => '/files/etc/mdadm/mdadm.conf',
        incl    => '/etc/mdadm/mdadm.conf',
        lens    => 'Mdadm_conf.lns',
        changes => 'set auto/- all',
        require => Package['mdadm'],
    }

}
