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
            ->production()
            ->write( $assets );

        foreach( $manifest['javascripts'] as $file ) {
            $this->logger->info("x {$file["path"]}");
        }

        foreach( $manifest['stylesheets'] as $file ) {
            $this->logger->info("x {$file["path"]}");
        }

        $php = '<?php return ' . var_export($manifest,1) . '; ?>';
        $this->logger->info("Generating manfiest file...");
        $manifestFile = 'manifest.php';
        file_put_contents( $manifestFile ,$php);
        $this->logger->info("x $manifestFile");

        $this->logger->info("Done");

        $this->logger->info(<<<END

Manifest file is generated, now you can simply require the manifest.php in your 
PHP application:

    \$manifest = require 'manifest.php';
    \$includer = new AssetKit\\IncludeRender;
    echo \$includer->render( \$manifest );

END
        );
    }
}




