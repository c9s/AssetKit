<?php

class AssetLoaderTest extends PHPUnit_Framework_TestCase
{

    function getConfig()
    {
        return new AssetKit\Config('.testassetkit');
    }

    function testLoader()
    {
        $config = $this->getConfig();
        $loader = new AssetKit\AssetLoader($config, array( 'assets') );
        $asset = $loader->load( 'jquery-ui' );
        ok( $asset );

        $installer = new AssetKit\Installer;
        $installer->enableLog = false;
        $installer->install( $asset );

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
        $writer = new AssetKit\AssetWriter( $config );
        $writer->enableCompressor = false;
        ok( $writer );

        $loader = new AssetKit\AssetLoader( $config , array('assets') );
        $jquery = $loader->load('jquery');
        $jqueryui = $loader->load('jquery-ui');

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
            ->name( 'jquery' )
            ->in('assets') // public/assets
            ->write( $assets );

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

        $installer->uninstall( $jquery );
        $installer->uninstall( $jqueryui );

    }
}

