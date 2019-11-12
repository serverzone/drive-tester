# == Class role::drive_tester
#
#

class role::drive_tester {
  include profile::base
  include profile::php
  include profile::mdadm
  include profile::lvm
}
