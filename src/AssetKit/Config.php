<?php
namespace AssetKit;

class Config
{

    /**
     * @var string $file the config file path
     */
    public $file;


    /**
     * @var array $config the config hash.
     */
    public $config;



    /**
     * @var string $baseDir the base directory for public files.
     */
    public $baseDir;


    /**
     * @var string $baseUrl The base url for front-end
     */
    public $baseUrl;

    public $cacheEnable = true;
    public $cacheSupport = false;

    public function __construct($file,$options = array())
    {
        $this->file = $file;

        if(isset($options['cache']) ) {
            $this->cacheEnable = $options['cache'];
        }

        $useCache = $this->cacheEnabled();
        if($useCache) {
            // get apc cache
            $cacheId = isset($options['cache_id'] 
                ? $options['cache_id']
                : __DIR__;
            $this->config = apc_fetch($cacheId);
        }

        if ( ! $this->config ) {
            // read the config file
            if( file_exists($file) ) {
                $this->config = json_decode(file_get_contents($file),true);
                if($useCache) {
                    apc_store($cacheId, 
                        $this->config, 
                        isset($options['cache_expiry']) 
                        ? $options['cache_expiry'] 
                        : 0 
                    );
                }
            } else {
                $this->config = array();
            }
        }
    }



    /**
     * Check if apc cache is supported and is cache enabled by user.
     *
     * @return bool 
     */
    public function cacheEnabled() 
    {
        if($this->cacheEnable) {
            return $this->cacheSupport = extension_loaded('apc') ;
        }
        return false;
    }


    /**
     * Get registered assets and return asset objects.
     *
     * @return AssetKit\Asset[]
     */
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

