#!/bin/bash
mkdir -p exts
cd exts

if [[ ${TRAVIS_PHP_VERSION:0:1} == "7" ]] ; then
    APCU_VERSION=5.1.8
else
    APCU_VERSION=4.0.11
fi
PACKAGE=apcu-$APCU_VERSION

if [[ ! -e $PACKAGE ]] ; then
    wget http://pecl.php.net/get/$PACKAGE.tgz
    tar zxvf $PACKAGE.tgz
fi
(cd $PACKAGE && phpize && ./configure && make install && echo "Installed ext/$PACKAGE")
