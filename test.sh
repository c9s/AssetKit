#!/bin/bash
# phpunit tests
php scripts/assetkit.php init --public public
php scripts/assetkit.php add assets/blueprint/manifest.yml
php scripts/assetkit.php add assets/jquery/manifest.yml
php scripts/assetkit.php add assets/jquery-ui/manifest.yml
php scripts/assetkit.php compile --as blueprint blueprint
