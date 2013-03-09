<?php
define('ROOT', dirname(__DIR__));
require 'PHPUnit/TestMore.php';
require ROOT . '/vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';

if( extension_loaded('apc') ) {
    apc_clear_cache();
}
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array(
    ROOT . '/src', 
    ROOT . '/vendor/pear',
));
$classLoader->useIncludePath(true);
$classLoader->register();
