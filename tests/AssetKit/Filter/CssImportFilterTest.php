<?php

class CssImportFilterTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $config = new AssetKit\AssetConfig('.assetkit');
        $loader = new AssetKit\AssetLoader( $config, array( 'assets' ));

        $jqueryui = $loader->load('jquery-ui');

        $rewriteFilter = new AssetKit\Filter\CssRewriteFilter;

        $filter = new AssetKit\Filter\CssImportFilter;

        foreach( $jqueryui->getCollections() as $c ) {
            if( $c->isStylesheet ) {
                $rewriteFilter->filter( $c );

                // $filter->filter( $c );
                $content = $c->getContent();
                // echo $content;
            }
        }

        
    }
}

