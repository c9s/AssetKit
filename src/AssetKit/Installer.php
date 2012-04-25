<?php
namespace AssetKit;

class Installer
{
    public $enableLog = true;

    public function log($msg)
    {
        if( $this->enableLog )
            echo $msg , "\n";
    }


    public function uninstall($asset)
    {
        // get asset files and copy them into 
        $fromDir = $asset->dir;
        $n       = $asset->name;

        // install into public asset root.
        foreach( $asset->getFileCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = $path;
                $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;
                $targetFile = $asset->config->getPublicAssetRoot() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;

                $this->log("x $targetFile");
                unlink( $targetFile );
            }
        }
    }

    public function install($asset)
    {
        // get asset files and copy them into 
        $fromDir = $asset->dir;
        $n       = $asset->name;

        // install into public asset root.
        foreach( $asset->getFileCollections() as $collection ) {
            foreach( $collection->getFilePaths() as $path ) {
                $subpath = $path;
                $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;
                $targetFile = $asset->config->getPublicAssetRoot() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;

                $this->log("x $targetFile");

                $content = file_get_contents($srcFile);
                if( file_exists($targetFile) ) {
                    $contentOrig = file_get_contents($targetFile);
                    if( ($chk1 = md5($content)) !== ($chk2 = md5($contentOrig)) ) {
                        echo "Checksum mismatch: \n";
                        echo "$chk2: $targetFile (original)";
                        echo "$chk1: $targetFile";
                        exit(1);
                    }
                }

                FileUtils::mkdir_for_file( $targetFile );
                file_put_contents( $targetFile , $content );
            }
        }
    }

}


