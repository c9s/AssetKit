<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
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

            $updater = new \AssetKit\ResourceUpdater;
            $updater->update($asset, true);

            $this->logger->info( "Installing {$asset->name}" );
            $installer->install( $asset );
        }
        $this->logger->info("Done");
    }
}


