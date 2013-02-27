<?php

use AssetKit\ResourceUpdater;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;

class AssetLoaderTest extends AssetKit\TestCase
{

    public function manifestProvider() 
    {
        return array(
            array("tests/assets/jquery-ui"),
            array("tests/assets/jquery"),
        );
    }



    /**
     *
     * @dataProvider manifestProvider
     */
    public function testInit($manifestPath)
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $asset = $loader->registerFromManifestFileOrDir($manifestPath);
        ok($asset, "asset is loaded from $manifestPath");


        $updater = new ResourceUpdater();
        ok($updater,'resource updater is loaded');
        $updater->update($asset);

        $installer = new AssetKit\LinkInstaller;
        ob_start();
        $installer->install( $asset );
        $installer->uninstall( $asset );
        ob_clean();

        $installer = new AssetKit\Installer;
        ob_start();
        $installer->install( $asset );
        $installer->uninstall( $asset );
        ob_clean();

        $config->save();
    }
}

