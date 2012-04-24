<?php

class AssetLoaderTest extends PHPUnit_Framework_TestCase
{

    function testWriter()
    {
        // $config = new AssetKit\Config;
        // $loader = new AssetLoader( );
    }

    function test()
    {
        $config = new AssetKit\Config('tests_assetkit');
        $loader = new AssetKit\AssetLoader($config, array( 'assets') );
        $asset = $loader->load( 'jquery-ui' );
        ok( $asset );

        $collections = $asset->getFileCollections();
        foreach( $collections as $collection ) {
            $files = $collection->getFilePaths();
            ok( $files );
            foreach( $files as $file ) {
                file_exists($file);
            }
        }
        foreach( $collections as $collection ) {
            $content = $collection->getContent();
            ok( $content );
            ok( strlen( $content ) > 0 );
        }



        $writer = new AssetKit\AssetWriter( $config );
        $writer->enableCompressor = false;
        ok( $writer );


        $apc = new CacheKit\ApcCache(array( 'namespace' => uniqid() , 'default_expiry' => 3 ));
        $manifest = $writer->from( array($asset) )
            // ->cache( $apc )
            ->name( 'jqueryui' )
            ->in('assets') // public/assets
            ->write();

        var_dump( $manifest ); 
    }
}

