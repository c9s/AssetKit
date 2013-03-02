<?php

use AssetKit\AssetCompiler;

class AssetCompilerTest extends AssetKit\TestCase
{
    public function testCssImportUrlFromTestAssetInProductionMode()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $assets = array();
        $assets[] = $loader->registerFromManifestFileOrDir("tests/assets/jquery");
        $assets[] = $loader->registerFromManifestFileOrDir("tests/assets/jquery-ui");
        $assets[] = $loader->registerFromManifestFileOrDir("tests/assets/test");
        ok($assets);

        $this->installAssets($assets);

        $compiler = $this->getCompiler();
        $compiler->setEnvironment( AssetCompiler::PRODUCTION );
        $compiler->enableProductionFstatCheck();

        $files = $compiler->compileAssets('myapp',$assets);
        ok($files);
        path_ok($files['js_file']);
        path_ok($files['css_file']);
        ok($files['mtime'], 'got mtime');


        /*
        var_dump( $files ); 
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

        $cssminContent = file_get_contents( $files['css_file'] );
        ok($cssminContent);

        // examine these paths
        $this->assertContains('background:url(/assets/test/images/test.png)', $cssminContent);
        $this->assertContains('.subpath2{color:green}', $cssminContent);
        $this->assertContains('.subpath{color:red}', $cssminContent);

        // ensure our sass is compiled.
        $this->assertContains('.content-navigation{border-color:#3bbfce;color:#2ca2af}', $cssminContent);



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
        $assets[] = $loader->registerFromManifestFileOrDir("tests/assets/test");
        $assets[] = $loader->registerFromManifestFileOrDir("tests/assets/jquery");
        $assets[] = $loader->registerFromManifestFileOrDir("tests/assets/jquery-ui");
        ok($assets);

        $this->installAssets($assets);

        $compiler = $this->getCompiler();
        $compiler->setEnvironment( AssetCompiler::DEVELOPMENT );
        $outs = $compiler->compileAssetsForDevelopment($assets);
        ok($outs);
        foreach($outs as $out) {
            ok($out['type']);
            ok(isset($out['url']) || isset($out['content']));
        }
    }

    public function testProductionModeForjQueryUI()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $asset = $loader->registerFromManifestFileOrDir("tests/assets/jquery-ui");
        ok($asset);

        $compiler = $this->getCompiler();
        $compiler->setEnvironment( AssetCompiler::PRODUCTION );

        $installer = $this->getInstaller();
        $installer->install($asset);

        $files = $compiler->compile($asset);
        ok($files);
        path_ok($files['js_file']);
        path_ok($files['css_file']);
        is('/assets/jquery-ui/jquery-ui.min.js', $files['js_url']);
        is('/assets/jquery-ui/jquery-ui.min.css', $files['css_url']);
        $installer->uninstall($asset);
    }


}

