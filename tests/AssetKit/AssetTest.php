<?php

class AssetTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $config = new AssetKit\Config('.tests_assetkit');
        $config->public = 'public';

        $loader = new AssetKit\AssetLoader($config);
        ok( $loader );

        $as = new AssetKit\Asset('assets/jquery-ui/manifest.yml');
        $as->config = $config;
        ok( $as );

        $config->addAsset( 'jquery-ui', $as );

        $installer = new \AssetKit\Installer;
        $installer->install( $as );

        is('/assets/jquery-ui', $as->getBaseUrl() );
        foreach( $as->getFileCollections() as $c ) {
            $paths = $c->getPublicPaths();
            foreach( $paths as $p ) { 
                file_ok( $p );
            }

            $urls = $c->getPublicUrls();
            ok( $paths );
        }

        $files = $as->createFileCollection();
        ok( $files );
        $files->addFile( 'assets/jssha/jsSHA/src/sha1.js' );
        $files->addFile( 'assets/jssha/jsSHA/src/sha256.js' );
        $files->addFilter( 'yui_js' );
        $mtime = $files->getLastModifiedTime();
        ok( $mtime );
        

#          $files->addFile( '...' );
#          $files->addFile( '...' );
#          $files->addFilter( '...' );
#          $files->addCompressor( '...' );

    }
}

