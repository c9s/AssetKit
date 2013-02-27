<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\Asset;
use CLIFramework\Command;

class RemoveCommand extends Command
{

    public function brief()
    {
        return 'remove an asset.';
    }

    public function execute($assetName)
    {
        $config = new Config('.assetkit');

        $this->logger->info("Removing $assetName ...");
        $config->removeAsset( $assetName );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


