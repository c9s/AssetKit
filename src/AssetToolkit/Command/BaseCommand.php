<?php
namespace AssetToolkit\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\Asset;
use AssetToolkit\FileUtils;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;
use AssetToolkit\CacheFactory;
use AssetToolkit\ResourceUpdater;
use CLIFramework\Command;
use Exception;

class BaseCommand extends Command
{
    public $assetConfig;
    public $assetLoader;
    public $assetCache;

    public function options($opts)
    {
        $opts->add('config?','the asset config file, defualt to assetkit.yml');
    }

    public function getAssetConfigLink() {
        return ".assetkit.yml";
    }

    public function getAssetConfigFile()
    {
        return $this->options->config ?: "assetkit.yml";
    }

    public function getAssetConfig()
    {
        if ( $this->assetConfig ) {
            return $this->assetConfig;
        }

        $configFile = $this->getAssetConfigFile();
        $this->assetConfig = new AssetConfig($configFile);
        return $this->assetConfig;
    }

    public function getAssetCache() {
        if ($this->assetCache) {
            return $this->assetCache;
        }
        $config = $this->getAssetConfig();
        return $this->assetCache = CacheFactory::create($config);
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

