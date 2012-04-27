<?php

class AssetLoaderTest extends PHPUnit_Framework_TestCase
{

    function setup()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader($config);

        $jquery = $loader->load( 'jquery' );
        $jquery->initResource();

        $jqueryui = $loader->load( 'jquery-ui' );
        $jqueryui->initResource();


        $installer = new AssetKit\Installer;
        $installer->enableLog = false;
        $installer->install( $jquery );
        $installer->install( $jqueryui );
    }


    function getLoader($config)
    {
        return new AssetKit\AssetLoader($config, array( 'assets','tests/assets' ) );
    }

    function getConfig()
    {
        return new AssetKit\Config('.testassetkit');
    }

    function testLoader()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader($config);
        $asset = $loader->load( 'jquery-ui' );
        ok( $asset );

        $collections = $asset->getFileCollections();
        foreach( $collections as $collection ) {
            $files = $collection->getFilePaths();
            ok( $files );
            foreach( $files as $file ) {
                file_exists($file);
            }
        }
        foreach( $collections as $collection ) {
            $content = $collection->getContent();
            ok( $content );
            ok( strlen( $content ) > 0 );
        }
    }

    function testWriter()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader($config);

        $writer = new AssetKit\AssetWriter( $config );
        $writer->enableCompressor = false;
        ok( $writer );

        $jquery = $loader->load('jquery');
        $jqueryui = $loader->load('jquery-ui');

        is( 'public/assets/jquery', $jquery->getPublicDir() );
        is( 'assets/jquery', $jquery->getSourceDir() );

        ok( $jquery );
        ok( $jqueryui );

        $installer = new AssetKit\Installer;
        $installer->enableLog = false;
        $installer->install( $jquery );
        $installer->install( $jqueryui );

        $assets = array();
        $assets[] = $jquery;
        $assets[] = $jqueryui;

        $manifest = $writer 
            ->name( 'app' )
            ->in('assets') // public/assets
            ->writeForProduction( $assets );

        ok( $manifest['javascripts'] );
        ok( $manifest['stylesheets'] );

        foreach( $manifest['javascripts'] as $file ) {
            ok( $file['url'] );
            file_ok( $file['path'] );
        }

        foreach( $manifest['stylesheets'] as $file ) {
            ok( $file['url'] );
            file_ok( $file['path'] );
        }


        $render = new AssetKit\IncludeRender;
        $html = $render->render( $manifest );

        like( '#<link rel="stylesheet" type="text/css"  href="/assets/app-\w+.css"/>
<script type="text/javascript"  src="/assets/app-\w+.js" > </script>#', $html );

    }

    function tearDown()
    {
#          $installer->uninstall( $jquery );
#          $installer->uninstall( $jqueryui );
    }
}

