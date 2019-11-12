# == Class: profile::php
#
# Install and configure php
#

class profile::php (
) inherits ::profile::base {

    include '::apt'

    # add sury.org repository
    apt::key { 'php::repo::sury.org':
        id     => '15058500A0235D97F5D10063B188E2B695BD4743',
        source => 'https://packages.sury.org/php/apt.gpg',
    }

    -> apt::source { 'php_sury.org':
        location => 'https://packages.sury.org/php/',
        repos    => 'main',
        include  => {
            'src' => false,
            'deb' => true,
        },
        require  => [
            Apt::Key['php::repo::sury.org'],
        ],
    }

    -> class { '::php::globals':
        php_version => '7.2',
    }

    -> class { '::php':
        ensure       => latest,
        manage_repos => false,
        pear         => false,
        composer     => false,
        fpm          => false,
        dev          => false,
        extensions   => {
            bcmath    => {},
            gd        => {},
            mbstring  => {},
            simplexml => {
                package_name => 'php7.2-xml',
            },
            sqlite3   => {},
        },
        settings     => {
            'Session/session.gc_maxlifetime' => 604800
        },
        require      => Class['apt::update'],
    }

}
