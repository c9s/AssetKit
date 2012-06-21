<?php

class AssetTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $config = new AssetKit\Config('.tests_assetkit');
        $config->public = 'public';

        $loader = new AssetKit\AssetLoader($config,array('assets','tests/assets'));
        ok( $loader );

        $as = new AssetKit\Asset('assets/jquery-ui/manifest.yml');
        $as->config = $config;
        ok( $as );

        $config->addAsset( 'jquery-ui', $as );

        $installer = new \AssetKit\Installer;
        $installer->enableLog = false;
        $installer->install( $as );

        is('public/assets/jquery-ui',$as->getInstalledDir());
        is('assets/jquery-ui',$as->getSourceDir());

        foreach( $as->getFileCollections() as $c ) {
            $paths = $c->getSourcePaths();
            foreach( $paths as $p ) { 
                file_ok( $p );
            }
            ok( $paths );
        }


#          $installer->uninstall( $as );

        $jssha = $loader->load('jssha');
        $jssha->initResource();

#          $files = $as->createFileCollection();
#          ok( $files );
#          $files->addFile( 'assets/jssha/jsSHA/src/sha1.js' );
#          $files->addFile( 'assets/jssha/jsSHA/src/sha256.js' );
#          $files->addFilter( 'yui_js' );
#          $mtime = $files->getLastModifiedTime();
#          ok( $mtime );
    }
}

