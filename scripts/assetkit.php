#!/usr/bin/env php
<?php
// require the FileUtil.php from phar
require "phar://assetkit.phar/FileUtil.php";
$app = AssetKit\Console::getInstance();
$app->runWithTry( $argv );
