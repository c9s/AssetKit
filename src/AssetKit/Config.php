<?php
namespace AssetKit;

class Config
{
    public $file;
    public $config = array();
    public $baseDir;
    public $apcSupport = false;

    public function __construct($file,$options = null)
    {
        $this->file = $file;
        if( file_exists($file) ) {
            $this->baseDir = isset($options['base_dir']) 
                ? $options['base_dir'] 
                : dirname(realpath($file));

            if( isset($options['cache']) 
                && $this->apcSupport = extension_loaded('apc') 
                && $d = apc_fetch($this->baseDir) )
            {
                $this->config = $d;
            } else {
                $this->config = json_decode(file_get_contents($file),true);

                // cache this if we have apc
                if( $this->apcSupport ) {
                    apc_store($this->baseDir, $this->config, isset($options['cache_expiry']) ? $options['cache_expiry'] : 0 );
                }
            }
        }
        else {
            $this->baseDir = isset($options['base_dir']) 
                ? $options['base_dir'] 
                : getcwd();
        }
    }

    public function getAssets()
    {
        $assets = array();
        if( isset($this->config['assets'] ) ) {
            foreach( $this->config['assets'] as $k => $v ) {
                $assets[$k] = $this->getAsset($k);
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


    /**
     * Return public dir + '/assets'
     *
     * @param bool $absolute
     * @return string path
     */
    public function getPublicAssetRoot($absolute = false)
    {
        return $this->getPublicRoot($absolute) . DIRECTORY_SEPARATOR . 'assets';
    }

    /**
     * Get public root path
     *
     * Relative path is for Command-line
     * Absolute path is for Web
     *
     * @param $absolute bool
     *
     * @return string Path
     */
    public function getPublicRoot($absolute = false)
    {
        return ( $absolute ? $this->baseDir . DIRECTORY_SEPARATOR : '' ) . (@$this->config['public'] ?: 'public');
    }


    public function getRoot()
    {
        return $this->baseDir;
    }

}

