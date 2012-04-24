<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Asset;
use CLIFramework\Command;

class AddCommand extends Command
{
    function options($opts)
    {
        $opts->add('public:=s','public directory, your web server root.');
    }

    function brief() { return 'add and initialize asset.'; }

    function execute($manifestPath)
    {
        $config = new Config('.assetkit');

        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        $asset = new Asset($manifestPath);
        $asset->initResource();

        $config->addAsset( $asset->name , $asset->export() );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


