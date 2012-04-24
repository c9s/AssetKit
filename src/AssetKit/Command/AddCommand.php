<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Asset;
use CLIFramework\Command;

class AddCommand extends Command
{
    function brief() { return 'add and initialize asset.'; }

    function execute($manifestPath)
    {
        $config = new Config('.assetkit');

        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        $asset = new \AssetKit\Asset($manifestPath);
        $asset->initResource();

        $writer = new \AssetKit\AssetWriter( $config );

        // get asset files and copy them into 
        $fromDir = $asset->dir;
        $n       = $asset->name;

        // save installed asset files
        $installed = array();

        foreach( $asset->getFileCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = $path;
                $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;
                $targetFile = $config->getPublicRoot() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;

                // var_dump( $srcFile, $targetFile ); 

                $this->logger->info("Filtering content from $srcFile");
                // We should run filters per file.
                //   - CssRewrite
                //   - CoffeeScript
                $tmp = new \AssetKit\FileCollection;
                $tmp->isJavascript = $collection->isJavascript;
                $tmp->isStylesheet = $collection->isStylesheet;
                $tmp->filters = $collection->filters;
                $tmp->addFile( $srcFile );
                $writer->runCollectionFilters($tmp);

                $content = $tmp->getContent();
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
                $installed[] = $targetFile;
            }
        }

        $export = $asset->export();
        $export['installed'] = $installed;
        $config->addAsset( $asset->name , $export );


        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


