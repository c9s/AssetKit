<?php

class AssetLoaderTest extends PHPUnit_Framework_TestCase
{

    public function manifestProvider() 
    {
        return array(
            array("tests/assets/jquery-ui")
            array("tests/assets/jquery")
        );
    }

    public function testInit($manifestPath)
    {
        $configFile = "tests/assetkit_init.php";

        if(file_exists($configFile) ) {
            unlink($configFile);
        }

        $config = new AssetConfig($configFile);
        ok( $config );
        ok( $config->fileLoaded );

        if( is_dir($manifestPath) ) {
            $manifestPath = $manifestPath  . DIRECTORY_SEPARATOR . 'manifest.yml';
        }

        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        $loader = new AssetLoader($config);
        ok($loader, "loader ok");

        $asset = $loader->loadFromManifestFile($manifestPath);
        ok($asset, "asset is loaded");


#          $updater = new \AssetKit\ResourceUpdater($this);
#          $updater->update(true);

        /*
        if( $options->link ) {
            $installer = new LinkInstaller;
            $installer->install( $asset );
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

