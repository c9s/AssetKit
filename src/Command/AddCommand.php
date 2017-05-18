<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use AssetKit\Command\BaseCommand;
use CLIFramework\Command;
use Exception;

class AddCommand extends BaseCommand
{
    public function brief() { return 'add and initialize asset.'; }

    public function execute($manifestFile)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $asset = $loader->register($manifestFile);

        if (!$asset) {
            throw new Exception("Can not load asset from $manifestFile.");
        }

        $this->logger->info("Initializing resource...");

        $updater = new \AssetKit\ResourceUpdater();
        $updater->update($asset);

        $this->logger->info( "Installing {$asset->name}" );

        $installer = $this->getInstaller();
        $installer->install( $asset );

        $loader->saveEntries();
        $this->logger->info("Done");
    }
}


