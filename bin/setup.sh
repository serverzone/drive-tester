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
# Install composer
#
if [ ! -f /usr/bin/composer ]; then
    wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --install-dir="/usr/bin" --filename="composer"
fi

#
# Install composer packages
#
cd $DIR/../
make vendor

#
# Create default local configuration
#
if [ ! -f src/config.local.neon ]; then
    cp src/config.local.neon.template src/config.local.neon
fi

#
# Clear nette cache
#
rm -rf temp/cache
