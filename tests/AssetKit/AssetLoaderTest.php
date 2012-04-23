<?php

class AssetLoaderTest extends PHPUnit_Framework_TestCase
{

    function test()
    {
        $config = new AssetKit\Config('tests_assetkit');
        $loader = new AssetKit\AssetLoader($config, array( 'assets') );
        $asset = $loader->load( 'jquery-ui' );
        ok( $asset );

        $collections = $asset->getFileCollections();
        foreach( $collections as $collection ) {
            $files = $collection->getFiles();
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


        // $loader->enableCompressor = false;

        $writer = new AssetKit\AssetWriter( $loader );
        ok( $writer );

        $writer->addCompressor('jsmin', function() {
            return new AssetKit\Compressor\JsMinCompressor;
        });
        $writer->addCompressor('cssmin', function() {
            return new AssetKit\Compressor\CssMinCompressor;
        });

        $writer->addFilter( 'css_rewrite', function() {
            // return new AssetKit\Compressor
        });

        $apc = new CacheKit\ApcCache(array( 'namespace' => uniqid() , 'default_expiry' => 3 ));
        $manifest = $writer->from( array($asset) )
            ->cache( $apc )
            ->name( 'jqueryui' )
            ->in('assets') // public/assets
            ->write();

        var_dump( $manifest ); 
    }
}

