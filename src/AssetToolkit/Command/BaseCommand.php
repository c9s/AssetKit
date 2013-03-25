<?php
namespace AssetToolkit\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\Asset;
use AssetToolkit\FileUtils;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;
use CLIFramework\Command;
use UniversalCache\ApcCache;
use UniversalCache\FileSystemCache;
use UniversalCache\UniversalCache;
use Exception;

class BaseCommand extends Command
{
    public $assetConfig;
    public $assetLoader;

    public function options($opts)
    {
        $opts->add('config?','the asset config file, defualt to .assetkit.php');
    }

    public function getAssetConfigFile()
    {
        return $this->options->config ?: ".assetkit.php";
    }

    public function getAssetConfig()
    {
        if ( $this->assetConfig )
            return $this->assetConfig;

        $cache = new UniversalCache;


        $configFile = $this->getAssetConfigFile();
        return $this->assetConfig = new AssetConfig($configFile,array( 
       
        ));
    }

    public function getAssetLoader()
    {
        if ( $this->assetLoader )
            return $this->assetLoader;
        return $this->assetLoader = new AssetLoader( $this->getAssetConfig() );
    }

    public function getAssetUpdater()
    {
        return new \AssetToolkit\ResourceUpdater();
    }

    public function updateAsset($asset, $fetch = false)
    {
        $updater = $this->getAssetUpdater();
        $updater->update($asset, $fetch);
    }


    public function getInstaller()
    {
        if( $this->options->link ) {
            return new LinkInstaller;
        }
        return new Installer;
    }
}

