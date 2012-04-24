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

        $asset = new Asset($manifestPath);
        $asset->initResource();



        // get asset files and copy them into 
        $fromDir = $asset->dir;
        $n       = $asset->name;
        foreach( $asset->getFileCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $path;
                $srcFile = $fromDir . $subpath;
                $targetFile = $config->getPublicRoot() . $subpath;

                // we should run filters per file.
                //   - CssRewrite
                //   - CoffeeScript
                $tmp = new \AssetKit\FileCollection;
                $tmp->addFile( $srcFile );
            }
        }

        $config->addAsset( $asset->name , $asset->export() );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


