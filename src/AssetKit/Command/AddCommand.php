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
        foreach( $asset->getFileCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = $path;
                $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;
                $targetFile = $config->getPublicRoot() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;

                // var_dump( $srcFile, $targetFile ); 

                // We should run filters per file.
                //   - CssRewrite
                //   - CoffeeScript
                $tmp = new \AssetKit\FileCollection;
                $tmp->filters = $collection->filters;
                $tmp->addFile( $srcFile );
                $writer->runCollectionFilters($tmp);

                $this->logger->info( "Writing to $targetFile" );

                // echo $tmp->getContent();
            }
        }

        $config->addAsset( $asset->name , $asset->export() );


        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


