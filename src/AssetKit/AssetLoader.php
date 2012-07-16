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
    public $paths;

    public $assets = array();


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
     * @param string|array $name asset name or asset name array
     *
     * @return Asset|Asset[]
     */
    function load($name)
    {
        if( $this->config && is_array($name) )  {
            $self = $this;
            return array_map(function($n) use($self) {
                        return $self->load($n);
                    },$name);
        } 
        elseif( $this->config && $asset = $this->config->getAsset($name) ) {
            return $this->assets[ $name ] = $asset;
        }
        else {
            foreach( $this->paths as $path ) {
                $manifestFile = $path . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'manifest.yml';
                if( file_exists($manifestFile) ) {
                    $a = new Asset( $manifestFile );
                    $a->config = $this->config;
                    $this->assets[ $name ] = $a;
                    return $a;
                }
            }
        }
    }

    function clear()
    {
        $this->assets = array();
    }

    function getAssets()
    {
        return $this->assets;
    }
}

