<?php

use AssetKit\ResourceUpdater;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;

class AssetLoaderTest extends PHPUnit_Framework_TestCase
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
        $configFile = "tests/assetkit_init.php";

        if(file_exists($configFile) ) {
            unlink($configFile);
        }

        $config = new AssetConfig($configFile);
        $config->setBaseDir("tests/public");
        $config->setBaseUrl("/assets");

        ok( $config , 'config object' );
        ok( ! $config->fileLoaded , 'config file should not be loaded' );

        $loader = new AssetLoader($config);
        ok($loader, "loader ok");

        $asset = $loader->registerFromManifestFileOrDir($manifestPath);
        ok($asset, "asset is loaded");


        $updater = new ResourceUpdater();
        ok($updater,'resource updater is loaded');
        $updater->update($asset);

        $installer = new AssetKit\LinkInstaller;
        $installer->install( $asset );

        $installer->uninstall( $asset );

        /*
        } 
        else {
            $installer = new Installer;
            $installer->install( $asset );
        }

        $export = $asset->export();
        $config->addAsset( $asset->name , $export );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
        */
    }
}

