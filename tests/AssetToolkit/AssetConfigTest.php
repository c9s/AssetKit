<?php
use AssetToolkit\AssetConfig;

class AssetConfigTest extends AssetToolkit\TestCase
{

    public function testAssetConfigWithRootOption() {
        $configFile = $this->getConfigFile();
        $config = new AssetConfig($configFile, array(
            'root' => realpath('tests'),
        ));
        ok($config);
        $config->save();
        path_ok($configFile);
        unlink($configFile);
    }

    public function testCreateAssetConfigWithArray() {
        $configFile = $this->getConfigFile();
        $config = new AssetConfig(array(
            'Environment' => 'production',
        ), array(
            'root' => realpath('tests'),
        ));
        ok($config);
        $config->save($configFile);
        path_ok($configFile);
        unlink($configFile);
    }





    public function testCreateAssetConfig()
    {
        $configFile = $this->getConfigFile();
        $config = new AssetConfig($configFile, array());
        ok($config);

        // test force reload
        $config->setBaseUrl('/assets');
        $config->setBaseDir('tests/assets');
        $config->setEnvironment('production');
        $config->addAssetDirectory('vendor/assets');
        $config->save(); // save the config
        $configContent = file_get_contents($configFile);
        ok($configContent);

        path_ok($configFile);
        $config->load(); // load it back
        unlink($configFile);
    }
}

