<?php
namespace AssetKit;
use Exception;
use AssetKit\AssetConfig;

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
    public $assets = array();


    /**
     * @var \AssetKit\AssetConfig
     */
    public $config;



    /**
     *
     * @param AssetKit\AssetConfig $config
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
            $asset->loadFromManifestFile( $assetConfig['manifest'] , $format);

            // save the asset object into the pool
            return $this->assets[$name] = $asset;
        } else {
            // some code to find asset automatically.
            // if there is not asset registered in config, we should look up from the asset paths
            if($asset = $this->lookup($name)) {
                return $this->assets[$name] = $asset;
            }
        }
    }




    /**
     * Load mutiple assets
     *
     * @return Asset[]
     */
    public function loadAssets($names) 
    {
        $self = $this;
        return array_map(function($name) use($self) {
            return $self->load($name);
        },$names);
    }


    /**
     * Load asset from a manifest file.
     *
     * @param string $path
     * @parma integer $format
     */
    public function registerFromManifestFile($path, $format = 0)
    {
        if( $format !== Data::FORMAT_PHP ) {
            $format = Data::FORMAT_PHP;
            $path = Data::compile_manifest_to_php($path);
        }

        $asset = new Asset($this->config);
        $asset->loadFromManifestFile($path, $format);
        $this->registerAsset($asset);
        return $asset;
    }


    public function registerAsset($asset)
    {
        $this->config->addAsset($asset);
    }


    /**
     * If the given path is a directory, then we should 
     * find the manifest from the directory.
     *
     * @param string $path
     * @param integer $format
     */
    public function registerFromManifestFileOrDir($path, $format = 0) 
    {
        if( is_dir($path) ) {
            $path = FileUtil::find_manifest_file_from_directory( $path );
            if( $format === 0 ) {
                $format = Data::detect_format_from_extension($path);
            }
        }
        if( file_exists($path))  {
            return $this->registerFromManifestFile($path, $format);
        }
    }



    public function updateAsset($asset)
    {
        $manifestFile = FileUtil::find_non_manifest_file_from_directory( dirname($asset->manifestFile) );
        return Data::compile_manifest_to_php($manifestFile);
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
            $asset = $this->load($name);
            $this->updateAsset($asset);
            $assets[] = $asset;
        }
        return $assets;
    }

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
            if($asset = $this->registerFromManifestFileOrDir( $root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $name )) {
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
     * Returns all asset objects (keys and values)
     */
    public function all()
    {
        return $this->assets;
    }
}

