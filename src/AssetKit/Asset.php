<?php
namespace AssetKit;
use ZipArchive;
use Exception;
use SerializerKit;

class Asset
{
    public $stash;

    public $file;

    public $config;

    public $loader;

    public function __construct($arg)
    {
        if( is_array($arg) ) {
            $this->stash = $arg['stash'];
            $this->file = $arg['file'];
            $this->dir = $arg['dir'];
            $this->name = $arg['name'];
        }
        else {
            $file = $arg;
            $this->file = $file;
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            if( 'yml' === $ext ) {
                $serializer = new SerializerKit\Serializer('yaml');
                $this->stash = $serializer->decode(file_get_contents($file));
            } else {
                $this->stash = require $file;
            }
            $this->dir = dirname($file);
            $this->name = basename(dirname($file));
        }
    }

    public function getFileCollections()
    {
        return FileCollection::create_from_manfiest($this);
    }

    public function export()
    {
        return array(
            'stash' => $this->stash,
            'file' => $this->file,
            'dir' => $this->dir,
            'name' => $this->name,
        );
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

            $info = parse_url($url);
            $path = $info['path'];
            $filename = basename($info['path']);
            $targetFile = $this->dir . DIRECTORY_SEPARATOR . $filename;

            echo "Downloading file...\n";
            $cmd = "curl -# --location " . escapeshellarg($url) . " > " . escapeshellarg($targetFile);
            system($cmd);

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



