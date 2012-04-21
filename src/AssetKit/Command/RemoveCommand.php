<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Manifest;
use CLIFramework\Command;

class RemoveCommand extends Command
{
    function execute($assetName)
    {
        $config = new Config('.assetkit');

        $this->logger->info("Removing $assetName ...");
        $config->removeAsset( $assetName );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


