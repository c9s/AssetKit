<?php


class ConfigTest extends PHPUnit_Framework_TestCase
{



    public function testEmptyAssetConfig()
    {
        $configFile = "tests/empty_config";
        if( file_exists($configFile) ) {
            unlink($configFile);
        }

        $config = new AssetKit\Config($configFile,array(  
            'cache' => true,
            'cache_id' => 'custom_app_id',
        ));
        ok($config);


        $assets = $config->getRegisteredAssets();
        ok( empty($asset) );

        $config->save();
        unlink($configFile);
    }


    public function testAddingAssetConfig()
    {
        $configFile = "tests/.adding_config";
        if( file_exists($configFile) ) {
            unlink($configFile);
        }

        $config = new AssetKit\Config($configFile);
        ok($config);


        $assets = $config->getRegisteredAssets();
        ok( empty($asset) );

        $config->save();
        unlink($configFile);
    }

}

