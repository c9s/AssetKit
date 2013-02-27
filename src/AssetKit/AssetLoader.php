<?php
namespace AssetKit;
use Exception;
use AssetKit\Config;

/**
 * @class
 *
 * Load Asset from manifest File
 */
class AssetLoader
{
    public $assets = array();


    /**
     * @var \AssetKit\Config
     */
    public $config;



    /**
     * @param AssetKit\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }


    /**
     * Load asset from assetkit config stash
     *
     * @param string|array $name asset name or asset name array
     *
     * @return Asset|Asset[]
     */
    public function load($names)
    {
        $names = (array) $names;
        foreach( $names as $n ) {
            /**
             * 'manifest'
             * 'source_dir'
             * 'name'
             */
            if( $assetConfig = $this->config->getAssetConfig($n) ) {
                // load the asset manifest file
                $a = new Asset( $assetConfig['manifest'] );

                // register asset into the pool
                $this->assets[$n] = $a;
            }
        }
    }


    /**
     * Load all registered assets from config.
     *
     * @return null
     */
    public function loadAll()
    {
        $assets = $this->config->getRegisteredAssets();
        $names = array_keys($assets);
        $this->load($names);
    }


    /**
     * Load asset from a manifest file.
     *
     * @param string $path
     * @parma integer $format
     */
    public function loadFromManifestFile($path, $format = 0)
    {
        if( $config ) {
            $asset = new Asset($config);
            $this->config->addAsset($asset);
        }
    }

    public function lookup($name)
    {
        $paths = $this->config->getAssetDirectories();
        foreach($paths as $path) {
            $target = $path . DIRECTORY_SEPARATOR . $name;
            if(! is_dir($target))
                continue;

            $manifestYaml = $target . DIRECTORY_SEPARATOR . 'manifest.yml';
            $manifestPhp = $target . DIRECTORY_SEPARATOR . 'manifest.php';
            $manifestJson = $target . DIRECTORY_SEPARATOR . 'manifest.json';
            $config = null;
            if( file_exists($manifestPhp) ) {
                $config = Data::decode($manifestPhp, Data::FORMAT_PHP);
            } elseif ( file_exists($manifestJson) ) {
                $config = Data::decode($manifestJson, Data::FORMAT_JSON);
            } elseif ( file_exists($manifestYaml) ) {
                $config = Data::decode($manifestYaml, Data::FORMAT_YAML);
            } 
            if($config) {
                return new Asset($config);
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

