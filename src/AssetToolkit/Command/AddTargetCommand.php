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

class AddTargetCommand extends BaseCommand {

    public function brief() {
        return 'Add asset target'; 
    }

    /*
    public function arguments($args) {
        $args->add
    }
    */

    public function execute($target)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();

        $args = func_get_args();
        $targetName = array_shift($args);
        $assetNames = $args;
        foreach( $assetNames as $n ) {
            $a = $loader->load($n);
            if ( ! $a ) {
                throw new Exception("Asset $n not found, please add the asset manifest file.");
            }
        }
        $this->logger->info("Adding target '$targetName': " . join(", ", $assetNames) );
        $config->addTarget($targetName, $assetNames);
        $config->save();
    }


}
