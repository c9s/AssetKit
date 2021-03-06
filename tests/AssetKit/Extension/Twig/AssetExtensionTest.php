<?php
use AssetKit\Extension\Twig\AssetExtension;
use AssetKit\AssetConfig;

class AssetExtensionTest extends Twig_Test_IntegrationTestCase
{
    public $test;

    public function getExtensions()
    {
        $config = $this->test->getConfig();
        $loader = $this->test->getLoader();
        $extension = new AssetExtension($config, $loader);

        $config->setEnvironment(AssetConfig::PRODUCTION);

        $this->assertNotNull($loader->register('tests/assets/jquery/manifest.yml'));
        $this->assertNotNull($loader->register('tests/assets/jquery-ui/manifest.yml'));
        $this->assertNotNull($loader->register('tests/assets/simple-sass/manifest.yml'));
        $this->assertNotNull($loader->register('tests/assets/simple-coffee/manifest.yml'));
        $this->assertNotNull($loader->register('tests/assets/test/manifest.yml'));
        $this->assertNotNull($loader->register('tests/assets/json-js/manifest.yml'));

        return array(
            new Twig_Extension_Debug(),
            $extension,
        );
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

