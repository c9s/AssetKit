<?php
namespace AssetKit\Command;
use AssetKit\Config;
use AssetKit\Asset;
use AssetKit\FileUtils;
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

        // get asset files and copy them into 
        $fromDir = $asset->dir;
        $n       = $asset->name;

        $this->logger->info( "Installing {$asset->name}" );

        // install into public asset root.
        foreach( $asset->getFileCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = $path;
                $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;
                $targetFile = $config->getPublicAssetRoot() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;

                $this->logger->info("x $srcFile",1);
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

                FileUtils::mkdir_for_file( $targetFile );
                file_put_contents( $targetFile , $content );
            }
        }


        $export = $asset->export();
        $config->addAsset( $asset->name , $export );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


