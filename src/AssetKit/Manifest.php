<?php
namespace AssetKit;
use ZipArchive;
use Exception;
use SerializerKit;

class Manifest
{
    public $stash;
    public $file;

    public function __construct($file)
    {
        $this->file = $file;
        $serializer = new SerializerKit\Serializer('yaml');
        $this->stash = $serializer->decode(file_get_contents($file));
        $this->dir = dirname(realpath($file));
    }

    public function compile()
    {
        $serializer = new SerializerKit\Serializer('php');
        $php = '<php? ' .  $serializer->encode($this->stash) . '?>';
        $ext = pathinfo($this->file, PATHINFO_EXTENSION);
        $filename = pathinfo($this->file, PATHINFO_FILENAME);
        $target = $this->dir . DIRECTORY_SEPARATOR . $filename . '.php';
        file_put_contents($target, $php);
        return $target;
    }

    public function initResource()
    {
        if( ! isset($this->stash['resource']) )
            return false;

        $resDir = null;
        $r = $this->stash['resource'];
        if( isset($r['url']) ) {
            $url = $r['url'];

            $filename = basename($url);
            $targetFile = $this->dir . DIRECTORY_SEPARATOR . $filename;

            echo "Downloading file...\n";
            system("curl -# --location $url > " . $targetFile );

            echo "Stored at $targetFile\n";

            if( isset($r['zip']) ) {
                $zip = new ZipArchive;
                if( $zip->open( $targetFile ) === TRUE ) {
                    echo "Extracting to {$this->dir}\n";
                    $zip->extractTo( $this->dir );
                    $zip->close();
                    $resDir = $this->dir;
                    unlink( $targetFile );
                }
                else {
                    throw new Exception('Zip fail');
                }
            }
        }
        elseif( isset($r['git']) ) {
            $url = $r['git'];
            $resDir = $this->dir . DIRECTORY_SEPARATOR . basename($url,'.git');
            system("git clone $url $resDir");
        }

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



