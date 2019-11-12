# == Class: profile::lvm
#
# Install and configure logical volume manager
#

class profile::lvm inherits ::profile::base {

    package { 'lvm2':
        ensure => latest,
    }

    augeas { 'lvm_disable_auto_mount':
        context => '/files/etc/lvm/lvm.conf',
        incl    => '/etc/lvm/lvm.conf',
        lens    => 'lvm.lns',
        changes => [
            'rm devices/dict/filter/list',
            'set devices/dict/filter/list/1/str "r/.*/"',
        ],
        require => Package['lvm2'],
    }

}
