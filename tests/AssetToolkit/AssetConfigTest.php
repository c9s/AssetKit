<?php

class AssetConfigTest extends AssetToolkit\TestCase
{


    public function testEmptyAssetConfig()
    {
        $configFile = $this->getConfigFile();
        $config = new AssetToolkit\AssetConfig($configFile,array(  
            'cache' => true,
            'cache_id' => 'custom_app_id',
            'cache_expiry' => 3600
        ));
        ok($config);

        // test force reload
        $config->setBaseUrl('/assets');
        $config->setBaseDir('tests/assets');
        $config->addAssetDirectory('vendor/assets');

        $assets = $config->getRegisteredAssets();
        ok( empty($asset) );

        $config->save();
    }
}

