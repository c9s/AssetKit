<?php
namespace AssetToolkit;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{

    public $config;
    public $configFile;
    public $loader;

    public function getConfigArrayFile()
    {
        if ($this->configFile) {
            return $this->configFile;
        }
        return $this->configFile = $this->createConfigFile();
    }

    public function createConfigFile()
    {
        $filename = str_replace('\\', '_', get_class($this)) . '_' . md5(microtime());
        return "tests/$filename.yml";
    }

    public function getConfigArray()
    {
        if($this->config)
            return $this->config;

        $configFile = $this->getConfigArrayFile();
        if (file_exists($configFile)) {
            unlink($configFile);
        }
        $this->config = new \AssetToolkit\AssetConfig($configFile);
        $this->config->setBaseDir("tests/public");
        $this->config->setBaseUrl("/assets");
        $this->config->setNamespace("assetkit-testing");
        $this->config->setCacheDir("cache");
        $this->config->setRoot(getcwd());
        return $this->config;
    }

    public function getLoader()
    {
        if ($this->loader)
            return $this->loader;
        return $this->loader =  new \AssetToolkit\AssetLoader($this->getConfigArray());
    }

    public function getCompiler()
    {
        $compiler = new AssetCompiler($this->getConfigArray(),$this->getLoader());
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
        if (extension_loaded('apc') ) {
            apc_clear_cache();
        }
    }

    public function tearDown()
    {
        $configFile = $this->getConfigArrayFile();
        if (file_exists($configFile)) {
            unlink($configFile);
        }
        if (extension_loaded('apc') ) {
            apc_clear_cache();
        }
    }

}

