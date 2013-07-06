#!/usr/bin/env php
<?php
// require the FileUtil.php from phar
require "phar://assetkit.phar/FileUtil.php";
$app = AssetToolkit\Console::getInstance();
$app->run( $argv );
