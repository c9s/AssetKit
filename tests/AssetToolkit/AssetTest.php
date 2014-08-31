<?php
use AssetToolkit\Asset;

class AssetTest extends AssetToolkit\TestCase
{
    public function testLoadFromManifestFile()
    {
        $config = $this->getConfigArray();
        $loader = $this->getLoader();
        ok($config);
        ok($loader);

        $as = new Asset($config);
        $as->loadFromManifestFile('tests/assets/jquery-ui/manifest.yml');
        ok($as);

        /*
        $config->addAsset( 'jquery-ui', $as );
        $installer = new \AssetToolkit\Installer;
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

