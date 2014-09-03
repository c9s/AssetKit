<?php
namespace AssetToolkit\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\Asset;
use CLIFramework\Command;

class RemoveCommand extends BaseCommand
{

    public function brief()
    {
        return 'Remove an asset.';
    }

    public function execute($assetName)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();

        $this->logger->info("Removing $assetName ...");

        $loader->remove($assetName);
        $loader->saveEntries();
    }
}


