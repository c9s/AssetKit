<?php
namespace AssetKit;
use AssetKit\Asset;
use AssetKit\AssetConfig;
use AssetKit\AssetEntryStorage;
use ConfigKit\ConfigCompiler;
use Exception;
use BadMethodCallException;

class ManifestFileNotFoundException extends Exception {}

/**
 * @class
 *
 * Load Asset from manifest File.
 *
 *
 *
 * Operations:
 *
 *
 * 1. Register asset: compile and register asset manifest to the asset config.
 * 2. Load asset: load the registered asset from the config hash, simply load the compiled php asset config.
 * 3. Update asset: get registered assets and re-compile their manifest file from the config.
 * 4. Remove asset: remove the registered assets by asset name.
 *
 */
class AssetLoader
{
    /**
     * @var AssetConfig
     */
    public $config;


    /**
     * @var Asset[string name]
     *
     * Used for storing loaded asset objects
     */
    public $objects = array();

    /**
     * @var AssetEntryStorage 
     *
     * Used for saving registered asset configs (PHP arrays)
     */
    public $entries;

    /**
     *
     * @param AssetKit\AssetConfig $config
     */
    public function __construct(AssetConfig $config)
    {
        $this->config = $config;

        // TODO: support entry file stat check
        if ($cache = $config->getCache()) {
            if ($entries = $cache->get('asset_entries')) {
                $this->entries = $entries;
                return;
            }
        }

        // Fallback to entry file automatically
        $entryFile = $config->getEntryFile();
        if (file_exists($entryFile)) {
            $this->entries = require $config->getEntryFile();
            return;
        }

        $this->entries = new AssetEntryStorage;
    }





    /**
     * Load mutiple assets.
     *
     * @param string[] asset names
     * @return Asset[]
     */
    public function loadAssets(array $names)
    {
        $assets = array();
        foreach( $names as $name ) {
            if ($asset = $this->load($name)) {
                $assets[] = $asset;
            }
        }
        return $assets;
    }


    public function updateAsset(Asset $asset)
    {
        $this->register($asset->manifestFile, $asset->name);
    }

    public function updateAssetByName($name)
    {
        $asset = $this->load($name);
        return $this->updateAsset($asset);
    }

    /**
     * This method is for updating all manifest files that
     * is registed in asset config.
     */
    public function updateAssetManifests()
    {
        $assets = array();
        $registered = $this->entries->all();
        foreach( $registered as $name => $subconfig ) {
            $assets[] = $this->register($subconfig['manifest'], $name);
        }
        return $assets;
    }



    /**
     * Load all registered assets from the entry storage
     *
     * @return Asset[]
     */
    public function loadAll()
    {
        $assets = array();
        $registered = $this->entries->pairs();
        foreach( $registered as $name => $subconfig ) {
            $assets[] = $this->load($name);
        }
        return $assets;
    }

    /**
     * Load asset by name
     *
     * This method looks up the asset in the entry storage by the asset name.
     *
     * If the asset is not found, then it will fallback to lookup method (check 
     * each asset directory) and use register method to register the found asset. (if any)
     *
     * @param string $name asset name
     *
     * @return Asset
     */
    public function load($name) {
        if (isset($this->objects[$name])) {
            return $this->objects[$name];
        }

        // Get the asset config from entries cluster
        if ($config = $this->entries->get($name)) {
            if (! isset($config['manifest'])) {
                throw new Exception("manifest path is not defined in $name");
            }

            // load the asset manifest file
            $asset = $this->register($config['manifest'], $name);
            // Save the asset object into the pool
            $this->objects[$name] = $asset;
            $this->loadDepends($asset);
            return $asset;
        }

        // fallback to lookup
        if ($asset = $this->lookup($name)) {
            $this->objects[$name] = $asset;
            $this->loadDepends($asset);
            return $asset;
        } else {
            throw new Exception("Asset $name not found. auto lookup failed.");
        }
    }

    public function loadDepends(Asset $asset)
    {
        $deps = $asset->getDepends();
        if (!empty($deps)) {
            foreach($deps as $dep) {
                $depAsset = $this->load($dep);
                $this->objects[$dep] = $depAsset;
            }
        }
    }


    /**
     * Look up an asset by its name and register the asset into the entry storage.
     *
     * @param string $name
     */
    public function lookup($name)
    {
        // some code to find asset automatically.
        // if there is not asset registered in config, we should look up from the asset paths
        $root = $this->config->getRoot();
        foreach ($this->config->getAssetDirectories() as $dir ) {
            if ($asset = $this->register($root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'manifest.yml', $name)) {
                return $asset;
            }
        }
    }


    /**
     * Load asset from a manifest file or a directory that contains a manifest.yml file.
     *
     * @param string $path absolute path
     * @parma integer $format
     */
    public function register($path, $name = NULL, $force = false)
    {
        if ($p = realpath($path)) {
            $path = $p;
        }

        if (is_dir($path)) {
            $path = $path . DIRECTORY_SEPARATOR . 'manifest.yml';
        }

        if (!file_exists($path)) {
            throw new ManifestFileNotFoundException("Manifest file not found: $path");
        }

        if (!$name) {
            $name = basename(dirname($path));
        }

        $asset = Asset::createFromManifestFile($path, $name, $force);
        $this->entries->add($asset);
        return $asset;
    }

    public function __call($method, $args) {
        if (method_exists($this->entries, $method)) {
            return call_user_func_array(array($this->entries, $method), $args);
        }
        throw new BadMethodCallException("Method $method is not defined.");
    }

    /**
     * Save cache
     */
    public function saveEntryCache() {
        if ($cache = $this->config->getCache()) {
            $cache->set('asset_entries', $this->entries);
        }
    }

    public function loadEntryCache() {
        if ($cache = $this->config->getCache()) {
            if ($entries =$cache->get('asset_entries')) {
                $this->entries = $entries;
            }
        }
        if (!$this->entries) {
            $this->entries = new AssetEntryStorage;
        }
        return false;
    }

    public function saveEntries() {
        return ConfigCompiler::write($this->config->getEntryFile(), $this->entries);
    }


    public function getEntries() {
        return $this->entries;
    }

}

