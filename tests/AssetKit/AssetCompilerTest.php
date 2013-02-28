<?php

use AssetKit\AssetCompiler;

class AssetCompilerTest extends AssetKit\TestCase
{

    public function testCssImportUrlFromTestAsset()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $asset = $loader->registerFromManifestFileOrDir("tests/assets/test");
        ok($asset);


        $compiler = new AssetCompiler;
        $compiler->setEnvironment( AssetCompiler::PRODUCTION );
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $installer = $this->getInstaller();
        $installer->install($asset);

        $files = $compiler->compile($asset);
        ok($files);

        var_dump( $files );

        foreach($files['js'] as $file ) {
            path_ok($file);
        }
        foreach($files['css'] as $file ) {
            path_ok($file);
        }

        $cssminContent = file_get_contents( $files['css'][0] );
        ok($cssminContent);

        // We should get:
        // .image{background:url(/assets/test/images/test.png)}


        /**
        $files which is something like:

        .array(4) {
            ["js"]=>
                string(68) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.js"
            ["css"]=>
                string(69) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.css"
            ["js_url"]=>
                string(30) "/assets/jquery-ui/jquery-ui.js"
            ["css_url"]=>
                string(31) "/assets/jquery-ui/jquery-ui.css"
        }
         */
        // is('/assets/jquery-ui/jquery-ui.js', $files['js_url'][0]);
        // is('/assets/jquery-ui/jquery-ui.css', $files['css_url'][0]);
    }

    public function testDevelopmentModeShouldOnlyRunFiltersForjQueryUI()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $asset = $loader->registerFromManifestFileOrDir("tests/assets/jquery-ui");
        ok($asset);

        $compiler = new AssetCompiler;
        $compiler->setEnvironment( AssetCompiler::DEVELOPMENT );
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $installer = $this->getInstaller();
        $installer->install($asset);

        $files = $compiler->compile($asset);
        ok($files);

        foreach($files['js'] as $file ) {
            path_ok($file);
        }
        foreach($files['css'] as $file ) {
            path_ok($file);
        }


        /**
        $files which is something like:

        .array(4) {
            ["js"]=>
                string(68) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.js"
            ["css"]=>
                string(69) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.css"
            ["js_url"]=>
                string(30) "/assets/jquery-ui/jquery-ui.js"
            ["css_url"]=>
                string(31) "/assets/jquery-ui/jquery-ui.css"
        }
         */
        is('/assets/jquery-ui/jquery-ui.js', $files['js_url'][0]);
        is('/assets/jquery-ui/jquery-ui.css', $files['css_url'][0]);
    }

    public function testProductionModeForjQueryUI()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $asset = $loader->registerFromManifestFileOrDir("tests/assets/jquery-ui");
        ok($asset);

        $compiler = new AssetCompiler;
        $compiler->setEnvironment( AssetCompiler::PRODUCTION );
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();

        $installer = $this->getInstaller();
        $installer->install($asset);

        $files = $compiler->compile($asset);
        ok($files);


        foreach($files['js'] as $file ) {
            path_ok($file);
        }
        foreach($files['css'] as $file ) {
            path_ok($file);
        }

        /**
        $files which is something like:

        .array(4) {
            ["js"]=>
                string(68) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.js"
            ["css"]=>
                string(69) "/Users/c9s/src/work/php/AssetKit/tests/public/jquery-ui/jquery-ui.css"
            ["js_url"]=>
                string(30) "/assets/jquery-ui/jquery-ui.js"
            ["css_url"]=>
                string(31) "/assets/jquery-ui/jquery-ui.css"
        }
         */
        is('/assets/jquery-ui/jquery-ui.min.js', $files['js_url'][0]);
        is('/assets/jquery-ui/jquery-ui.min.css', $files['css_url'][0]);
    }


}

