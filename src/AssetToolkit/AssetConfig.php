<?php

namespace AssetToolkit;
use AssetToolkit\Asset;
use AssetToolkit\Data;
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

    const PRODUCTION = 1;
    const DEVELOPMENT = 2;

    /**
     * ->setEnvironment( AssetIncluder::PRODUCTION );
     * ->setEnvironment( AssetIncluder::DEVELOPMENT );
     */
    public $environment = self::DEVELOPMENT;


    /**
     * @var string namespace for caching
     */
    public $namespace;

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


    /**
     * Cache Interface object
     */
    public $cache;

    public $fileLoaded = false;

    public function __construct($file, $options = array())
    {
        $this->options = $options;
        if ( isset($options['environment']) ) {
            $this->environment = $this->options['environment'];
        }
        if ( isset($options['cache']) ) {
            // the cache object
            $this->cache = $options['cache'];
        }
        if ( isset($options['namespace']) ) {
            $this->setNamespace( $options['namespace'] );
        }
        $this->fileLoaded = $this->loadFromFile($file);
    }

    public function getDefaultTarget()
    {
        return 'minified';
    }


    /**
     * Set the cache backend.
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }



    /**
     * Get the cache object.
     */
    public function getCache()
    {
        return $this->cache;
    }



    public function setCacheDir($dir)
    {
        $this->config['cache_dir'] = $dir;
    }

    public function getCacheDir($absolute = false)
    {
        $dir = null;
        if ( isset($this->config['cache_dir']) ) {
            $dir = $this->config['cache_dir'];
        } else {
            $dir = 'cache'; // default cache_dir
        }
        return $absolute ? $this->getRoot() . DIRECTORY_SEPARATOR . $dir : $dir;
    }


    /**
     * Return namespace
     */
    public function getNamespace()
    {
        if ( $this->namespace ) {
            return $this->namespace;
        }
        if ( isset($this->config['namespace']) ) {
            return $this->config['namespace'];
        }
        return getcwd();
    }


    public function setNamespace($namespace)
    {
        $this->config['namespace'] = $namespace;
        $this->namespace = $namespace;
    }


    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function setEnvironment($env)
    {
        $this->environment = $env;
    }

    public function loadFromFile($file)
    {
        $this->file = $file;

        if (isset($this->options['root']) ) {
            $this->root = $this->options['root'];
        }

        if ($this->cache) {
            // get apc cache
            $cacheId = isset($this->options['cache_id'])
                ? $this->options['cache_id']
                : __DIR__;
            $this->config = $this->cache->get($cacheId);
        }

        if ( ! $this->config ) {
            // read the config file
            if( file_exists($this->file) ) {
                // use php format config by default, this is faster than JSON.
                $format = isset($this->options['format']) 
                    ? $this->options['format']
                    : self::FORMAT_PHP;

                $this->load($format);

                if($this->cache) {
                    $this->cache->set($cacheId, $this->config, 
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
                    'cache_dir' => 'cache',
                    'namespace' => $this->getNamespace(), // which returns default namespace (__DIR__)
                    'pages' => array(),
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
     * Register Assets to a target,
     * So that we can get assets by a target Id.
     *
     * @param string $targetId
     * @param string[] $assets
     */
    public function addTarget($targetId, $assets) 
    {
        if ( ! isset($this->config['target']) ) {
            $this->config['target'] = array();
        }
        $this->config['target'][ $targetId ] = $assets;
    }


    /**
     * Remove a target from the config stash
     *
     * @param string $targetId
     */
    public function removeTarget($targetId)
    {
        unset($this->config['target'][ $targetId ]);
    }

    public function hasTarget($targetId)
    {
        return isset($this->config['target'][ $targetId ]);
    }

    public function getTarget($targetId)
    {
        if ( isset($this->config['target'][ $targetId ]) ) {
            return $this->config['target'][ $targetId ];
        }
    }


    public function getTargets()
    {
        if ( isset($this->config['target']) ) {
            return $this->config['target'];
        }
    }


    /**
     * Get registered assets and return asset objects.
     *
     * @return AssetToolkit\Asset[]
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
     * Load asset from a manifest file.
     *
     * @param string $path
     * @parma integer $format
     */
    public function registerAssetFromManifestFile($path, $format = 0)
    {
        if( $format !== Data::FORMAT_PHP ) {
            $format = Data::FORMAT_PHP;
            $path = Data::compile_manifest_to_php($path);
        }

        if( ! file_exists($path) ) {
            throw new Exception("Manifest file not found: $path.");
        }

        $asset = new Asset($this);
        $asset->loadFromManifestFile($path, $format);
        $this->addAsset($asset);
        return $asset;
    }

    /**
     * If the given path is a directory, then we should 
     * find the manifest from the directory.
     *
     * @param string $path
     * @param integer $format
     */
    public function registerAssetFromPath($path) 
    {
        if( is_dir($path) ) {
            $path = FileUtil::find_non_php_manifest_file_from_directory( $path );
            $format = Data::detect_format_from_extension($path);
        }
        if( file_exists($path))  {
            return $this->registerAssetFromManifestFile($path, $format);
        }
        return false;
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
            if( $absolute ) {
                return $this->getRoot() . DIRECTORY_SEPARATOR .  $this->config['baseDir'];
            }
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


    public function getCompiledDir()
    {
        return $this->getBaseDir(true) . DIRECTORY_SEPARATOR . 'compiled';
    }

    public function getCompiledUrl()
    {
        return $this->getBaseUrl() . '/compiled';
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
        if ($this->root) {
            return $this->root;
        }
        return realpath(dirname($this->file));
    }

}

