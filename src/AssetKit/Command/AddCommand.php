<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use CLIFramework\Command;
use Exception;

class AddCommand extends Command
{
    function brief() { return 'add and initialize asset.'; }

    function execute($manifestPath)
    {
        $config = new Config('.assetkit');

        if( is_dir($manifestPath) ) {
            $manifestPath = $manifestPath  . DIRECTORY_SEPARATOR . 'manifest.yml';
        }

        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        $asset = new Asset($manifestPath);
        $asset->config = $config;
        $asset->initResource(true); // update it

        $this->logger->info( "Installing {$asset->name}" );

        $installer = new Installer;
        $installer->install( $asset );

        $export = $asset->export();
        $config->addAsset( $asset->name , $export );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


