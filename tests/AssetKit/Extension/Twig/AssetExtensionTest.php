<?php

class AssetExtensionTest extends Twig_Test_IntegrationTestCase
{
    public $test;

    public function getExtensions()
    {
        $extension = new AssetKit\Extension\Twig\AssetExtension();

        $config = $this->test->getConfig();
        $loader = $this->test->getLoader();

        $extension->setAssetConfig($config);
        $extension->setAssetLoader($loader);
        $config->setEnvironment( AssetKit\AssetConfig::PRODUCTION );

        ok($loader->register('tests/assets/jquery/manifest.yml'));
        ok($loader->register('tests/assets/jquery-ui/manifest.yml'));
        ok($loader->register('tests/assets/test/manifest.yml'));
        ok($loader);
        return array( $extension );
    }

    public function setUp()
    {
        $this->test = new AssetObject;
        $this->test->setUp();
    }

    public function tearDown()
    {
        $this->test->tearDown();
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__) . '/Fixtures/';
    }
}

class AssetObject extends AssetKit\TestCase {
    // dirty hack
    public function testAssetTwigExtension() {  }
}

