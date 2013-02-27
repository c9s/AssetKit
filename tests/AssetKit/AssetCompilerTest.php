<?php


class AssetCompilerTest extends AssetKit\TestCase
{
    public function test()
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $asset = $loader->registerFromManifestFileOrDir("tests/assets/jquery-ui");
        ok($asset);

        $compiler = new AssetKit\AssetCompiler;
        $files = $compiler->compile($asset);
        ok($files);
        ok($files['js']);
        ok($files['css']);
    }
}

