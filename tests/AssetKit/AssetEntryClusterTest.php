<?php
use AssetKit\AssetEntryStorage;
use AssetKit\TestCase;

class AssetEntryStorageTest extends TestCase
{
    public function testAssetEntryStorage()
    {
        $cache = new AssetEntryStorage;
        $this->assertNotNull($cache);
        $cache['foo'] = array( 'source_file' => '1' );
        $cache['bar'] = array( 'source_file' => '2' );
    }


    public function manifestProvider()
    {
        return array(
            array("tests/assets/jquery-ui"),
            array("tests/assets/jquery"),
            array("tests/assets/underscore"),
        );
    }


    /**
     * @dataProvider manifestProvider
     */
    public function testAssetEntryExport($manifestPath)
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $asset = $loader->register($manifestPath);
        $this->assertNotNull($asset, "Asset is loaded from $manifestPath");

        $entries = $loader->getEntries();
        $code = '$evalEntries = ' . var_export($entries, true) . ';';
        eval($code);
        $this->assertNotNull($evalEntries);
        $this->assertNotNull(isset($evalEntries[$asset->name]));
    }
}

