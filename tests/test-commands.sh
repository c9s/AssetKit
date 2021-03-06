#!/bin/bash
set -e
set -o pipefail
set -o errtrace
set -o errexit
php bin/assetkit init --baseDir "public/assets" \
    --baseUrl "/assets" \
    --dir "tests/assets" \
    assetkit.yml
cat .assetkit.yml
php bin/assetkit add tests/assets/jquery
php bin/assetkit list
php bin/assetkit remove jquery
php bin/assetkit add tests/assets/jquery
php bin/assetkit add tests/assets/underscore
php bin/assetkit add tests/assets/webtoolkit
php bin/assetkit add tests/assets/jquery-ui
php bin/assetkit target add main jquery
php bin/assetkit target list
php bin/assetkit compile jquery
php bin/assetkit compile --target all jquery underscore webtoolkit jquery-ui
php bin/assetkit clean
php bin/assetkit zsh --bind assetkit --program assetkit
