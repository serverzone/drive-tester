#/bin/bash

set -e

DIR=`readlink -e $0`
DIR=`dirname $DIR`

#
# Install required packages
#
apt-get -y install ruby-dev gcc make libgmp-dev libgmp3-dev puppet
gem install r10k

#
# Install puppet modules
#
cd $DIR/../.puppet
r10k puppetfile install

#
# Run puppet
#
puppet apply --modulepath=site:modules site.pp

#
# Install composer packages
#
cd $DIR
make vendor
