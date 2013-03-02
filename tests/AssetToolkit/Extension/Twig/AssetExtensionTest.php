<?php

class AssetExtensionTest extends Twig_Test_IntegrationTestCase
{
    public $test;

    public function getExtensions()
    {
        $extension = new AssetToolkit\Extension\Twig\AssetExtension();
        $extension->setAssetConfig($this->test->getConfig());
        $extension->setAssetLoader($this->test->getLoader());
        $loader = $this->test->getLoader();
        ok($loader->registerFromManifestFile('tests/assets/jquery/manifest.yml'));
        ok($loader->registerFromManifestFile('tests/assets/jquery-ui/manifest.yml'));
        ok($loader->registerFromManifestFile('tests/assets/test/manifest.yml'));
        ok($loader);
        return array( $extension );
    }

    public function setUp()
    {
        $this->test = new AssetObject;
        echo "*setup";
        $this->test->setUp();
    }

    public function tearDown()
    {
        echo "*teardown";
        $this->test->tearDown();
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__) . '/Fixtures/';
    }
}

class AssetObject extends AssetToolkit\TestCase {
    // dirty hack
    function test() {  }
}

