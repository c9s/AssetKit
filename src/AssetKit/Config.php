<?php
namespace AssetKit;

class Config
{

    const FORMAT_JSON = 1;
    const FORMAT_PHP = 1;

    /**
     * @var string $file the config file path
     */
    public $file;


    /**
     * @var array $config the config hash.
     *
     *    'baseDir': the base directory for public files.
     *    'baseUrl': The base url for front-end
     */
    public $config;


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
            $cacheId = isset($options['cache_id'])
                ? $options['cache_id']
                : __DIR__;
            $this->config = apc_fetch($cacheId);
        }

        if ( ! $this->config ) {
            // read the config file
            if( file_exists($file) ) {
                // use php format config by default, this is faster than JSON.
                $format = isset($options['format']) 
                    ? $options['format']
                    : self::FORMAT_PHP;
                
                $this->load($format);

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
     * Load or reload the config file.
     * 
     * @param integer $format FORMAT_PHP or FORMAT_JSON
     */
    public function load($format = self::FORMAT_PHP )
    {
        return $this->config = $this->readFile( $this->file , $format );
    }


    /**
     * Read a config from a file.
     *
     * @param string $file
     * @param integer $format FORMAT_PHP or FORMAT_JSON
     * @return array config array
     */
    public function readFile($file,$format = self::FORMAT_PHP ) 
    {
        if($format == self::FORMAT_PHP ) {
            return require($file);
        } elseif ($format == self::FORMAT_JSON ) {
            return json_decode(file_get_contents($file),true);
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
    public function getRegisteredAssets()
    {
        if( isset($this->config['assets'] ) ) {
            return $this->config['assets'];
        }
        return array();
    }

    public function getAssetDirectories()
    {
        return $this->config['dirs'];
    }


    /**
     * set config, if you need to replace it.
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }



    /**
     * get config
     */
    public function getConfig()
    {
        return $this->config;
    }


    /**
     * Write current config to file
     *
     * @param string $filename 
     * @param integer $format Can be FORMAT_PHP, FORMAT_JSON.
     */
    public function writeFile($path, $config, $format = self::FORMAT_PHP )
    {
        if( $format == self::FORMAT_JSON ) {
            if( ! defined('JSON_PRETTY_PRINT') )
                define('JSON_PRETTY_PRINT',0);
            return file_put_contents($path, json_encode($config,
                JSON_PRETTY_PRINT));
        } else if ($format == self::FORMAT_PHP ) {
            $php = '<?php return ' .  var_export($config,true) . ';';
            return file_put_contents($path, $php);
        }
    }


    /**
     * Save current asset config with $format
     *
     * @param integer $format FORMAT_PHP or FORMAT_JSON
     */
    public function save($format = self::FORMAT_PHP)
    {
        return $this->writeFile($this->file, $this->config, $format);
    }


    /**
     * Get baseDir, this is usually used for compiling and minifing.
     *
     * @param bool $absolute reutrn absolute path or not 
     * @return string the path
     */
    public function getBaseDir($absolute = false) 
    {
        if( isset($this->config['baseDir']) ) 
            return $this->config['baseDir'];
    }


    /**
     * Get baseUrl for front-end including
     * 
     * @return string the path.
     */
    public function getBaseUrl()
    {
        if( isset($this->config['baseUrl']) ) 
            return $this->config['baseUrl'];
    }

    public function setBaseUrl($url) 
    {
        $this->config['baseUrl'] = $url;
    }

    public function setBaseDir($dir) 
    {
        $this->config['baseDir'] = $dir;
    }

}


