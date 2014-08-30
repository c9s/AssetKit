<?php
use AssetToolkit\AssetCache;

class AssetCacheTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $cache = new AssetCache([
            'namespace' => 'testing',
        ]);
        ok($cache);

        $cache['jquery'] = array();
        // ok($cache['jquery']);
    }
}

