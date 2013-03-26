<?php
namespace AssetToolkit\Command;

use Exception;
use DateTime;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\AssetCompiler;
use AssetToolkit\Asset;
use AssetToolkit\Command\BaseCommand;
use CLIFramework\Command;

class CompileCommand extends BaseCommand
{
    public function options($opts)
    {
        parent::options($opts);
        $opts->add('t|target:', 'The target ID');
        $opts->add('html-output:', 'Output html file');
    }

    public function brief() { return 'precompile asset files.'; }

    public function execute()
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();

        $target = $this->options->target ?: $config->getDefaultTarget();

        if ( $target != $config->getDefaultTarget() ) {
            if ( $config->hasTarget($target) ) {
                $assetNames = $config->getTarget($target);
            } else {
                $assetNames = func_get_args();
            }
        } else {
            $assetNames = func_get_args();
            if( empty($assetNames) ) {
                throw new Exception("Asset names are required.");
            }
        }

        if( ! ini_get('apc.enable_cli') ) {
            $this->logger->info("Notice: You may enable apc.enable_cli option to precompile production files from command-line.");
        }


        // initialize loader and writer
        $this->logger->info("Loading assets " . join(', ', $assetNames));
        $assets = $loader->loadAssets($assetNames);

        $compiler = new AssetCompiler($config,$loader);
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();


        $this->logger->info("Compiling assets to target '$target'...");
        
        // force compile
        $files = $compiler->compileAssetsForProduction($assets, $target, true); // use force to compile.

        printf( "----------------------------------------------------\n" );
        printf( "Target:            %s\n" , $files['target'] );
        printf( "Cache Key:         %s\n" , $files['cache_key'] );
        printf( "Modofication Time: %s\n" , date(\DateTime::ATOM,$files['mtime']) );

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
        printf( "----------------------------------------------------\n" );


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
            printf( "----------------------------------------------------\n" );
            echo $html;
            printf( "----------------------------------------------------\n" );
        }
        $this->logger->info("Done");
    }
}




