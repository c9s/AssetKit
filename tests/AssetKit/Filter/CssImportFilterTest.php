<?php

class CssImportFilterTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $config = new AssetKit\Config('.assetkit');
        $loader = new AssetKit\AssetLoader( $config, array( 'assets' ));

        $jqueryui = $loader->load('jquery-ui');

        $filter = new AssetKit\Filter\CssImportFilter;

        foreach( $jqueryui->getFileCollections() as $c ) {
            if( $c->isStylesheet ) {
                $filter->filter( $c );


                $content = $c->getContent();
                echo $content;
            }
        }

        
    }
}

