<?php
namespace AssetToolkit\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\Asset;
use AssetToolkit\FileUtils;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;
use CLIFramework\Command;
use Exception;

class UpdateCommand extends BaseCommand
{
    public function brief() { return 'update and install assets'; }

    public function options($opts)
    {
        parent::options($opts);
        $opts->add('l|link','link asset files, instead of copy install.');
    }

    public function execute()
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();

        $installer = $this->getInstaller();
        $installer->logger = $this->logger;

        $assets = $loader->updateAssetManifests();
        foreach( $assets as $asset ) {
            $this->logger->info("Updating {$asset->name} ...");

            $updater = new \AssetToolkit\ResourceUpdater;
            $updater->update($asset, true);

            $this->logger->info( "Installing {$asset->name}" );
            $installer->install( $asset );
        }
        $this->logger->info("Done");
    }
}


