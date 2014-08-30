<?php
use AssetToolkit\AssetEntryCluster;

class AssetEntryClusterTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $cache = new AssetEntryCluster;
        ok($cache);

        $cache['foo'] = array( 'source_file' => '1' );
        $cache['bar'] = array( 'source_file' => '2' );

        if (extension_loaded('apc') && ini_get('apc.enable_cli') ) {
        }
    }
}

