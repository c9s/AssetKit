<?php
namespace AssetToolkit;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{

    public $config;
    public $configFile;

    public function getConfigFile()
    {
        if($this->configFile)
            return $this->configFile;
        $filename = str_replace('\\', '_', get_class($this)) . '_' . md5(microtime());
        return $this->configFile = "tests/$filename.php";
    }

    public function getConfig()
    {
        ok($this->config, 'asset config object');
        return $this->config;
    }

    public function getLoader()
    {
        $loader = new \AssetToolkit\AssetLoader($this->getConfig());
        ok($loader);
        return $loader;
    }

    public function getCompiler()
    {
        $compiler = new AssetCompiler($this->getConfig(),$this->getLoader() );
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();
        return $compiler;
    }


    public function getInstaller()
    {
        static $installer;
        if($installer)
            return $installer;
        $installer = new \AssetToolkit\Installer;
        $installer->enableLog = false;
        return $installer;
    }

    public function installAssets($assets)
    {
        $assets = (array) $assets;
        $installer = $this->getInstaller();
        foreach($assets as $asset) {
            $installer->install($asset);
        }
    }


    public function uninstallAssets($assets)
    {
        $assets = (array) $assets;
        $installer = $this->getInstaller();
        foreach($assets as $asset) {
            $installer->uninstall($asset);
        }
    }


    public function getLinkInstaller()
    {
        $installer = new \AssetToolkit\LinkInstaller;
        $installer->enableLog = false;
        return $installer;
    }

    public function setUp()
    {
        $configFile = $this->getConfigFile();
        if(file_exists($configFile)) {
            unlink($configFile);
        }
        $this->config = new \AssetToolkit\AssetConfig($configFile);
        $this->config->setBaseDir("tests/public");
        $this->config->setBaseUrl("/assets");
        $this->config->setRoot(getcwd());
    }

    public function tearDown()
    {
        $configFile = $this->getConfigFile();
        if(file_exists($configFile)) {
            unlink($configFile);
        }
    }

}

