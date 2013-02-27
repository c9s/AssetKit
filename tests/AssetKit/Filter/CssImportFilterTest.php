<?php

class CssImportFilterTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $config = new AssetKit\AssetConfig('.assetkit.php');
        ok($config, 'asset config object');

        $loader = new AssetKit\AssetLoader($config);
        $jqueryui = $loader->load('jquery-ui');
        ok($jqueryui, 'jqueryui asset is loaded');

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

