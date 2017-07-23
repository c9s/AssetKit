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

class AddTargetCommand extends BaseCommand {

    public function brief()
    {
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
        $loader->entries->addTarget($targetName, $assetNames);
        $loader->saveEntries();
    }


}
