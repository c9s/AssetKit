<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use AssetKit\Command\BaseCommand;
use Exception;

class ListTargetCommand extends BaseCommand {

    public function brief() {
        return 'List asset targets'; 
    }

    public function execute()
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();

        $this->logger->info("Available targets:");
        if ( $targets = $loader->entries->getTargets() ) {
            foreach( $targets as $target => $assetNames ) {
                $this->logger->info("$target: " . join(', ', $assetNames) ,1);
            }
        }
    }


}
