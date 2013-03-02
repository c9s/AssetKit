<?php
require 'PHPUnit/TestMore.php';
require 'vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array(
    'src', 'vendor/pear',
));
$classLoader->useIncludePath(false);
$classLoader->register();

class Test 
{
    static function getConfig()
    {
        return new AssetToolkit\AssetConfig('.assetkit');
    }

    static function getLoader($config)
    {
        return new AssetToolkit\AssetLoader($config,array(
            'assets', 'tests/assets'
        ));
    }
}
