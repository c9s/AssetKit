<?php
namespace AssetKit;
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
        $loader = new \AssetKit\AssetLoader($this->getConfig());
        ok($loader);
        return $loader;
    }

    public function getCompiler()
    {
        $compiler = new AssetCompiler($this->getConfig(),$this->getLoader() );
        $compiler->setEnvironment( AssetCompiler::PRODUCTION );
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();
        return $compiler;
    }


    public function getInstaller()
    {
        $installer = new \AssetKit\Installer;
        $installer->enableLog = false;
        return $installer;
    }

    public function getLinkInstaller()
    {
        $installer = new \AssetKit\LinkInstaller;
        $installer->enableLog = false;
        return $installer;
    }

    public function setUp()
    {
        $configFile = $this->getConfigFile();
        if(file_exists($configFile)) {
            unlink($configFile);
        }
        $this->config = new \AssetKit\AssetConfig($configFile);
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

