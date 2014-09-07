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
        );
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



        /*
        $updater = new ResourceUpdater();
        ok($updater, "Resource updater is loaded");
        $updater->update($asset);

        $installer = new AssetKit\LinkInstaller($config);
        ob_start();
        $installer->install( $asset );
        $installer->uninstall( $asset );
        ob_clean();

        $installer = new AssetKit\Installer($config);
        ob_start();
        $installer->install( $asset );
        $installer->uninstall( $asset );
        ob_clean();
        */
        $config->save();
    }
}

