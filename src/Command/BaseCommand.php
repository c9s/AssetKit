<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use AssetKit\CacheFactory;
use AssetKit\ResourceUpdater;
use CLIFramework\Command;
use Exception;

abstract class BaseCommand extends Command
{
    public $assetConfig;
    public $assetLoader;
    public $assetCache;

    public function options($opts)
    {
        $opts->add('c|config?', 'the asset config file, default to assetkit.yml');
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
        if ($this->assetConfig) {
            return $this->assetConfig;
        }

        $file = $this->getAssetConfigLink();
        if ( file_exists($file) ) {
            return $this->assetConfig = new AssetConfig($file);
        }
        return $this->assetConfig = new AssetConfig($this->getAssetConfigFile());
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
        return new \AssetKit\ResourceUpdater();
    }

    public function updateAsset(Asset $asset, $fetch = false)
    {
        $updater = $this->getAssetUpdater();
        $updater->update($asset, $fetch);
    }


    public function getInstaller()
    {
        if( $this->options->link ) {
            return new LinkInstaller($this->getAssetConfig());
        }
        return new Installer($this->getAssetConfig());
    }
}

