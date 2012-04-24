#!/bin/bash
onion -d compile \
    --lib src \
    --lib vendor/pear \
    --lib vendor/symfony/process \
    --classloader \
    --bootstrap scripts/assetkit.embed.php \
    --executable \
    --output assetkit.phar
mv assetkit.phar assetkit
chmod +x assetkit
