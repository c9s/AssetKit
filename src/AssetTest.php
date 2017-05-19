<?php

namespace AssetKit;

use AssetKit\Asset;
use AssetKit\TestCase;

class AssetTest extends TestCase
{
    public function testLoadFromManifestFile()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $this->assertNotNull($config);
        $this->assertNotNull($loader);

        $as = new Asset($config);
        $as->loadManifestFile("tests/assets/jquery-ui/manifest.yml");
        $this->assertNotNull($as);

        $collections = $as->getCollections();
        $this->assertNotNull($collections);

        foreach( $collections as $c ) {
            $paths = $c->getSourcePaths();
            foreach( $paths as $p ) {
                file_ok( $p );
            }
            $this->assertNotNull( $paths );
        }
    }

    /*
        $config->addAsset( 'jquery-ui', $as );
        $installer = new \AssetKit\Installer($config);
        $installer->enableLog = false;
        $installer->install( $as );

        $this->assertEquals('public/assets/jquery-ui',$as->getInstallDir());
        $this->assertEquals('assets/jquery-ui',$as->getSourceDir());
        # $installer->uninstall( $as );
        $jssha = $loader->load('jssha');
        // $jssha->initResource();
    */
}

