<?php
namespace AssetKit;
use ArrayAccess;
use AssetKit\Cache;


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

    public $targets = array();

    public function __construct($stash = NULL) {
        if ($stash) {
            $this->stash = $stash;
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

    static public function __set_state($array) {
        $o = new self();
        $o->stash = $array['stash'];
        $o->targets = $array['targets'];
        return $o;
    }


    /**
     * Register Assets to a target,
     * So that we can get assets by a target Id.
     *
     * @param string $targetId
     * @param string[] $assets The names of assets.
     */
    public function addTarget($targetId, $assetNames)
    {
        $this->targets[ $targetId ] = $assetNames;
    }

    /**
     * Remove a target from the config stash
     *
     * @param string $targetId
     */
    public function removeTarget($targetId)
    {
        unset($this->targets[ $targetId ]);
    }

    public function hasTarget($targetId)
    {
        return isset($this->targets[ $targetId ]);
    }

    public function getTarget($targetId)
    {
        if ( isset($this->targets[ $targetId ]) ) {
            return $this->targets[ $targetId ];
        }
    }

    public function getTargets()
    {
        if ( isset($this->targets['Targets']) ) {
            return $this->targets['Targets'];
        }
    }

}


