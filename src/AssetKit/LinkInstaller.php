<?php
namespace AssetKit;

class LinkInstaller extends Installer 
{

    public function install($asset)
    {
        $fromDir = $asset->sourceDir;

        // asset name
        $name       = $asset->name;

        // install into public asset root.
        foreach( $asset->getCollections() as $collection ) {
            $srcFile = $fromDir;
            $targetFile = $asset->config->getPublicAssetRoot() . DIRECTORY_SEPARATOR . $name;
            if( file_exists($targetFile) ) {
                unlink($targetFile);
            }

            // simply use symbol link
            FileUtils::mkdir_for_file( $targetFile );
            symlink(realpath($srcFile),$targetFile) 
                    or die("$targetFile link failed.");
            $this->log("* $targetFile");
        }
    }

}



