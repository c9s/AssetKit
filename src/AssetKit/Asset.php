<?php
namespace AssetKit;
use ZipArchive;
use Exception;
use SerializerKit;


/**
 * Asset class
 *
 * Asset object can be created from a manifest file.
 * Or can just be created with no arguments.
 */
class Asset
{
    public $stash;

    /* manifest file (related path) */
    public $manfiest;

    /* asset dir (related path) */
    public $dir;


    /**
     * @var AssetKit\Config
     */
    public $config;

    public $collections = array();

    /**
     * @param array|string|null $arg manifest array, manifest file path, or asset name
     */
    public function __construct($arg = null)
    {
        // load from array
        if( $arg && is_array($arg) ) {
            $this->stash     = @$arg['stash'];
            $this->manfiest  = @$arg['file'];
            $this->dir       = @$arg['dir'];
            $this->name      = isset($arg['name']) ? $arg['name'] : null;
        }
        elseif( $arg && file_exists($arg) ) 
        {
            // load from file
            $file = $arg;
            $this->manifest = $file;
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
        elseif( $arg && is_string($arg) ) {
            $this->name = $arg;
        }

        if( isset($this->stash['assets']) ) {
            $this->collections = FileCollection::create_from_manfiest($this);
        }
    }

    public function createFileCollection()
    {
        $collection = new FileCollection;
        $collections[] = $collection;
        return $collection;
    }

    public function getFileCollections()
    {
        return $this->collections;
    }

    public function export()
    {
        return array(
            'stash' => $this->stash,
            'manifest' => $this->manifest,
            'dir'  => $this->dir,
            'name' => $this->name,
        );
    }

    public function compile()
    {
        // compile assets


    }

    public function getPathName()
    {
        return $this->dir;
    }

    /**
     * Return the public dir of this asset
     */
    public function getPublicDir()
    {
        $public = $this->config->getPublicRoot();
        return $public . DIRECTORY_SEPARATOR . $this->name;
    }


    public function initResource()
    {
        if( ! isset($this->stash['resource']) ) {
            return false;
        }

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
            if( file_exists($resDir) ) {
                $dir = getcwd();
                chdir($resDir);
                system("git remote update --prune");
                system("git pull origin HEAD");
                chdir($dir);
            } else {
                system("git clone $url $resDir");
            }
        }
        elseif( isset($r['hg']) ) {
            $url = $r['hg'];
            $resDir = $this->dir . DIRECTORY_SEPARATOR . basename($url);
            if( file_exists($resDir) ) {
                $dir = getcwd();
                chdir($resDir);
                system("hg pull -u");
                chdir($dir);
            } else {
                system("hg clone $url $resDir");
            }
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



