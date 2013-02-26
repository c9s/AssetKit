<?php

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $config = new AssetKit\Config("tests/assetkit.config");
        ok($config);

        $config->getRegisteredAssets();


    }
}

