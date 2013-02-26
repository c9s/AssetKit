<?php


class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyAssetConfig()
    {
        $configFile = "tests/empty_config";
        if( file_exists($configFile) ) {
            unlink($configFile);
        }

        $config = new AssetKit\Config($configFile);
        ok($config);


        $assets = $config->getRegisteredAssets();
        ok( empty($asset) );

        $config->save();
    }
}

