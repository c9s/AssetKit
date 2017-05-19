<?php

namespace AssetKit;

use AssetKit\AssetCollection;
use AssetKit\TestCase;

class AssetCollectionTest extends TestCase
{
    public function testAssetCollectionConstructor()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $assets = array();
        $assets[] = $loader->register("tests/assets/jquery");
        $assets[] = $loader->register("tests/assets/jquery-ui");
        $collection = new AssetCollection($assets);
        $this->assertNotNull($collection);
    }

    public function testAssetCollectionAppend()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $collection = new AssetCollection();
        $this->assertNotNull($collection);

        $collection->add($loader->register("tests/assets/jquery"));
        $collection->add($loader->register("tests/assets/jquery-ui"));
    }
}

