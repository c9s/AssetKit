<?php
use AssetKit\ResourceUpdater;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\AssetUrlBuilder;
use AssetKit\Asset;
use AssetKit\Collection;

class AssetLoaderTest extends AssetKit\TestCase
{

    public function manifestProvider() 
    {
        return array(
            array("tests/assets/jquery-ui"),
            array("tests/assets/jquery"),
            array("tests/assets/underscore"),
            array("tests/assets/webtoolkit"),
            array("tests/assets/action-js"),
        );
    }


    public function testAssetFileTypeFiltering()
    {
        $loader = $this->getLoader();
        $asset = $loader->register("tests/assets/jquery-ui");
        ok($asset);

        $asset = $loader->load('jquery-ui:stylesheet');
        ok($asset instanceof Asset);
        $collections = $asset->getCollections();
        $this->assertNotEmpty($collections);
        ok( $collections[0]->isStylesheet );

        $asset = $loader->load('jquery-ui:javascript');
        ok($asset instanceof Asset);
        $collections = $asset->getCollections();
        $this->assertNotEmpty($collections);
        ok( $collections[0]->isScript );



        $asset = $loader->load('jquery-ui#darkness');
        ok($asset instanceof Asset);
        $collections = $asset->getCollections();
        $this->assertNotEmpty($collections);
        ok( $collections[0]->isStylesheet );
        is( 'darkness', $collections[0]->id );
    }

    public function testAssetLoad() {
        $manifestFiles = array(
            "tests/assets/jquery-ui",
            "tests/assets/jquery",
            "tests/assets/underscore",
            "tests/assets/webtoolkit",
            "tests/assets/action-js"
        );
        $loader = $this->getLoader();
        foreach($manifestFiles as $manifestFile) {
            $loader->register($manifestFile);
        }
        $assets[] = $loader->load('jquery');
        $assets[] = $loader->load('jquery-ui');
        $assets[] = $loader->load('underscore');
        $assets[] = $loader->load('webtoolkit');
        $assets[] = $loader->load('action-js');
        foreach($assets as $asset) {
            ok($asset);
            ok($asset instanceof Asset);
        }
    }

    /**
     * @dataProvider manifestProvider
     */
    public function testAssetLookup($manifestPath) {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $name = basename($manifestPath);
        $asset = $loader->lookup($name);
        ok($asset);
        ok($asset instanceof Asset);
    }


    /**
     * @dataProvider manifestProvider
     */
    public function testAssetRegister($manifestPath)
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $asset = $loader->register($manifestPath);
        ok($asset, "Asset is loaded from $manifestPath");

        $collections = $asset->getCollections();
        ok($collections);

        foreach( $collections as $collection ) {
            ok( $collection instanceof Collection, 'Got Collection object');
        }

        $urlBuilder = new AssetUrlBuilder($config);
        $assetBaseUrl = $urlBuilder->buildBaseUrl($asset);
        is( "/assets/" . $asset->name, $assetBaseUrl);
        $config->save();
    }
}

