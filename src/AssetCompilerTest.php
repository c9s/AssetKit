<?php

namespace AssetKit;

use AssetKit\AssetCompiler;
use AssetKit\ProductionAssetCompiler;
use AssetKit\AssetRender;
use AssetKit\Asset;
use AssetKit\TestCase;

class AssetCompilerTest extends TestCase
{

    public function testProductionAssetCompilerCache() {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $assets = array();
        $assets[] = $loader->register("tests/assets/jquery");
        $assets[] = $loader->register("tests/assets/jquery-ui");
        $assets[] = $loader->register("tests/assets/test");
        $assets[] = $loader->register("tests/assets/underscore");
        $assets[] = $loader->register("tests/assets/webtoolkit");
        foreach($assets as $asset) {
            $this->assertNotNull($asset);
            $this->assertInstanceOf(Asset::class, $asset);
        }

        $this->installAssets($assets);
        $compiler = new ProductionAssetCompiler($config,$loader);
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $entries = $compiler->compileAssets($assets,'myapp2', $force = true);
        $this->assertNotEmpty($entries);

        for($i = 0; $i < 100; $i++) {
            $entries2 = $compiler->compileAssets($assets,'myapp2', $force = false);
            $this->assertNotEmpty($entries2);
            $this->assertSame($entries, $entries2);
        }
    }

    public function testSassAsset()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $assets = array();
        $assets[] = $loader->register("tests/assets/test");
        $assets[] = $loader->register("tests/assets/simple-sass");

        $compiler = new ProductionAssetCompiler($config,$loader);
        $compiler->enableFstatCheck();
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $entries = $compiler->compileAssets($assets, 'sass-test', $force = true);
        $this->assertNotEmpty($entries);
        $this->assertCount(1, $entries);

        $this->assertFileExists($entries[0]['css_file']);
        $this->assertNotNull($entries[0]['mtime'], 'got mtime');

        $css = file_get_contents($entries[0]['css_file']);
        $this->assertNotNull($css);

        $this->assertContains('.subpath2{color:green}', $css, "Checking " . $entries[0]['css_file']);
        $this->assertContains('.subpath{color:red}', $css, "Checking " . $entries[0]['css_file']);
        $this->assertContains('.content-navigation{border-color:#3bbfce;color:#2ca2af}', $css);
        $this->assertContains('.extended', $css);

        $this->uninstallAssets($assets);
    }


    public function testCssImportUrlFromTestAssetInProductionMode()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $assets = array();
        $assets[] = $loader->register("tests/assets/jquery");
        $assets[] = $loader->register("tests/assets/jquery-ui");
        $assets[] = $loader->register("tests/assets/test");
        foreach($assets as $asset) {
            $this->assertInstanceOf(Asset::class, $asset);
        }

        $this->installAssets($assets);
        $compiler = new ProductionAssetCompiler($config,$loader);
        $compiler->enableFstatCheck();
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $entries = $compiler->compileAssets($assets,'myapp', $force = true);
        $this->assertNotEmpty($entries);
        path_ok($entries[0]['js_file']);
        path_ok($entries[0]['css_file']);
        $this->assertNotNull($entries[0]['mtime'], 'got mtime');


        /*
        array(7) {
            ["css_md5"]=> string(32) "07fb97faf2a7056360fb048aac109800"
            ["js_md5"]=> string(32) "d95da0fbdccc220ccb5e4949a41ec796"
            ["css_file"]=> string(88) "/Users/c9s/git/Work/AssetKit/tests/public/myapp/07fb97faf2a7056360fb048aac109800.min.css"
            ["js_file"]=> string(87) "/Users/c9s/git/Work/AssetKit/tests/public/myapp/d95da0fbdccc220ccb5e4949a41ec796.min.js"
            ["css_url"]=> string(54) "/assets/myapp/07fb97faf2a7056360fb048aac109800.min.css"
            ["js_url"]=> string(53) "/assets/myapp/d95da0fbdccc220ccb5e4949a41ec796.min.js"
            ["mtime"]=> int(1362217186)
        }
        */

        $cssminContent = file_get_contents($entries[0]['css_file']);
        $this->assertNotNull($cssminContent);

        // examine these paths
        $this->assertContains('background:url(/assets/test/images/test.png)', $cssminContent, "Checking " . $entries[0]['css_file']);




        /**
        $files which is something like:
        .array(4) {
            ["js_file"]      => string(68) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.js"
            ["css_file"]     => string(69) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.css"
            ["js_url"]  => string(30) "/assets/jquery-ui/jquery-ui.js"
            ["css_url"] => string(31) "/assets/jquery-ui/jquery-ui.css"
        }
         */
        // $this->assertEquals('/assets/jquery-ui/jquery-ui.js', $files['js_url'][0]);
        // $this->assertEquals('/assets/jquery-ui/jquery-ui.css', $files['css_url'][0]);
        $this->uninstallAssets($assets);
    }



    public function testDevelopmentModeShouldOnlyRunFiltersForjQueryUI()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $assets = array();
        $assets[] = $loader->register("tests/assets/test");
        $assets[] = $loader->register("tests/assets/simple-coffee");
        $assets[] = $loader->register("tests/assets/simple-sass");
        $assets[] = $loader->register("tests/assets/jquery");
        $assets[] = $loader->register("tests/assets/jquery-ui");
        $this->assertNotNull($assets);

        $this->installAssets($assets);

        $compiler = new AssetCompiler($config,$loader);
        $outs = $compiler->compileAssets($assets);
        $this->assertNotNull($outs);
        foreach($outs as $out) {
            $this->assertNotNull($out['type']);
            $this->assertNotNull(isset($out['url']) || isset($out['content']));
        }
        return $outs;
    }


    /**
     * @depends testDevelopmentModeShouldOnlyRunFiltersForjQueryUI
     */
    public function testAssetRenderForDevelopment($outs)
    {
        $render = new \AssetKit\AssetRender($this->getConfig(),$this->getLoader());

        // the below tests are only for local.
        if (getenv('TRAVIS_BUILD_ID')) {
            $this->markTestSkipped('Skip asset render test on travis-ci');
            return;
        }

        $outputFile = 'tests/asset_render.out';
        if (file_exists($outputFile)) {
            $expected = file_get_contents($outputFile);
            $render->renderFragments($outs);
            $this->expectOutputString($expected);
        } else {
            ob_start();
            $render->renderFragments($outs);
            $content = ob_get_contents();
            ob_clean();
            file_put_contents($outputFile, $content);
            echo "Rendered: \n";
            echo $content;
        }
    }


    public function testProductionAssetCompiler()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $asset = $loader->register("tests/assets/jquery-ui");
        $this->assertNotNull($asset);

        $compiler = new ProductionAssetCompiler($config,$loader);
        $compiler->enableFstatCheck();
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $installer = $this->getInstaller();
        $installer->install($asset);

        $entry = $compiler->compile($asset);
        $this->assertNotNull($entry);
        path_ok($entry['js_file']);
        path_ok($entry['css_file']);
        $this->assertEquals('/assets/compiled/jquery-ui.min.js', $entry['js_url']);
        $this->assertEquals('/assets/compiled/jquery-ui.min.css', $entry['css_url']);
        $installer->uninstall($asset);
    }


}

