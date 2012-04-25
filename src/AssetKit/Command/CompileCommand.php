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

        $config = new Config('.assetkit');

        $this->logger->info('Precompiling...');

        // initialize loader and writer
        $assets = $config->getAssets();

        $writer = new \AssetKit\AssetWriter( $config );

        // get asset files and copy them into 
        $fromDir = $asset->dir;
        $n       = $asset->name;

        // save installed asset files
        foreach( $asset->getFileCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = $path;
                $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;
                $targetFile = $config->getPublicRoot() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;

# XXX: move this compile operation into compile command:
# 
#                  if( $collection->isJavascript ) {
#                      $targetFile = \AssetKit\FileUtils::replace_extension( $targetFile, 'js' );
#                  }
#                  elseif( $collection->isStylesheet ) {
#                      $targetFile = \AssetKit\FileUtils::replace_extension( $targetFile , 'css' );
#                  }
#  
#                  $this->logger->info("Filtering content from $srcFile");
#                  // We should run filters per file.
#                  //   - CssRewrite
#                  //   - CoffeeScript
#                  $tmp = new \AssetKit\FileCollection;
#                  $tmp->isJavascript = $collection->isJavascript;
#                  $tmp->isStylesheet = $collection->isStylesheet;
#                  $tmp->filters = $collection->filters;
#                  $tmp->addFile( $srcFile );
#                  $writer->runCollectionFilters($tmp);

                $content = file_get_contents($srcFile);
                if( file_exists($targetFile) ) {
                    $contentOrig = file_get_contents($targetFile);
                    if( ($chk1 = md5($content)) !== ($chk2 = md5($contentOrig)) ) {
                        $this->logger->error("Checksum mismatch: ");
                        $this->logger->error("$chk2: $targetFile (original)");
                        $this->logger->error("$chk1: $targetFile");
                        exit(1);
                    }
                }
                $this->logger->info( "Writing $targetFile" );

                \AssetKit\FileUtils::mkdir_for_file( $targetFile );
                file_put_contents( $targetFile , $content );
            }
        }



        $this->logger->info("Done");
    }
}




