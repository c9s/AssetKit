<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Asset;
use AssetKit\FileUtils;
use CLIFramework\Command;

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

        $asset = new \AssetKit\Asset($manifestPath);
        $asset->initResource();

        $this->logger->info( "Installing {$asset->name}" );

        $asset->config = $config;

        $installer = new \AssetKit\Installer;
        $installer->install( $asset );

        $export = $asset->export();
        $config->addAsset( $asset->name , $export );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


