<?php
namespace AssetToolkit\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\Asset;
use CLIFramework\Command;

class RemoveCommand extends BaseCommand
{

    public function brief()
    {
        return 'remove an asset.';
    }

    public function execute($assetName)
    {
        $config = $this->getAssetConfig();
        $this->logger->info("Removing $assetName ...");
        $config->removeAsset( $assetName );
        $config->save();
    }
}


