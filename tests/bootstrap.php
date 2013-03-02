<?php
require 'PHPUnit/TestMore.php';
require 'vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array(
    dirname(__DIR__) . '/src', 'vendor/pear',
));
$classLoader->useIncludePath(true);
$classLoader->register();
