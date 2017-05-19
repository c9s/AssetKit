#!/bin/bash
set -e
if [[ ${TRAVIS_PHP_VERSION:0:1} == "7" ]] ; then
    PACKAGE=yaml-2.0.0
else
    PACKAGE=yaml-1.3.0
fi
wget -c http://pecl.php.net/get/$PACKAGE.tgz
tar -xzf $PACKAGE.tgz
(cd $PACKAGE && phpize && ./configure && make && sudo make install)
echo "extension=yaml.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
