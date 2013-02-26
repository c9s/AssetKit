<?php
namespace AssetKit;
use Exception;

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
    public function __construct( \AssetKit\Config $config)
    {
        $this->config = $config;
    }


    /**
     * @param string|array $name asset name or asset name array
     *
     * @return Asset|Asset[]
     */
    public function load($name)
    {
        $names = (array) $name;
        foreach( $names as $n ) {

            /**
             * 'manifest'
             * 'source_dir'
             * 'name'
             */
            $assetConfig = $this->config->getAssetConfig($n);

            // load the asset manifest file
            $a = new Asset( $assetConfig['manifest'] );

            // register asset into the pool
            $this->assets[$n] = $a;
        }
    }

    public function lookup($name)
    {
        $paths = $this->config->getAssetDirectories();
        foreach($paths as $path) {
            $target = $path . DIRECTORY_SEPARATOR . $name;
            $manifestYaml = $target . DIRECTORY_SEPARATOR . 'manifest.yml';
            $manifestPhp = $target . DIRECTORY_SEPARATOR . 'manifest.php';
            $manifestJson = $target . DIRECTORY_SEPARATOR . 'manifest.json';
            if(! is_dir($target))
                continue;

            $config = null;
            if( file_exists($manifestPhp) ) {
                $config = require $manifestPhp;
            } elseif ( file_exists($manifestJson) ) {
                $config = json_decode(file_get_contents($manifestJson),true);
            } elseif ( file_exists($manifestYaml) ) {
                $config = yaml_parse_file($manifestYaml);
            } 
            if($config) {
                // loaded
            }
        }
    }

    public function get($name)
    {
        if(isset($this->assets[$name]) ) {
            return $this->assets[$name];
        }
    }

    public function clear()
    {
        $this->assets = array();
    }

    public function all()
    {
        return $this->assets;
    }
}

