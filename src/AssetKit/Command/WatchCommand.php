<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\Asset;
use CLIFramework\Command;

class WatchCommand extends BaseCommand
{
    public function brief()
    {
        return 'Watch an asset.';
    }

    public function execute($assetName)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $assetNames = func_get_args();
        $assets = array();
        foreach($assetNames as $assetName) {
            $assets[] = $loader->load($assetName);
        }
    }
}


