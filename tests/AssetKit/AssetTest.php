<?php

class AssetTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $as = new AssetKit\Asset('jquery-ui');
        ok( $as );

        
    }
}

