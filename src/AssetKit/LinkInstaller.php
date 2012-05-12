<?php
namespace AssetKit;

class LinkInstaller extends Installer 
{

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

                if( ! file_exists($srcFile) ) {
                    $this->log("$srcFile not found.");
                    continue;
                }

                $targetFile = $asset->config->getPublicAssetRoot() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;
                if( file_exists($targetFile) ) {
                    unlink($targetFile);
                }
                FileUtils::mkdir_for_file( $targetFile );
                $this->log("x $targetFile");
                symlink($srcFile,$targetFile) or die("$targetFile link failed.");
            }
        }
    }

}



