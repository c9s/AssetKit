<?php
namespace AssetKit\Command;
use Exception;
use AssetKit\Config;
use AssetKit\Asset;
use CLIFramework\Command;

class CompileCommand extends Command
{
    function options($opts)
    {
        $opts->add('a|as:', 'compile asset with an ID');
    }

    function brief() { return 'precompile asset files.'; }

    function execute()
    {
        $assets = func_get_args();
        $options = $this->options;

        if( empty($assets) ) {
            throw new Exception("asset name is required.");
        }

        if( null === $options->as ) {
            throw new Exception("please specify --as=name option.");
        }

        $as = $options->as;
        $config = new Config('.assetkit');

        $this->logger->info('Compiling...');

        // initialize loader and writer
        $assets = $config->getAssets();
        $writer = new \AssetKit\AssetWriter( $config );
        $manifest = $writer
            ->name( $as )
            ->writeForProduction( $assets );

        foreach( $manifest['javascripts'] as $file ) {
            $this->logger->info("x {$file["path"]}");
        }

        foreach( $manifest['stylesheets'] as $file ) {
            $this->logger->info("x {$file["path"]}");
        }

        $this->logger->info("Done");
    }
}




