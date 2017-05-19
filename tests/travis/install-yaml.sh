#!/bin/bash
set -e
mkdir -p exts
cd exts
if [[ ${TRAVIS_PHP_VERSION:0:1} == "7" ]] ; then
    PACKAGE=yaml-2.0.0
else
    PACKAGE=yaml-1.3.0
fi
if [[ ! -e $PACKAGE ]] ; then
    wget -c http://pecl.php.net/get/$PACKAGE.tgz
    tar xzf $PACKAGE.tgz
fi
(cd $PACKAGE && phpize && ./configure && make && make install)
echo "extension=yaml.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
