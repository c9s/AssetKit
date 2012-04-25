#!/bin/bash
# phpunit tests
php scripts/assetkit.php init --public public/assets
php scripts/assetkit.php add assets/blueprint/manifest.yml
php scripts/assetkit.php compile --as blueprint blueprint
