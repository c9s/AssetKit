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

class RemoveTargetCommand extends BaseCommand {

    public function brief() {
        return 'Add asset target'; 
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
