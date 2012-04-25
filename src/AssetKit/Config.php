<?php
namespace AssetKit;

class Config
{
    public $file;
    public $config = array();
    public $baseDir;

    public function __construct($file,$baseDir = null)
    {
        $this->file = $file;
        if( file_exists($file) ) {
            $this->config = json_decode(file_get_contents($file),true);
            $this->baseDir = $baseDir ?: dirname(realpath($file));
        }
        else {
            $this->baseDir = $baseDir ?: getcwd();
        }
    }

    public function getAssets()
    {
        $assets = array();
        if( isset($this->config['assets'] ) ) {
            foreach( $this->config['assets'] as $k => $v ) {
                $assets[] = $this->getAsset($k);
            }
        }
        return $assets;
    }


    /**
     * Register asset to config file
     *
     * @param string $name asset name
     * @param string $asset asset stash array, contains paths, resources .. etc
     */
    public function addAsset($name,$asset)
    {
        if( ! isset($this->config['assets']) )
            $this->config['assets'] = array();
        $this->config['assets'][$name] = $asset;
    }

    /**
     * Remove Asset from config file
     */
    public function removeAsset($asset)
    {
        unset($this->config['assets'][$asset]);
    }

    public function getAsset($name)
    {
        if( isset($this->config['assets'][$name] ) ) {
            $a = new Asset( $this->config['assets'][$name] );
            $a->config = $this;
            return $a;
        }
    }

    public function save()
    {
        if( ! defined('JSON_PRETTY_PRINT') )
            define('JSON_PRETTY_PRINT',0);
        file_put_contents($this->file, json_encode($this->config, 
                JSON_PRETTY_PRINT));
    }

    public function getAbsolutePublicRoot()
    {
        return $this->baseDir .DIRECTORY_SEPARATOR . (@$this->config['public'] ?: 'public');
    }

    public function getPublicAssetBaseUrl()
    {
        return '/assets';
    }

    public function getPublicAssetRoot()
    {
        return $this->getPublicRoot() . DIRECTORY_SEPARATOR . 'assets';
    }

    public function getPublicRoot($absolute = false)
    {
        return ( $absolute ? $this->baseDir . DIRECTORY_SEPARATOR : '' ) . (@$this->config['public'] ?: 'public');
    }

    public function getRoot()
    {
        return $this->baseDir;
    }

}

