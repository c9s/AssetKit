<?php
use AssetKit\AssetConfig;
use AssetKit\TestCase;

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

        $this->assertEquals('/assets',$array['BaseUrl']);
        $this->assertEquals('tests/assets',$array['BaseDir']);
        $this->assertEquals('production',$array['Environment']);

        $yamlContent = file_get_contents($configFile);
        ok($yamlContent);

        if (extension_loaded('yaml')) {
            $array = yaml_parse($yamlContent);
            $this->assertEquals('/assets',$array['BaseUrl']);
            $this->assertEquals('tests/assets',$array['BaseDir']);
            $this->assertEquals('production',$array['Environment']);
        }

    }
}

