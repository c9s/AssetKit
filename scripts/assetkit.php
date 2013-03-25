#!/usr/bin/env php
<?php
require "FileUtil.php"; // require the FileUtil.php from phar
$app = AssetToolkit\Console::getInstance();
$app->run( $argv );
