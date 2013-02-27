<?php
namespace AssetKit;
use AssetKit\FileUtil;

class LinkInstaller extends Installer 
{

    public function install($asset)
    {
        // asset name
        $name       = $asset->name;
        $targetFile = $asset->getInstallDir(true);
        $sourceDir  = $asset->getSourceDir(true);

        # echo $sourceDir , " => " , $targetFile, "\n";

        // simply use symbol link
        FileUtil::mkdir_for_file( $targetFile );

        if (file_exists($targetFile)) {
            if (is_link($targetFile)) {
                unlink($targetFile);
            }
            else if(is_dir($targetFile)) {
                echo "Removing $targetFile\n";
                # FileUtil::rmtree($targetFile);
            } 
        }

        $sourceDir = realpath($sourceDir);
        $targetFile = realpath($targetFile);

        echo $sourceDir , " => " , $targetFile , "\n";
        symlink($sourceDir,$targetFile) or die("$targetFile link failed.");
    }

}



