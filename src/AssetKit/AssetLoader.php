<?php
namespace AssetKit;

/**
 * @class
 *
 * Load Asset from manifest File
 */
class AssetLoader
{
    public $paths;


    /**
     * @var \AssetKit\Config
     */
    public $config;

    /**
     * @param AssetKit\Config $config
     * @param array $paths
     */
    function __construct( \AssetKit\Config $config,$paths = array())
    {
        $this->config = $config;
        $this->paths = $paths;
    }


    /**
     * @param string $name asset name
     *
     */
    function load($name)
    {
        if( $this->config && $assetData = $this->config->getAsset($name) ) {
            $m = new Asset( $assetData );
            $m->config = $this->config;
            $m->loader = $this;
            return $m;
        }
        else {
            foreach( $this->paths as $path ) {
                $manifestFile = $path . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'manifest.yml';
                if( file_exists($manifestFile) ) {
                    $m = new Asset( $manifestFile );
                    $m->config = $this->config;
                    $m->loader = $this;
                    return $m;
                }
            }
        }
    }
}

