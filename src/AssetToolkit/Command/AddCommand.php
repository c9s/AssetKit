<?php
namespace AssetToolkit\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\Asset;
use AssetToolkit\FileUtils;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;
use AssetToolkit\Command\BaseCommand;
use CLIFramework\Command;
use Exception;

class AddCommand extends BaseCommand
{
    public function brief() { return 'add and initialize asset.'; }

    public function execute($manifestFile)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $asset = $config->registerAssetFromPath($manifestFile);

        if (!$asset) {
            throw new Exception("Can not load asset from $manifestFile.");
        }

        $this->logger->info("Initializing resource...");

        $updater = new \AssetToolkit\ResourceUpdater();
        $updater->update($asset);

        $this->logger->info( "Installing {$asset->name}" );

        $installer = $this->getInstaller();
        $installer->install( $asset );

        $config->addAsset( $asset );
        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


