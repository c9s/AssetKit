<?php

class FileCollectionTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $cln = new AssetKit\FileCollection;
        $cln->addFile( 'assets/jquery/jquery/dist/jquery.js' );

        ok($cln);
    }
}

