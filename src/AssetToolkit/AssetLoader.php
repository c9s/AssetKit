<?php
namespace AssetToolkit;
use Exception;
use AssetToolkit\AssetConfig;
use AssetToolkit\Asset;

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
     * @var array asset object map, asset name => asset object
     */
    public $assets = array();


    /**
     * @var array asset object queue.
     */
    public $assetObjects = array();


    /**
     * @var \AssetToolkit\AssetConfig
     */
    public $config;



    /**
     *
     * @param AssetToolkit\AssetConfig $config
     */
    public function __construct(AssetConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Load asset from assetkit config stash
     *
     * @param string|array $name asset name or asset name array
     *
     * @return Asset
     */
    public function load($name)
    {
        if( $this->has($name) ) {
            return $this->get($name);
        }


        /**
         * 'manifest'
         * 'source_dir'
         * 'name'
         */
        if( $assetConfig = $this->config->getAssetConfig($name) ) {
            if( ! isset($assetConfig['manifest']) ) {
                throw new Exception("manifest file is not defined in $name");
            }

            $format = isset($assetConfig['format']) 
                          ? $assetConfig['format']
                          : 0;
            
            // load the asset manifest file
            $asset = new Asset($this->config);
            $asset->loadFromManifestFile( 
                $this->config->getRoot() . '/' . $assetConfig['manifest'], 
                $format);

            // save the asset object into the pool
            return $this->add($asset);
        } else {
            // some code to find asset automatically.
            // if there is not asset registered in config, we should look up from the asset paths
            if($asset = $this->lookup($name)) {
                return $this->add($asset);
            }
            throw new Exception("asset $name not found.");
        }
    }

    public function loadFromManifestFile($file)
    {
        $asset = $this->config->registerAssetFromManifestFile($file);
        return $this->assets[$name] = $asset;
    }

    public function loadFromPath($path)
    {
        $asset = $this->config->registerAssetFromPath($path);
        return $this->assets[$asset->name] = $asset;
    }


    /**
     * Load mutiple assets.
     *
     * @param string[] asset names
     * @return Asset[]
     */
    public function loadAssets($names) 
    {
        $self = $this;
        $assets = array();
        foreach( $names as $name ) {
            $assets[] = $this->load($name);
        }
        return $assets;
    }


    public function updateAsset($asset)
    {
        $manifestFile = FileUtil::find_non_php_manifest_file_from_directory( dirname($asset->manifestFile) );
        $phpManifestFile = Data::compile_manifest_to_php($manifestFile);
        $this->config->registerFromManifestFile($phpManifestFile);
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
        $registered = $this->config->getRegisteredAssets();
        foreach( $registered as $name => $subconfig ) {
            $assets[] = $this->config->registerAssetFromPath( dirname($subconfig['manifest']) );
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
        $registered = $this->config->getRegisteredAssets();
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
        foreach( $this->config->getAssetDirectories() as $dir ) {
            if($asset = $this->config->registerAssetFromPath( $root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $name )) {
                return $asset;
            }
        }
    }



    /**
     * Get asset object.
     *
     * @param string $name asset name.
     */
    public function get($name)
    {
        if(isset($this->assets[$name]) ) {
            return $this->assets[$name];
        }
    }


    /**
     * Remove all asset objects
     */
    public function clear()
    {
        $this->assets = array();
    }

    /**
     * @var string check if we've loaded this asset.
     * @return bool
     */
    public function has($name)
    {
        return isset($this->assets[$name]);
    }


    /**
     * @var Asset Add an asset to the asset queue and map.
     */
    public function add($asset)
    {
        // Add asset object safely.
        if( ! isset($this->assets[$asset->name]) ) {
            $this->assetObjects[] = $asset;
            return $this->assets[$asset->name] = $asset;
        }
    }

    /**
     * Returns all asset objects (keys and values)
     */
    public function pairs()
    {
        return $this->assets;
    }

    public function all()
    {
        return $this->assetObjects;
    }

}

