<?php
use AssetKit\Asset;

class AssetTest extends AssetKit\TestCase
{

    public function testLoadFromManifestFile()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        ok($config);
        ok($loader);

        $as = new Asset($config);
        $as->loadFromManifestFile('tests/assets/jquery-ui/manifest.yml');
        ok($as);

        $collections = $as->getCollections();
        ok($collections);

        foreach( $collections as $c ) {
            $paths = $c->getSourcePaths();
            foreach( $paths as $p ) {
                file_ok( $p );
            }
            ok( $paths );
        }
    }





        /*
        $config->addAsset( 'jquery-ui', $as );
        $installer = new \AssetKit\Installer($config);
        $installer->enableLog = false;
        $installer->install( $as );

        is('public/assets/jquery-ui',$as->getInstallDir());
        is('assets/jquery-ui',$as->getSourceDir());


#          $installer->uninstall( $as );

        $jssha = $loader->load('jssha');

        // $jssha->initResource();
         */
}

