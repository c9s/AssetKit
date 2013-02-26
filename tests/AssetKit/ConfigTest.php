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
            'cache_expiry' => 3600
        ));
        ok($config);

        // test force reload
        $config->load();

        $config->setBaseUrl('/assets');
        $config->setBaseDir('tests/assets');

        $assets = $config->getRegisteredAssets();
        ok( empty($asset) );


        $config->save();
        unlink($configFile);
    }
}

