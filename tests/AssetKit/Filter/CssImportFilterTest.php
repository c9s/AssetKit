<?php
use AssetKit\Filter\CssRewriteFilter;
use AssetKit\Filter\CssImportFilter;

class CssImportFilterTest extends AssetKit\TestCase
{

    public function test()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $urlBuilder = new AssetKit\AssetUrlBuilder($config);

        $jqueryui = $loader->register('tests/assets/jquery-ui');
        $this->assertNotNull($jqueryui, 'jqueryui asset is loaded');

        $rewriteFilter = new CssRewriteFilter($config, $urlBuilder->buildBaseUrl($jqueryui) );
        $importFilter        = new CssImportFilter($config, $urlBuilder->buildBaseUrl($jqueryui) );
        foreach( $jqueryui->getCollections() as $c ) {

            // for css stylesheet
            if( $c->isStylesheet ) {
                $rewriteFilter->filter( $c );

                // $importFilter->filter( $c );
                $content = $c->getContent();
                $this->assertNotNull($content,"Got content");
                // echo $content;
            }
        }
    }


    public function testFilterPath()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $urlBuilder = new AssetKit\AssetUrlBuilder($config);

        $collection = new AssetKit\Collection;
        $collection->setContent("background: url(../images/file.png)");

        $jqueryui = $loader->register('tests/assets/jquery-ui');
        $this->assertNotNull($jqueryui, 'jqueryui asset is loaded');


        $rewriteFilter = new CssRewriteFilter($config, $urlBuilder->buildBaseUrl($jqueryui) );
        $importFilter        = new CssImportFilter($config, $urlBuilder->buildBaseUrl($jqueryui) );
        foreach( $jqueryui->getCollections() as $c ) {

            $this->assertNotNull( $c->getContent() ,'get content ok' );

            // for css stylesheet
            if( $c->isStylesheet ) {
                $rewriteFilter->filter( $c );

                // $importFilter->filter( $c );
                $content = $c->getContent();
                $this->assertNotNull($content,"Got content");
                // echo $content;
            }
        }

    }

}

