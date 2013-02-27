<?php
namespace AssetKit;
use AssetKit\FileUtil;

class LinkInstaller extends Installer 
{

    public function uninstall($asset)
    {
        $name       = $asset->name;
        $targetDir = $asset->getInstallDir(true);
        FileUtil::rmtree($targetDir);
    }

    public function install($asset)
    {
        // asset name
        $name       = $asset->name;
        $targetDir = $asset->getInstallDir(true);
        $sourceDir  = $asset->getSourceDir(true);

        // simply use symbol link
        FileUtil::mkdir_for_file( $targetDir );

        if (file_exists($targetDir)) {
            if (is_link($targetDir)) {
                unlink($targetDir);
            } else if(is_dir($targetDir)) {
                FileUtil::rmtree($targetDir);
            } 
        }

        # echo $sourceDir , " => " , $targetDir , "\n";
        symlink(realpath($sourceDir),$targetDir) or die("$targetDir link failed.");
        return array(
            'src' => $sourceDir,
            'dst' => $targetDir,
        );
    }

}



