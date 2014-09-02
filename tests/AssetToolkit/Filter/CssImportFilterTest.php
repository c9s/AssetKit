<?php
use AssetToolkit\Filter\CssRewriteFilter;
use AssetToolkit\Filter\CssImportFilter;

class CssImportFilterTest extends AssetToolkit\TestCase
{

    public function test()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $jqueryui = $loader->register('tests/assets/jquery-ui');
        ok($jqueryui, 'jqueryui asset is loaded');

        $rewriteFilter = new CssRewriteFilter($config);
        $filter        = new CssImportFilter($config);
        foreach( $jqueryui->getCollections() as $c ) {

            // for css stylesheet
            if( $c->isStylesheet ) {
                $rewriteFilter->filter( $c );

                // $filter->filter( $c );
                $content = $c->getContent();
                ok($content,"Got content");
                // echo $content;
            }
        }
    }


    public function testFilterPath()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $collection = new AssetToolkit\Collection;
        $collection->setContent("background: url(../images/file.png)");

        $jqueryui = $loader->register('tests/assets/jquery-ui');
        ok($jqueryui, 'jqueryui asset is loaded');


        $rewriteFilter = new CssRewriteFilter($config);
        $filter        = new CssImportFilter($config);
        foreach( $jqueryui->getCollections() as $c ) {

            ok( $c->getContent() ,'get content ok' );

            // for css stylesheet
            if( $c->isStylesheet ) {
                $rewriteFilter->filter( $c );

                // $filter->filter( $c );
                $content = $c->getContent();
                ok($content,"Got content");
                // echo $content;
            }
        }

    }

}

