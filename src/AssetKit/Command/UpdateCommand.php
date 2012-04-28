<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use CLIFramework\Command;
use Exception;

class UpdateCommand extends Command
{
    function brief() { return 'update and install assets'; }

    function execute()
    {
        $config = new Config('.assetkit');
        $installer = new Installer;
        foreach( $config->getAssets() as $name => $asset ) {
            $this->logger->info("Updating $name ...");
            $asset->initResource(true); // update it

            $this->logger->info( "Installing {$asset->name}" );
            $installer->install( $asset );

            $export = $asset->export();
            $config->addAsset( $asset->name , $export );
            $config->save();
        }
        $this->logger->info("Done");
    }
}


