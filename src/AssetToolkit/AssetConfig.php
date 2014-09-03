<?php
namespace AssetToolkit;
use ConfigKit\ConfigCompiler;
use AssetToolkit\Asset;
use AssetToolkit\Data;
use Exception;
use ArrayAccess;

/**
 * AssetConfig defines methods to get/set asset config value.
 *
 *
 */
class AssetConfig implements ArrayAccess
{
    const PRODUCTION = 1;
    const DEVELOPMENT = 2;

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
     *    'BaseDir': The base directory for public files.
     *    'BaseUrl': The base url for front-end.
     *    'dirs': asset directories.
     *    'assets': contains asset configs
     *
     *       { 
     *          manifest: manifest path
     *          source_dir: asset directory
     *       }
     */
    public $stash = array();

    /**
     * @var array
     */
    public $options = array();

    /**
     * Cache object instance
     */
    public $cache;

    public function __construct($arg = null, $options = null) {
        if ($options) {
            $this->options = $options;
            if ( isset($options['root']) ) {
                $this->root = $options['root'];
            }
            if ( isset($options['cache']) ) {
                $this->cache = $options['cache'];
            }
        }
        if ($arg) {
            if (is_string($arg)) {
                $this->file = $arg;
                if (file_exists($arg)) {
                    $this->load();
                }
            } elseif (is_array($arg)) {
                $this->stash = $arg;
            }
        }
    }


    public function getDefaultTarget()
    {
        if ( isset($this->stash['DefaultTarget']) ) {
            return $this->stash['DefaultTarget'];
        }
        return 'minified';
    }

    public function getCache() {
        return $this->cache;
    }

    public function setCache($cache) {
        $this->cache = $cache;
    }

    public function setCacheDir($dir)
    {
        $this->stash['CacheDir'] = $dir;
    }

    public function getCacheDir($absolute = false)
    {
        $dir = null;
        if ( isset($this->stash['CacheDir']) ) {
            $dir = $this->stash['CacheDir'];
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
        if ( isset($this->stash['Namespace']) ) {
            return $this->stash['Namespace'];
        }
        return getcwd();
    }


    public function setNamespace($namespace)
    {
        $this->stash['Namespace'] = $namespace;
    }

    /**
     * Extra options
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }


    /**
     * @var string
     *
     * Return the file that stores data from AssetEntryCluster.
     */
    public function getEntryFile() {
        if (isset($this->stash['EntryFile']) ) {
            return $this->stash['EntryFile'];
        }
        return '.asset-entries.php';
    }

    public function getEnvironment() {
        if (isset($this->stash['Environment']) ) {
            return $this->stash['Environment'];
        }
    }

    public function setEnvironment($env)
    {
        if ($env == self::PRODUCTION) {
            $this->stash['Environment'] = 'production';
        } elseif ($env == self::DEVELOPMENT ) {
            $this->stash['Environment'] = 'development';
        } else {
            $this->stash['Environment'] = $env;
        }
    }

    public function loadFile($file) {
        $this->file = $file;
        $this->load();
    }

    /**
     * Load or reload the stash file.
     */
    public function load()
    {
        if (extension_loaded('apc')) {
            $key = 'asset-config:' . $this->root . ':' . $this->file;
            if ($stash = apc_fetch($key)) {
                $this->stash = $stash;
            } else {
                $this->stash = ConfigCompiler::load($this->file);
                apc_store($key, $this->stash);
            }
            if (!$this->stash) {
                throw new Exception('Config load failed: ' . $this->file);
            }
            return $this->stash;
        }
        return $this->stash = ConfigCompiler::load($this->file);
    }

    /**
     * Write current config to file
     *
     * @param string $filename
     */
    public function writeFile($filepath, $config)
    {
        ConfigCompiler::write_yaml($filepath, $config);
    }

    /**
     * Save current asset config with $format
     */
    public function save($file = null)
    {
        if ($file) {
            $this->file = $file;
        }
        if (!$this->file) {
            throw new Exception("Filename for config is not defined.");
        }
        ConfigCompiler::write_yaml($this->file, $this->stash);
    }



    /**
     * Register Assets to a target,
     * So that we can get assets by a target Id.
     *
     * @param string $targetId
     * @param string[] $assets The names of assets.
     */
    public function addTarget($targetId, $assets)
    {
        if ( ! isset($this->stash['Targets']) ) {
            $this->stash['Targets'] = array();
        }
        $this->stash['Targets'][ $targetId ] = $assets;
    }


    /**
     * Remove a target from the config stash
     *
     * @param string $targetId
     */
    public function removeTarget($targetId)
    {
        unset($this->stash['Targets'][ $targetId ]);
    }

    public function hasTarget($targetId)
    {
        return isset($this->stash['Targets'][ $targetId ]);
    }

    public function getTarget($targetId)
    {
        if ( isset($this->stash['Targets'][ $targetId ]) ) {
            return $this->stash['Targets'][ $targetId ];
        }
    }


    public function getTargets()
    {
        if ( isset($this->stash['Targets']) ) {
            return $this->stash['Targets'];
        }
    }

    /**
     * Add asset directory, this asset directory is for looking up asset to 
     * register.
     *
     * @param string $dir
     */
    public function addAssetDirectory($dir)
    {
        $this->stash['Dirs'][] = $dir;
    }



    /**
     * Return asset directories
     *
     * @return array
     */
    public function getAssetDirectories()
    {
        if(isset($this->stash['Dirs']) ) {
            return $this->stash['Dirs'];
        }
        return array();
    }

    /**
     * get config
     */
    public function getConfigArray()
    {
        return $this->stash;
    }


    /**
     * Get BaseDir, this is usually used for compiling and minifing.
     *
     * @param bool $absolute reutrn absolute path or not 
     * @return string the path
     */
    public function getBaseDir($absolute = false)
    {
        // Here the absolute base dir path should not be prefixed by fileDirectory
        // We should simply get the realpath in their context.
        if( isset($this->stash['BaseDir']) && $this->stash['BaseDir'] ) 
        {
            if( $absolute ) {
                return $this->getRoot() . DIRECTORY_SEPARATOR .  $this->stash['BaseDir'];
            }
            return $this->stash['BaseDir'];
        }
        throw new Exception("BaseDir is not defined in asset config.");
    }


    /**
     * Get BaseUrl for front-end including
     *
     * @return string the path.
     */
    public function getBaseUrl()
    {
        if ( isset($this->stash['BaseUrl']) && $this->stash['BaseUrl'] ) {
            return $this->stash['BaseUrl'];
        }
        throw new Exception("BaseUrl is not defined in asset config.");
    }



    /**
     * Get the base url of the installed assets.
     */
    public function setBaseUrl($url) 
    {
        $this->stash['BaseUrl'] = $url;
    }

    /**
     * Set the base dir of installed asset.
     *
     * @param string $path must be relative path.
     *
     */
    public function setBaseDir($path) 
    {
        $this->stash['BaseDir'] = $path;
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

    public function offsetSet($name,$value)
    {
        $this->stash[ $name ] = $value;
    }

    public function offsetExists($name)
    {
        return isset($this->stash[ $name ]);
    }

    public function offsetGet($name)
    {
        return $this->stash[ $name ];
    }

    public function offsetUnset($name)
    {
        unset($this->stash[$name]);
    }

}

