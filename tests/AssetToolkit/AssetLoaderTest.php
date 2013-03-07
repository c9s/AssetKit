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
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $asset = $loader->loadFromPath($manifestPath);
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

        foreach( $loader->pairs() as $name => $a ) {
            ok( is_string($name) );
            ok( $a );
            ok($loader->has($name));
        }

        foreach( $loader->all() as $a ) {
            ok($loader->has($a->name));
        }
        ok($loader->get('jquery'));
        ok($loader->get('jquery-ui'));

        $config->save();
    }
}

