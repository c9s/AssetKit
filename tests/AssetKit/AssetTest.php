<?php

class AssetTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $as = new AssetKit\Asset('jquery-ui');
        ok( $as );

        $files = $as->createFileCollection();
        ok( $files );

#          $files->addFile( '...' );
#          $files->addFile( '...' );
#          $files->addFilter( '...' );
#          $files->addCompressor( '...' );

    }
}

