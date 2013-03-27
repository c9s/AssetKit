<?php
namespace AssetToolkit\Command;
use AssetToolkit\Asset;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\FileUtils;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;
use CLIFramework\Command;
use Exception;

class ListCommand extends BaseCommand
{

    public function brief()
    {
        return 'list assets';
    }

    public function options($opts)
    {
        parent::options($opts);
    }

    public function execute()
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $loader->updateAssetManifests();

        $cwdLen =  strlen(getcwd()) + 1;
        foreach( $config->getRegisteredAssets() as $name => $stash ) {
            $asset = $loader->load($name);
            $this->logger->info( sprintf('%12s  | %2d collections | %s',$name, count($asset->collections)  ,  substr($asset->manifestFile, $cwdLen)   ),1);
        }
    }
}



