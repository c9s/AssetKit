<?php
namespace AssetKit;
use AssetKit\FileUtil;

class Installer
{
    public $enableLog = true;
    public $logger;

    public function log($msg)
    {
        if( $this->enableLog ) {
            if( $this->logger ) {
                $this->logger->info( $msg );
            } else {
                echo $msg , "\n";
            }
        }
    }


    public function uninstall($asset)
    {
        // get asset files and copy them into 
        $fromDir = $asset->sourceDir;
        $n       = $asset->name;

        // install into public asset root.
        foreach( $asset->getCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = $path;
                $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;
                $targetFile = $asset->config->getBaseDir() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;

                $this->log("x $targetFile");
                unlink( $targetFile );
            }
        }
    }

    public function install($asset)
    {
        // get asset files and copy them into 
        $fromDir = $asset->sourceDir;
        $n       = $asset->name;

        // install into public asset root.
        foreach( $asset->getCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = $path;
                $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;

                if( ! file_exists($srcFile) ) {
                    $this->log("$srcFile not found.");
                    continue;
                }

                $targetFile = $asset->config->getBaseDir() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;

                $content = file_get_contents($srcFile);
                if( file_exists($targetFile) ) {
                    $contentOrig = file_get_contents($targetFile);
                    if( ($chk1 = md5($content)) !== ($chk2 = md5($contentOrig)) ) {
                        echo "Checksum mismatch: \n";
                        echo "$chk2: $targetFile (original)\n";
                        echo "$chk1: $targetFile\n";
                        echo ">> Overwrite ? (Y/n) ";
                        $line = trim(fgets(STDIN));
                        if( $line == "n" ) {
                            echo "Skip\n";
                            continue;
                        }
                    }
                    else {
                        // skip existing files
                        $this->log("- $targetFile");
                        continue;
                    }
                }
                FileUtil::mkdir_for_file( $targetFile );
                $this->log("x $targetFile");
                file_put_contents( $targetFile , $content ) or die("$targetFile write failed.");
            }
        }
    }
}


