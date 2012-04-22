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

        $manifest = $writer->from( array($asset) )
            ->name( 'jqueryui' )
            ->in('tests/public/assets')
            ->publicDir('tests/public')
            ->write();

        var_dump( $manifest ); 
    }
}

