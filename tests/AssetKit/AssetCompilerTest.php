<?php
use AssetKit\AssetCompiler;
use AssetKit\ProductionAssetCompiler;
use AssetKit\AssetRender;
use AssetKit\Asset;

class AssetCompilerTest extends AssetKit\TestCase
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
            ok($asset);
            ok($asset instanceof Asset);
        }

        $this->installAssets($assets);
        $compiler = new ProductionAssetCompiler($config,$loader);
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $entries = $compiler->compileAssets($assets,'myapp2', $force = true);
        ok($entries);

        for($i = 0; $i < 100; $i++) {
            $entries2 = $compiler->compileAssets($assets,'myapp2', $force = false);
            ok($entries2);
            $this->assertSame($entries, $entries2);
        }
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
            ok($asset);
            ok($asset instanceof Asset);
        }

        $this->installAssets($assets);
        $compiler = new ProductionAssetCompiler($config,$loader);
        $compiler->enableFstatCheck();
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $entries = $compiler->compileAssets($assets,'myapp', $force = true);
        ok($entries);

        path_ok($entries[0]['js_file']);
        path_ok($entries[0]['css_file']);
        ok($entries[0]['mtime'], 'got mtime');


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

        $cssminContent = file_get_contents( $entries[0]['css_file'] );
        ok($cssminContent);

        // examine these paths
        $this->assertContains('background:url(/assets/test/images/test.png)', $cssminContent);
        $this->assertContains('.subpath2{color:green}', $cssminContent);
        $this->assertContains('.subpath{color:red}', $cssminContent);

        // ensure our sass is compiled.
        $this->assertContains('.content-navigation{border-color:#3bbfce;color:#2ca2af}', $cssminContent);
        $this->assertContains('.extended', $cssminContent);



        /**
        $files which is something like:
        .array(4) {
            ["js_file"]      => string(68) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.js"
            ["css_file"]     => string(69) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.css"
            ["js_url"]  => string(30) "/assets/jquery-ui/jquery-ui.js"
            ["css_url"] => string(31) "/assets/jquery-ui/jquery-ui.css"
        }
         */
        // is('/assets/jquery-ui/jquery-ui.js', $files['js_url'][0]);
        // is('/assets/jquery-ui/jquery-ui.css', $files['css_url'][0]);
        $this->uninstallAssets($assets);
    }



    public function testDevelopmentModeShouldOnlyRunFiltersForjQueryUI()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $assets = array();
        $assets[] = $loader->register("tests/assets/test");
        $assets[] = $loader->register("tests/assets/jquery");
        $assets[] = $loader->register("tests/assets/jquery-ui");
        ok($assets);

        $this->installAssets($assets);

        $compiler = new AssetCompiler($config,$loader);
        $outs = $compiler->compileAssets($assets);
        ok($outs);
        foreach($outs as $out) {
            ok($out['type']);
            ok(isset($out['url']) || isset($out['content']));
        }
        return $outs;
    }


    /**
     * @depends testDevelopmentModeShouldOnlyRunFiltersForjQueryUI
     */
    public function testAssetRenderForDevelopment($outs)
    {
        $render = new \AssetKit\AssetRender($this->getConfig(),$this->getLoader());
        ok($render);

        // the below tests are only for local.
        if( getenv('TRAVIS_BUILD_ID') )
            return;

        $outputFile = 'tests/asset_render.out';
        if( file_exists($outputFile)) {
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
        ok($asset);

        $compiler = new ProductionAssetCompiler($config,$loader);
        $compiler->enableFstatCheck();
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $installer = $this->getInstaller();
        $installer->install($asset);

        $entry = $compiler->compile($asset);
        ok($entry);
        path_ok($entry['js_file']);
        path_ok($entry['css_file']);
        is('/assets/compiled/jquery-ui.min.js', $entry['js_url']);
        is('/assets/compiled/jquery-ui.min.css', $entry['css_url']);
        $installer->uninstall($asset);
    }


}

