<?php
require 'PHPUnit/TestMore.php';
require 'vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
define('ROOT', dirname(__DIR__));
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array(
    ROOT . '/src', 
    ROOT . '/vendor/pear',
));
// $classLoader->useIncludePath(true);
$classLoader->register();
