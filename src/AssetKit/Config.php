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

    public $fileDirectory;


    /**
     * @var array $config the config hash.
     *
     *    'baseDir': The base directory for public files.
     *    'baseUrl': The base url for front-end.
     *    'dirs': asset directories.
     */
    public $config;



    /**
     * @var array
     */
    public $options = array();


    public $cacheEnable = true;

    public $cacheSupport = false;

    public function __construct($file, $options = array())
    {
        $this->file = $file;
        $this->fileDirectory = dirname(realpath($file));
        $this->options = $options;

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
            if( file_exists($this->file) ) {
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
                // default config
                $this->config = array(
                    'baseDir' => '',
                    'baseUrl' => '',
                    'dirs' => array(),
                    'assets' => array(),
                );
            }
        }
    }


    public function getCacheExpiry()
    {
        return isset($options['cache_expiry']) 
            ? $options['cache_expiry'] 
            : 0;
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
     * Load or reload the config file.
     * 
     * @param integer $format FORMAT_PHP or FORMAT_JSON
     */
    public function load($format = self::FORMAT_PHP )
    {
        return $this->config = $this->readFile( $this->file , $format );
    }






    public function configExists()
    {
        return file_exists($this->file);
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



    /**
     * Get the config of name asset.
     *
     * The asset config contains:
     *
     *   "manifest": "tests\/assets\/jquery\/manifest.yml"
     *   "source_dir": "tests\/assets\/jquery"
     *   "name": "jquery"
     *
     * @param string $name asset name
     */
    public function getAssetConfig($name)
    {
        if( isset($this->config['assets'][$name]) ) {
            return $this->config['assets'][$name];
        }
        return null;
    }



    /**
     * Remove asset from config
     *
     * @param string $name asset name
     */
    public function removeAssetConfig($name)
    {
        unset($this->config['assets'][$name]);
    }



    /**
     * Add asset directory, this asset directory is for looking up asset to 
     * register.
     *
     * @param string $dir
     */
    public function addAssetDirectory($dir)
    {
        $this->config['dirs'][] = $dir;
    }



    /**
     * Return asset directories
     *
     * @return array
     */
    public function getAssetDirectories()
    {
        if(isset($this->config['dirs']) ) {
            return $this->config['dirs'];
        }
        return array();
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
        if( isset($this->config['baseDir']) ) {
            if($absolute) {
                return $this->fileDirectory . DIRECTORY_SEPARATOR . $this->config['baseDir'];
            }
            return $this->config['baseDir'];
        }
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

