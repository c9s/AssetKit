<?php
namespace AssetKit;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{

    public $config;

    public function getConfigFile()
    {
        $filename = str_replace('\\', '_', get_class($this));
        return "tests/$filename.php";
    }

    public function getConfig()
    {
        $config = $this->config;
        ok($config, 'asset config object');
        return $config;
    }

    public function getLoader()
    {
        return new \AssetKit\AssetLoader($this->getConfig());
    }

    public function setUp()
    {
        $configFile = $this->getConfigFile();
        if(file_exists($configFile)) {
            unlink($configFile);
        }
        $this->config = new \AssetKit\AssetConfig($configFile);
    }

    public function tearDown()
    {
        $configFile = $this->getConfigFile();
        if(file_exists($configFile)) {
            unlink($configFile);
        }
    }

}

