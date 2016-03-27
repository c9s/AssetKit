#!/bin/bash
set -e
echo -e "yes\nno\n" | pecl install apcu
echo "apc.enable_cli = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
php -m | grep apc
