<?php
namespace AssetKit;
use AssetKit\Asset;
use AssetKit\Data;
use Exception;



/**
 * AssetConfig defines methods to get/set asset config value.
 *
 *
 */
class AssetConfig
{

    const FORMAT_JSON = 1;
    const FORMAT_PHP = 2;

    /**
     * @var string $file the config file path
     */
    public $file;


    /**
     * @var string root path (absolute path)
     */
    public $root;


    /**
     * @var array $config the config hash.
     *
     *    'baseDir': The base directory for public files.
     *    'baseUrl': The base url for front-end.
     *    'dirs': asset directories.
     *    'assets': contains asset configs
     *      
     *       { 
     *          manifest: manifest path
     *          source_dir: asset directory
     *       }
     */
    public $config = array();



    /**
     * @var array
     */
    public $options = array();


    public $cacheEnable = true;

    public $cacheSupport = false;

    public $fileLoaded = false;

    public function __construct($file, $options = array())
    {
        $this->options = $options;
        $this->fileLoaded = $this->loadFromFile($file);
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function loadFromFile($file)
    {
        $this->file = $file;


        if(isset($this->options['cache']) ) {
            $this->cacheEnable = $this->options['cache'];
        }

        $useCache = $this->cacheEnabled();
        if($useCache) {
            // get apc cache
            $cacheId = isset($this->options['cache_id'])
                ? $this->options['cache_id']
                : __DIR__;
            $this->config = apc_fetch($cacheId);
        }

        if ( ! $this->config ) {
            // read the config file
            if( file_exists($this->file) ) {
                // use php format config by default, this is faster than JSON.
                $format = isset($this->options['format']) 
                    ? $this->options['format']
                    : self::FORMAT_PHP;

                $this->load($format);

                if($useCache) {
                    apc_store($cacheId, 
                        $this->config, 
                        isset($this->options['cache_expiry']) 
                        ? $this->options['cache_expiry'] 
                        : 0 
                    );
                }
            } else {
                // default config
                $this->config = array(
                    'baseDir' => null,
                    'baseUrl' => null,
                    'dirs' => array(),
                    'assets' => array(),
                );
                return false;
            }
        }
        return true;
    }


    public function getCacheExpiry()
    {
        return isset($this->options['cache_expiry']) 
            ? $this->options['cache_expiry'] 
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
    public function readFile($file,$format = Data::FORMAT_PHP ) 
    {
        return Data::decode_file($file, $format);
    }




    /**
     * Write current config to file
     *
     * @param string $filename 
     * @param integer $format Can be FORMAT_PHP, FORMAT_JSON.
     */
    public function writeFile($path, $config, $format = Data::FORMAT_PHP )
    {
        return Data::encode_file($path, $config, $format);
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
     * Add an asset object to the config stash.
     *
     * @param Asset $asset
     */
    public function addAsset(Asset $asset)
    {
        $this->config['assets'][ $asset->name ] = array(
            'manifest' => $asset->manifestFile,
            'source_dir' => $asset->sourceDir,
            'name' => $asset->name,
        );
    }


    /**
     * Add asset to the config stash.
     *
     * @param string $name
     * @param array $config
     */
    public function setAssetConfig($name,$config)
    {
        $this->config['assets'][$name] = $config;
    }


    /**
     * Remove asset from config
     *
     * @param string $name asset name
     */
    public function removeAsset($name)
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
     * Get baseDir, this is usually used for compiling and minifing.
     *
     * @param bool $absolute reutrn absolute path or not 
     * @return string the path
     */
    public function getBaseDir($absolute = false)
    {
        // Here the absolute base dir path should not be prefixed by fileDirectory
        // We should simply get the realpath in their context.
        if( isset($this->config['baseDir']) && $this->config['baseDir'] ) 
        {
            if( $absolute )
                return realpath($this->config['baseDir']);
            return $this->config['baseDir'];
        }
        throw new Exception("baseDir is not defined in asset config.");
    }


    /**
     * Get baseUrl for front-end including
     * 
     * @return string the path.
     */
    public function getBaseUrl()
    {
        if( isset($this->config['baseUrl']) && $this->config['baseUrl'] ) 
            return $this->config['baseUrl'];
        throw new Exception("baseUrl is not defined in asset config.");
    }



    /**
     * Get the base url of the installed assets.
     */
    public function setBaseUrl($url) 
    {
        $this->config['baseUrl'] = $url;
    }



    /**
     * Get the base dir of installed asset.
     */
    public function setBaseDir($dir) 
    {
        $this->config['baseDir'] = $dir;
    }

    public function setRoot($root)
    {
        $this->root = $root;
    }


    /**
     * Return the config file dir path.
     */
    public function getRoot()
    {
        if($this->root)
            return $this->root;
        throw new Exception("root is not defined.");
    }

}

