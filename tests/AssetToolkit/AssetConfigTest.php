<?php
use AssetToolkit\AssetConfig;
use AssetToolkit\TestCase;

class AssetConfigTest extends TestCase
{

    public function testAssetConfigWithRootOption() {
        $configFile = $this->getConfigFile();
        $config = new AssetConfig($configFile, array(
            'root' => realpath('tests'),
        ));
        ok($config);
        $config->save();
        path_ok($configFile);
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
    }


    public function testCreateAssetConfig()
    {
        $configFile = $this->getConfigFile();
        $config = new AssetConfig($configFile, array());
        ok($config);

        $config->setBaseUrl('/assets');
        $config->setBaseDir('tests/assets');
        $config->setEnvironment('production');
        $config->addAssetDirectory('vendor/assets');

        $config->save(); // save the config
        ok($array = $config->getConfigArray());

        is('/assets',$array['BaseUrl']);
        is('tests/assets',$array['BaseDir']);
        is('production',$array['Environment']);

        $yamlContent = file_get_contents($configFile);
        ok($yamlContent);

        if (extension_loaded('yaml')) {
            $array = yaml_parse($yamlContent);
            is('/assets',$array['BaseUrl']);
            is('tests/assets',$array['BaseDir']);
            is('production',$array['Environment']);
        }

    }
}

