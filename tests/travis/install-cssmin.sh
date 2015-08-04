#!/bin/bash
set -e
wget -O cssmin-1.0.tar.gz https://github.com/c9s/cssmin/archive/v1.0.tar.gz
tar xvf cssmin-1.0.tar.gz
cd cssmin-1.0/
phpize
./configure
make
sudo make install
echo "extension=cssmin.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
