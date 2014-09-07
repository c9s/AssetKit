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

class RemoveTargetCommand extends BaseCommand {

    public function brief() {
        return 'Remove asset target'; 
    }

    /*
    public function arguments($args) {
        $args->add
    }
    */

    public function execute($targetName)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        if ( $config->hasTarget($targetName) ) {
            $this->logger->info("Removing target '$targetName'");
            $config->removeTarget($targetName);
            $config->save();
            $this->logger->info("Done");
        } else {
            $this->logger->warn("Target $targetName not found");
        }
    }


}
