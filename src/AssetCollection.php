<?php
namespace AssetKit;
use ArrayAccess;
use AssetKit\Cache;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use AssetKit\Asset;

class AssetCollection implements ArrayAccess, IteratorAggregate, Countable
{
    public $assets = array();

    public function __construct($assets = null)
    {
        if ($assets) {
            $this->assets = $assets;
        }
    }

    public function add(Asset $asset)
    {
        $this->assets[] = $asset;
    }

    public function getIterator() {
        return new ArrayIterator($this->assets);
    }

    public function count() {
        return count($this->assets);
    }
    
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



