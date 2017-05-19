#!/bin/bash
set -e
mkdir -p exts
cd exts
if [[ ! -e cssmin ]] ; then
    if [[ ${TRAVIS_PHP_VERSION:0:1} == "7" ]] ; then
        wget -O cssmin-master.tar.gz https://github.com/c9s/cssmin/archive/master.tar.gz
        tar xvf cssmin-master.tar.gz && mv cssmin-master cssmin
    else
        wget -O cssmin-1.0.tar.gz https://github.com/c9s/cssmin/archive/v1.0.tar.gz
        tar xvf cssmin-1.0.tar.gz && mv cssmin-1.0 cssmin
    fi
fi
(cd cssmin && phpize && ./configure && make && make install)
echo "extension=cssmin.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
