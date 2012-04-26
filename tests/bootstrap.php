<?php
require 'PHPUnit/TestMore.php';
require 'vendor/autoload.php';
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
        return new AssetKit\Config('.assetkit');
    }

    static function getLoader($config)
    {
        return new AssetKit\AssetLoader($config,array(
            'assets', 'tests/assets'
        ));
    }
}
