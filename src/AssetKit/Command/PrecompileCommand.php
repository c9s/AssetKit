<?php
namespace AssetKit\Command;

use AssetKit\Config;
use AssetKit\Asset;
use CLIFramework\Command;

class PrecompileCommand extends Command
{
    function options($opts)
    {

    }

    function brief() { return 'precompile asset files.'; }

    function execute()
    {
        $config = new Config('.assetkit');

        $this->logger->info('Precompiling...');

        /*
        $manifest = new Asset($manifestPath);
        $manifest->initResource();

        $config->addAsset( $manifest->name , $manifest->export() );

        $this->logger->info("Saving config...");
        $config->save();
         */

        $this->logger->info("Done");
    }
}




