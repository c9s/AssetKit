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

    }
}

