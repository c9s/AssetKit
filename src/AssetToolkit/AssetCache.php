<?php
namespace AssetToolkit;
use ArrayAccess;
/**
 * An asset cache container that caches the config of the 
 * assets.
 */
class AssetCache implements ArrayAccess
{
    /**
     * @var string namespace for caching
     */
    public $namespace;

    public $options = array();

    /**
     * @array the assets array that contains the config of all assets.
     *
     *   $assets = [
     *       [asset name] = [ 'source' => .... ];
     *   ];
     */
    public $assets = array();


    public function __construct($options = array()) {
        $this->options = $options;
        if ( isset($options['namespace']) ) {
            $this->namespace = $options['namespace'];
        }
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
    public function get($name)
    {
        if( isset($this->assets[$name]) ) {
            return $this->assets[$name];
        }
    }

    /**
     * @var string check if we've loaded this asset.
     * @return bool
     */
    public function has($name)
    {
        return isset($this->assets[$name]);
    }

    /**
     * Add asset to the config assets.
     *
     * @param string $name
     * @param array $config
     */
    public function set($name, $config)
    {
        $this->assets[$name] = $config;
    }


    /**
     * Remove asset from config
     *
     * @param string $name asset name
     */
    public function remove($name)
    {
        // TODO: should also remove from assetObjects
        unset($this->assets[$name]);
    }

    public function removeAll() {
        $this->assets = array();
    }


    /**
     * Add an asset object to the config assets.
     *
     * @param Asset $asset
     */
    public function add(Asset $asset)
    {
        $this->assets[ $asset->name ] = $asset->export(); 
    }


    /**
     * Returns all asset objects (keys and values)
     */
    public function pairs()
    {
        return $this->assets;
    }

    /**
     * This method returns all assets in an indexed array.
     *
     * @return Asset[]
     */
    public function all()
    {
        return array_values($this->assets);
    }




    /***********************************************
     * Methods implemented for ArrayAccess interface.
     ***********************************************/


    public function offsetSet($name, $value)
    {
        $this->assets[ $name ] = $value;
    }

    public function offsetExists($name)
    {
        return isset($this->assets[ $name ]);
    }

    public function offsetGet($name)
    {
        return $this->assets[ $name ];
    }

    public function offsetUnset($name)
    {
        unset($this->assets[$name]);
    }

}


