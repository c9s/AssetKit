<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Asset;
use CLIFramework\Command;

class AddCommand extends Command
{
    function options($opts)
    {
    }

    function execute($manifestPath)
    {
        $config = new Config('.assetkit');

        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        $manifest = new Asset($manifestPath);
        $manifest->initResource();

#          $php = $manifest->compile();
#          $this->logger->info("compiled $php");

        $config->addAsset( $manifest->name , $manifest->export() );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


