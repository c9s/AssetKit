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

    public function options($opts)
    {
        $opts->add('l|link','link asset files, instead of copy install.');
        $opts->add('config?','config file');
    }

    public function execute($assetName)
    {
        $options = $this->options;
        $configFile = $this->options->config ?: ".assetkit.php";
        $config = new AssetConfig($configFile);

        $this->logger->info("Removing $assetName ...");
        $config->removeAsset( $assetName );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


