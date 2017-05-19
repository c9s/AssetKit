#!/bin/bash
set -e
if [[ $(phpenv version-name) == "7.1" ]] ; then
    wget -O cssmin-1.0.tar.gz https://github.com/c9s/cssmin/archive/master.tar.gz
    tar xvf cssmin-master.tar.gz
    cd cssmin-master/
else
    wget -O cssmin-1.0.tar.gz https://github.com/c9s/cssmin/archive/v1.0.tar.gz
    tar xvf cssmin-1.0.tar.gz
    cd cssmin-1.0/
fi

phpize && ./configure && make && sudo make install
echo "extension=cssmin.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
