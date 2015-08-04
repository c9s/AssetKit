#!/bin/bash
set -e
wget -c http://pecl.php.net/get/yaml-1.1.1.tgz
tar -xzf yaml-1.1.1.tgz
cd yaml-1.1.1
phpize
./configure
make
sudo make install
echo "extension=yaml.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
