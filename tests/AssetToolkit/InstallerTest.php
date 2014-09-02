<?php

class InstallerTest extends AssetToolkit\TestCase
{




    /**
     * @dataProvider assetDataProvider
     */
    public function testInstaller($assets)
    {
        $installer = new AssetToolkit\Installer($this->getConfig());
        ok($installer);
        foreach($assets as $asset) {
            $installer->install($asset);
        }
        foreach($assets as $asset) {
            $installer->uninstall($asset);
        }
    }

    /**
     * @dataProvider assetDataProvider
     */
    public function testLinkInstaller($assets)
    {
        $installer = new AssetToolkit\LinkInstaller($this->getConfig());
        ok($installer);
        foreach($assets as $asset) {
            $installer->install($asset);
        }
        foreach($assets as $asset) {
            $installer->uninstall($asset);
        }
    }

    public function assetDataProvider()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $assets = array();
        $assets[] = $loader->register('tests/assets/jquery');
        $assets[] = $loader->register('tests/assets/jquery-ui');
        return array( 
            array($assets)
        );
    }


}


