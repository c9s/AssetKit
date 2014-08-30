<?php

use AssetToolkit\ResourceUpdater;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\Asset;

class AssetLoaderTest extends AssetToolkit\TestCase
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
        $config = $this->getConfigArray();
        $loader = $this->getLoader();

        $asset = $loader->register($manifestPath);
        ok($asset, "asset is loaded from $manifestPath");

        $updater = new ResourceUpdater();
        ok($updater,'resource updater is loaded');
        $updater->update($asset);

        $installer = new AssetToolkit\LinkInstaller;
        ob_start();
        $installer->install( $asset );
        $installer->uninstall( $asset );
        ob_clean();

        $installer = new AssetToolkit\Installer;
        ob_start();
        $installer->install( $asset );
        $installer->uninstall( $asset );
        ob_clean();

        $config->save();
    }
}

