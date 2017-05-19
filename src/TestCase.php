<?php

namespace AssetKit;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    public $config;

    public $configFile;

    public $loader;

    public function getConfigFile()
    {
        if ($this->configFile) {
            return $this->configFile;
        }
        return $this->configFile = $this->createConfigFilename();
    }

    public function createConfigFilename()
    {
        $filename = str_replace('\\', '_', get_class($this)) . '_' . md5(microtime());
        return "tests/$filename.yml";
    }

    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }

        $configFile = $this->getConfigFile();
        $this->config = new \AssetKit\AssetConfig($configFile);
        $this->config->setBaseDir("tests/public");
        $this->config->setBaseUrl("/assets");
        $this->config->setNamespace("assetkit-testing");
        $this->config->setCacheDir("cache");
        $this->config->addAssetDirectory("tests/assets");
        $this->config->setRoot(getcwd());
        return $this->config;
    }

    public function getLoader()
    {
        if ($this->loader)
            return $this->loader;
        return $this->loader =  new \AssetKit\AssetLoader($this->getConfig());
    }

    public function getCompiler()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        return AssetCompilerFactory::create($config, $loader);
    }


    public function getInstaller()
    {
        static $installer;
        if($installer)
            return $installer;
        $installer = new \AssetKit\Installer($this->getConfig());
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
        $installer = new \AssetKit\LinkInstaller;
        $installer->enableLog = false;
        return $installer;
    }

    public function setUp()
    {
        if (extension_loaded('apc') ) {
            apc_clear_cache();
        }
        $config = $this->getConfig();

        // Clean up compiled directory
        $dir = $config->getCompiledDir();
        if (is_dir($dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $action($fileinfo->getRealPath());
            }
            rmdir($dir);
        }
    }


    public function tearDown()
    {
        $configFile = $this->getConfigFile();
        if (file_exists($configFile)) {
            // fwrite(STDERR,"Cleaning up config file $configFile...\n");
            unlink($configFile);
        }
        if (extension_loaded('apc') ) {
            apc_clear_cache();
        }
    }

}

