<?php

class FileCollectionTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $cln = new AssetKit\FileCollection;
        $cln->addFile( 'assets/jquery/jquery/dist/jquery.js' );
        ok($cln);

        $files = $cln->getFiles();
        ok( $files );

        foreach( $files as $file ) {
            file_ok( $file );
        }

        // read content from files
        $content = $cln->getContent();
        ok( $content );

        // ok, now let's use a compressor
        $compressor = new AssetKit\Compressor\Yui\JsCompressor(
            'utils/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar',
            '/usr/bin/java');
        ok( $compressor );
        $compressor->compress( $cln );

        $content = $cln->getContent();

        like( '/jQuery/', $content );
        


    }
}

