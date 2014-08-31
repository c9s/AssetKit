<?php
namespace AssetToolkit;
use ArrayAccess;
use AssetToolkit\Cache;


/**
 * An asset cache container that caches the config of the 
 * assets.
 */
class AssetEntryCluster implements ArrayAccess
{
    /**
     * @array the assets array that contains the config of all assets.
     *
     *   $assets = [
     *       [asset name] = [ 'source' => .... ];
     *   ];
     */
    public $stash = array();

    public function __construct($stash = array()) {
        $this->stash = $stash;
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
        if( isset($this->stash[$name]) ) {
            return $this->stash[$name];
        }
    }

    /**
     * @var string check if we've loaded this asset.
     * @return bool
     */
    public function has($name)
    {
        return isset($this->stash[$name]);
    }

    /**
     * Add asset to the config assets.
     *
     * @param string $name
     * @param array $config
     */
    public function set($name, $config)
    {
        $this->stash[$name] = $config;
    }


    /**
     * Remove asset from config
     *
     * @param string $name asset name
     */
    public function remove($name)
    {
        // TODO: should also remove from assetObjects
        unset($this->stash[$name]);
    }

    public function removeAll() {
        $this->stash = array();
    }


    /**
     * Add an asset object to the config assets.
     *
     * @param Asset $asset
     */
    public function add(Asset $asset)
    {
        $this->stash[ $asset->name ] = $asset->export(); 
    }


    /**
     * Returns all asset objects (keys and values)
     */
    public function pairs()
    {
        return $this->stash;
    }

    /**
     * This method returns all assets in an indexed array.
     *
     * @return Asset[]
     */
    public function all()
    {
        return array_values($this->stash);
    }




    /***********************************************
     * Methods implemented for ArrayAccess interface.
     ***********************************************/


    public function offsetSet($name, $value)
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

    public function export() {
        return $this->stash;
    }

    public function save() {
        if (extension_loaded('apc') ) {
            apc_store($this->namespace . ':assets', $this->stash);
        }
    }

    public function __set_state($array) {
        $o = new self( $array['stash'] );
        return $o;
    }
}


