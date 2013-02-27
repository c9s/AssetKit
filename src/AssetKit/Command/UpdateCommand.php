<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use CLIFramework\Command;
use Exception;

class UpdateCommand extends Command
{
    function brief() { return 'update and install assets'; }

    function options($opts)
    {
        $opts->add('l|link','link asset files, instead of copy install.');
    }

    function execute()
    {
        $options = $this->options;
        $config = new Config('.assetkit');

        $installer = $options->link
                ? new LinkInstaller
                : new Installer;

        $installer->logger = $this->logger;

        foreach( $config->getAssets() as $name => $asset ) {
            $this->logger->info("Updating $name ...");

            $updater = new \AssetKit\ResourceUpdater;
            $updater->update($asset, true);

            $this->logger->info( "Installing {$asset->name}" );
            $installer->install( $asset );

            $export = $asset->export();
            $config->addAsset( $asset->name , $export );
            $config->save();
        }
        $this->logger->info("Done");
    }
}


