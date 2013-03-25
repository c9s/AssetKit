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

class TargetCommand extends BaseCommand
{
    public function brief() { return 'add, remove, list asset targets'; }

    public function options($opts)
    {
        parent::options($opts);
        $opts->add('remove:', 'remove target');
        $opts->add('add:', 'add target');
    }

    public function execute()
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();

        if ( $targetName = $this->options->add ) {
            $assetNames = func_get_args();
            foreach( $assetNames as $n ) {
                $a = $loader->load($n);
                if ( ! $a ) {
                    throw new Exception("Asset $n not found, please add the asset first.");
                }
            }
            $this->logger->info("Adding target $targetName: " . join(", ", $assetNames) );
            $config->addTarget($targetName, $assetNames);
            $config->save();
        } elseif ( $targetName = $this->options->remove ) {
            if ( $config->hasTarget($targetName) ) {
                $this->logger->info("Removing target $targetName");
                $config->removeTarget($targetName);
                $config->save();
            }
        } else {
            // list targets
            if ( $targets = $config->getTargets() ) {
                foreach( $targets as $target => $assetNames ) {
                    $this->logger->info("$target: " . join(', ', $assetNames) ,1);
                }
            }
        }
    }
}


