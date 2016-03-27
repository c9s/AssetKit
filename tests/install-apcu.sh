#!/bin/bash

# this script is in a `bin/` folder

if [ "$TRAVIS_PHP_VERSION" == "5.3" ]
then
    exit 0
fi

# this is helpful to compile extension
sudo apt-get install autoconf

# install this version
APCU_VERSION=4.0.10

# 5.1.3 is for php7

# compile manually, because `pecl install apcu-beta` keep asking questions
wget http://pecl.php.net/get/apcu-$APCU_VERSION.tgz
tar zxvf apcu-$APCU_VERSION.tgz
cd "apcu-${APCU_VERSION}"
phpize && ./configure && make install && echo "Installed ext/apcu-${APCU_VERSION}"
