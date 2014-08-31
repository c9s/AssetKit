<?php
namespace AssetToolkit;
use Exception;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetEntryCluster;
use AssetToolkit\Asset;
use ConfigKit\ConfigCompiler;

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
     * @var \AssetToolkit\AssetConfig
     */
    public $config;

    public $objects = array();

    public $entries;

    /**
     *
     * @param AssetToolkit\AssetConfig $config
     */
    public function __construct(AssetConfig $config)
    {
        $this->config = $config;

        if ($cache = $config->getCache()) {
            if ($entries = $cache->get('asset_entries')) {
                $this->entries = $entries;
            } else {
                $this->entries = new AssetEntryCluster;
            }
        } else {
            $this->entries = new AssetEntryCluster;
        }
    }



    /**
     * Load asset from assetkit config stash
     *
     * @param string|array $name asset name or asset name array
     *
     * @return Asset
     */
    public function load($name) {
        if (isset($this->objects[$name])) {
            return $this->objects[$name];
        }


        $config = $this->entries->get($name);
        if (!$config) {
            throw new Exception("Asset $name is not defined.");
        }

        if( ! isset($config['manifest']) ) {
            throw new Exception("manifest path is not defined in $name");
        }

        // load the asset manifest file
        $asset = new Asset($this->config);

        // load the asset config from manifest.php file.
        $asset->loadFromManifestFile($this->config->getRoot() . DIRECTORY_SEPARATOR . $config['manifest']);

        // Save the asset object into the pool
        return $this->objects[$name] = $asset;
    }


    /**
     * Load mutiple assets.
     *
     * @param string[] asset names
     * @return Asset[]
     */
    public function loadAssets($names)
    {
        $assets = array();
        foreach( $names as $name ) {
            $assets[] = $this->load($name);
        }
        return $assets;
    }


    public function updateAsset($asset)
    {
        $manifestFile = dirname($asset->manifestFile) . DIRECTORY_SEPARATOR . 'manifest.yml';
        $compiledFile = ConfigCompiler::compile($manifestFile);
        $this->register($compiledFile);
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
        $registered = $this->config->all();
        foreach( $registered as $name => $subconfig ) {
            $assets[] = $this->register( dirname($subconfig['manifest']) );
        }
        return $assets;
    }



    /**
     * Load all registered assets.
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


    public function lookup($name)
    {
        // some code to find asset automatically.
        // if there is not asset registered in config, we should look up from the asset paths
        $root = $this->config->getRoot();
        foreach ($this->config->getAssetDirectories() as $dir ) {
            if ($asset = $this->register( $root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'manifest.yml' )) {
                return $asset;
            }
        }
    }


    /**
     * Load asset from a manifest file or a directory that contains a manifest.yml file.
     *
     * @param string $path
     * @parma integer $format
     */
    public function register($path)
    {
        if (is_dir($path) ) {
            $path = $path . DIRECTORY_SEPARATOR . 'manifest.yml';
        }
        if (! file_exists($path)) {
            throw new Exception("Manifest file not found: $path.");
        }

        $compiledFile = ConfigCompiler::compile($path);
        $asset = new Asset($this->config);
        $asset->loadFromManifestFile($compiledFile);
        $this->entries->add($asset);
        return $asset;
    }

    public function __call($method, $args) {
        if (method_exists($this->entries, $method)) {
            return call_user_func_array(array($this->entries, $method), $args);
        }
        throw new Exception("Method $method is not defined.");
    }

    public function save() {
        if ($cache = $this->config->getCache()) {
            $cache->set('asset_entries', $this->entries);
        }
    }
}

