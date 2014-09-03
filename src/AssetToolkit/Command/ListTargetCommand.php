<?php
namespace AssetToolkit\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\Asset;
use AssetToolkit\FileUtils;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;
use AssetToolkit\Command\BaseCommand;
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
        if ( $targets = $config->getTargets() ) {
            foreach( $targets as $target => $assetNames ) {
                $this->logger->info("$target: " . join(', ', $assetNames) ,1);
            }
        }
    }


}
