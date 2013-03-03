<?php
namespace AssetToolkit\Command;
use Exception;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\AssetCompiler;
use AssetToolkit\Asset;
use CLIFramework\Command;

class CompileCommand extends Command
{
    public function options($opts)
    {
        $opts->add('t|target:', 'the target ID');
    }

    public function brief() { return 'precompile asset files.'; }

    public function execute()
    {
        $assetNames = func_get_args();

        if( empty($assetNames) ) {
            throw new Exception("asset name is required.");
        }

        $target = $this->options->target;
        $configFile = $this->options->config ?: ".assetkit.php";
        $config = new AssetConfig($configFile);
        $loader = new AssetLoader($config);

        if( ! ini_get('apc.enable_cli') ) {
            $this->logger->info("Notice: You may enable apc.enable_cli option to precompile production files from command-line.");
        }

        $this->logger->info("Compiling assets to target '$target'...");

        // initialize loader and writer
        $assets = $loader->loadAssets($assetNames);

        $compiler = new AssetCompiler($config,$loader);
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();
        
        // force compile
        $files = $compiler->compileAssetsForProduction($assets,$target, true);

        printf( "Stylesheet:\n" );
        printf( "  MD5:   %s\n" , $files['css_md5'] );
        printf( "  URL:   %s\n" , $files['css_url'] );
        printf( "  File:  %s\n" , $files['css_file'] );
        printf( "  Size:  %d KBytes\n" , filesize($files['css_file']) / 1024 );

        printf( "Javascript:\n" );
        printf( "  MD5:   %s\n" , $files['js_md5'] );
        printf( "  URL:   %s\n" , $files['js_url'] );
        printf( "  File:  %s\n" , $files['js_file'] );
        printf( "  Size:  %d KBytes\n" , filesize($files['js_file']) / 1024 );

        $this->logger->info("Done");
    }
}




