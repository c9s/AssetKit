#!/bin/bash
if [[ ${TRAVIS_PHP_VERSION:0:1} == "7" ]] ; then
    APCU_VERSION=5.1.8
else
    APCU_VERSION=4.0.11
fi
PACKAGE=apcu-$APCU_VERSION

# compile manually, because `pecl install apcu-beta` keep asking questions
wget http://pecl.php.net/get/$PACKAGE.tgz
tar zxvf $PACKAGE.tgz
(cd $PACKAGE && phpize && ./configure && make install && echo "Installed ext/$PACKAGE")
