<?php
namespace AssetKit;
use AssetKit\FileUtil;

class LinkInstaller extends Installer
{
    public function uninstall(Asset $asset)
    {
        $name       = $asset->name;
        $targetDir = $this->getAssetInstallDir($asset, true);
        if (file_exists($targetDir)) {
            $this->info("Removing $targetDir");
            if (is_link($targetDir) ) {
                unlink($targetDir);
            } else {
                return \futil_rmtree($targetDir);
            }
        }
    }

    public function install(Asset $asset)
    {
        // asset name
        $name       = $asset->name;
        $targetDir = $this->getAssetInstallDir($asset, true);
        $sourceDir  = $asset->getSourceDir(true);

        // simply use symbol link
        FileUtil::mkdir_for_file($targetDir);

        // Remove the previously created symlink files
        if (is_link($targetDir)) {
            unlink($targetDir);
        } else if (is_dir($targetDir)) {
            \futil_rmtree($targetDir);
            if (file_exists($targetDir)) {
                rmdir($targetDir);
            }
        }

        $this->info('Creating symlink at ' . $targetDir . ' for ' . realpath($sourceDir) );
        // echo realpath($sourceDir) , " => " , $targetDir , "\n";
        symlink(realpath($sourceDir),$targetDir) or die("$targetDir link failed.");
        return array(
            'src' => $sourceDir,
            'dst' => $targetDir,
        );
    }

}



