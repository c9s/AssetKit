<?php

class CssImportFilterTest extends AssetKit\TestCase
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
        $this->config = new AssetKit\AssetConfig($configFile);
    }

    public function tearDown()
    {
        $configFile = $this->getConfigFile();
        if(file_exists($configFile)) {
            unlink($configFile);
        }
    }

    public function test()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $jqueryui = $loader->registerFromManifestFileOrDir('tests/assets/jquery-ui');
        ok($jqueryui, 'jqueryui asset is loaded');

        $rewriteFilter = new AssetKit\Filter\CssRewriteFilter;
        $filter = new AssetKit\Filter\CssImportFilter;

        foreach( $jqueryui->getCollections() as $c ) {
            if( $c->isStylesheet ) {
                $rewriteFilter->filter( $c );

                // $filter->filter( $c );
                $content = $c->getContent();
                // echo $content;
            }
        }

        
    }
}

