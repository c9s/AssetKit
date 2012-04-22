<?php
namespace AssetKit;

class AssetLoader
{
    public $paths;
    public $config;

    function __construct($config,$paths = array())
    {
        $this->config = $config;
        $this->paths = $paths;
    }



    function load($name)
    {
        if( $this->config && $path = $this->config->getAssetPath($name) ) {
            $manifestFile = $path . DIRECTORY_SEPARATOR . 'manifest.yml';
            $m = new Asset( $manifestFile );
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




