<?php
namespace AssetKit;
use ZipArchive;
use Exception;

class ResourceUpdater
{
    public $asset;

    function __construct($asset)
    {
        if( ! extension_loaded('zip') && ! class_exists('ZipArchive') ) {
            throw new Exception('zip extension or ZipArchive class is required.');
        }
        $this->asset = $asset;
    }

    function update($update = false)
    {
        $asset = $this->asset;

        if( ! isset($asset->stash['resource']) ) {
            return false;
        }

        // if we have the source files , we 
        // should skip initializing resource from remotes.
        if( ! $update && $asset->hasSourceFiles() ) {
            return;
        }

        $resDir = null;
        $r = $asset->stash['resource'];
        if( isset($r['url']) ) {
            $url = $r['url'];

            $info = parse_url($url);
            $path = $info['path'];
            $filename = basename($info['path']);
            $targetFile = $asset->sourceDir . DIRECTORY_SEPARATOR . $filename;

            echo "Downloading file...\n";
            $cmd = "curl -# --location " . escapeshellarg($url) . " > " . escapeshellarg($targetFile);
            system($cmd);

            echo "Stored at $targetFile\n";

            if( isset($r['zip']) ) {
                $zip = new ZipArchive;
                if( $zip->open( $targetFile ) === TRUE ) {
                    echo "Extracting to {$asset->sourceDir}\n";
                    $zip->extractTo( $asset->sourceDir );
                    $zip->close();
                    $resDir = $asset->sourceDir;
                    unlink( $targetFile );
                }
                else {
                    throw new Exception('Zip fail');
                }
            }
        }
        elseif( isset($r['github']) ) 
        {

            // read-only
            $url = 'git://github.com/' . $r['github'] . '.git';
            $resDir = $asset->sourceDir . DIRECTORY_SEPARATOR . basename($url,'.git');
            if( file_exists($resDir) && $update ) {
                $dir = getcwd();
                chdir($resDir);
                system("git remote update --prune");
                system("git pull origin HEAD");
                chdir($dir);
            } else {
                system("git clone $url $resDir");
            }

        }
        elseif( isset($r['git']) ) 
        {
            $url = $r['git'];
            $resDir = $asset->sourceDir . DIRECTORY_SEPARATOR . basename($url,'.git');
            if( file_exists($resDir) && $update ) {
                $dir = getcwd();
                chdir($resDir);
                system("git remote update --prune");
                system("git pull origin HEAD");
                chdir($dir);
            } else {
                system("git clone -q $url $resDir");
            }
        }
        elseif( isset($r['svn']) ) 
        {
            $url = $r['svn'];
            $resDir = $asset->sourceDir . DIRECTORY_SEPARATOR . basename($url);
            if( file_exists($resDir) && $update ) {
                $dir = getcwd();
                chdir($resDir);
                system("svn update");
                chdir($dir);
            } else {
                system("svn checkout $url $resDir");
            }
        }
        elseif( isset($r['hg']) ) {
            $url = $r['hg'];
            $resDir = $asset->sourceDir . DIRECTORY_SEPARATOR . basename($url);
            if( file_exists($resDir) && $update ) {
                $dir = getcwd();
                chdir($resDir);
                system("hg pull -u");
                chdir($dir);
            } else {
                system("hg clone $url $resDir");
            }
        }

        // run commands for resources to initialize
        if( isset($r['commands']) ) {
            $cwd = getcwd();
            chdir( $resDir );
            foreach( $r['commands'] as $command ) {
                system($command);
            }
            chdir($cwd);
        }

    }


}



