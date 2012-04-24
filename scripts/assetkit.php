#!/usr/bin/env php
<?php
/*
 * This file is part of the Onion package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

define( 'ROOT', dirname(__DIR__) );
require ROOT . '/vendor/autoload.php';
require ROOT . '/vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array( 
    ROOT . '/src', 
    ROOT . '/vendor/pear',
));
$classLoader->useIncludePath(true);
$classLoader->register();
$app = AssetKit\Console::getInstance();
$app->run( $argv );
