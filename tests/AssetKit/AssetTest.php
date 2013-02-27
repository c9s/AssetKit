<?php

class AssetTest extends AssetKit\TestCase
{
    public function test()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        /*
        $as = new AssetKit\Asset('assets/jquery-ui/manifest.yml');
        $as->config = $config;
        ok( $as );

        $config->addAsset( 'jquery-ui', $as );

        $installer = new \AssetKit\Installer;
        $installer->enableLog = false;
        $installer->install( $as );

        is('public/assets/jquery-ui',$as->getInstallDir());
        is('assets/jquery-ui',$as->getSourceDir());

        foreach( $as->getCollections() as $c ) {
            $paths = $c->getSourcePaths();
            foreach( $paths as $p ) { 
                file_ok( $p );
            }
            ok( $paths );
        }

#          $installer->uninstall( $as );

        $jssha = $loader->load('jssha');

        // $jssha->initResource();
         */
    }
}

