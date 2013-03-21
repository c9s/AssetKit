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
        $opts->add('t|target:', 'The target ID');
        $opts->add('html-output:', 'Output html file');
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

        if ( isset($files['css_file']) ) {
            printf( "Stylesheet:\n" );
            printf( "  MD5:   %s\n" , $files['css_checksum'] );
            printf( "  URL:   %s\n" , $files['css_url'] );
            printf( "  File:  %s\n" , $files['css_file'] );
            printf( "  Size:  %d KBytes\n" , filesize($files['css_file']) / 1024 );
        }

        if ( isset($files['js_file']) ) {
            printf( "Javascript:\n" );
            printf( "  MD5:   %s\n" , $files['js_checksum'] );
            printf( "  URL:   %s\n" , $files['js_url'] );
            printf( "  File:  %s\n" , $files['js_file'] );
            printf( "  Size:  %d KBytes\n" , filesize($files['js_file']) / 1024 );
        }

        $render = new \AssetToolkit\AssetRender($config, $loader);
        ob_start();
        $render->renderFragment($files);
        $html = ob_get_contents();
        ob_clean();

        if ( $outputFile = $this->options->{"html-output"} ) {
            $this->logger->info("Writing output to $outputFile");
            if ( false === file_put_contents($outputFile, $html) ) {
                throw new Exception("Can not write file.");
            }
            $this->logger->info("You may simply require this file to render.");
        } else {
            $this->logger->info("HTML Output (you may use --html-output option to write as a file):");
            echo $html;
        }
        $this->logger->info("Done");
    }
}




