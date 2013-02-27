<?php

class CssImportFilterTest extends AssetKit\TestCase
{

    public function test()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $jqueryui = $loader->registerFromManifestFileOrDir('tests/assets/jquery-ui');
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

