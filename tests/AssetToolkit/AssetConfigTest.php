<?php
use AssetToolkit\AssetConfig;

class AssetConfigTest extends AssetToolkit\TestCase
{

    public function testAssetConfigWithRoot() {
        $configFile = $this->getConfigArrayFile();
        $config = new AssetConfig($configFile, array(
            'root' => realpath('tests'),
        ));
        ok($config);
    }



    public function testCreateAssetConfig()
    {
        $configFile = $this->getConfigArrayFile();

        $config = new AssetConfig($configFile, array());
        ok($config);

        // test force reload
        $config->setBaseUrl('/assets');
        $config->setBaseDir('tests/assets');
        $config->addAssetDirectory('vendor/assets');
        $config->save(); // save the config
        $configContent = file_get_contents($configFile);
        ok($configContent);

        path_ok($configFile);
        $config->load(); // load it back
        unlink($configFile);
    }
}

