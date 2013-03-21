<?php
define('ROOT', dirname(__DIR__));
require 'vendor/autoload.php';
require 'PHPUnit/TestMore.php';
require ROOT . '/vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';

if( extension_loaded('apc') ) {
    apc_clear_cache();
}
if (extension_loaded('xhprof') ) {
    ini_set('xhprof.output_dir','/tmp');
}

// from c9s/php-fileutil
if ( ! extension_loaded('fileutil') ) {
    require "FileUtil.php";
}

$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array(
    ROOT . '/src', 
    ROOT . '/vendor/pear',
));
$classLoader->useIncludePath(false);
$classLoader->register(true);
